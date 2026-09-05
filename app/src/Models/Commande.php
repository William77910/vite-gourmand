<?php

require_once __DIR__ . '/../../config/database.php';

class Commande
{
  /**
   * Crée une commande (Transactionnel)
   */
  public static function create(array $data): int
  {
    $pdo = Database::getConnection();
    try {
      // On commence une transaction
      $pdo->beginTransaction();
      // On verrouille la ligne du menu pour éviter les problèmes de stock
      $stmt = $pdo->prepare("SELECT stock_disponible FROM menu WHERE id_menu = ? FOR UPDATE");
      $stmt->execute([$data['id_menu']]);
      $m = $stmt->fetch();
      // On vérifie le stock disponible
      if (!$m || (int)$m['stock_disponible'] <= 0) throw new Exception("Stock épuisé");
      // On insère la commande
      $sql = "INSERT INTO commande (id_utilisateur, id_menu, nb_personnes, date_prestation, heure_prestation, adresse_livraison,
      ville_livraison, code_postal_livraison, frais_livraison, reduction_appliquee, prix_total, commentaires, mode_paiement)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
      $pdo->prepare($sql)->execute([
        $data['id_utilisateur'],
        $data['id_menu'],
        $data['nb_personnes'],
        $data['date_prestation'],
        $data['heure_prestation'],
        $data['adresse_livraison'],
        $data['ville_livraison'],
        $data['code_postal_livraison'],
        $data['frais_livraison'],
        $data['reduction_appliquee'] ? 1 : 0,
        $data['prix_total'],
        $data['commentaires'],
        $data['mode_paiement']
      ]);
      $id = (int)$pdo->lastInsertId();
      // On ajoute le statut initial "en_attente" dans l'historique
      $pdo->prepare("INSERT INTO historique_statut (id_commande, statut) VALUES (?, 'en_attente')")->execute([$id]);
      // On décrémente le stock disponible du menu
      $pdo->prepare("UPDATE menu SET stock_disponible = stock_disponible - 1 WHERE id_menu = ?")->execute([$data['id_menu']]);
      // On valide la transaction
      $pdo->commit();
      return $id;
      // Si une exception est levée, on annule la transaction et on relance l'exception
    } catch (Exception $e) {
      $pdo->rollBack();
      throw $e;
    }
  }
  /**
   * Récupère une commande par son ID
   */
  public static function getById(int $id): ?array
  {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("
            SELECT c.*, m.titre as titre_menu, m.prix_personne, u.nom, u.prenom, u.email,
            (SELECT statut FROM historique_statut WHERE id_commande = c.id_commande ORDER BY date_modification DESC LIMIT 1) as statut_actuel
            FROM commande c 
            JOIN menu m ON c.id_menu = m.id_menu 
            JOIN utilisateur u ON c.id_utilisateur = u.id_utilisateur
            WHERE c.id_commande = ?
        ");
    $stmt->execute([$id]);
    $res = $stmt->fetch();
    return $res ?: null;
  }

  /**
   * Récupère toutes les commandes avec filtres (Espace Employé / Admin)
   * Recherche avancée par nom complet, email ou statut.
   */
  public static function getAll(array $filters = []): array
  {
    $pdo = Database::getConnection();

    $where = [];
    $params = [];

    // 1. Filtrage par statut actuel
    if (!empty($filters['statut'])) {
      $where[] = "(SELECT statut FROM historique_statut WHERE id_commande = c.id_commande ORDER BY date_modification DESC LIMIT 1) = :statut";
      $params[':statut'] = $filters['statut'];
    }

    // 2. Filtrage par client (nom, prénom, email OU NOM COMPLET)
    if (!empty($filters['client'])) {
      // CONCAT permet de chercher "John Doe" dans la fusion "prenom + nom" ou "nom + prenom"
      // On utilise des alias pour éviter les collisions de noms de colonnes
      $where[] = "(u.nom LIKE :c1
                         OR u.prenom LIKE :c2
                         OR u.email LIKE :c3
                         OR CONCAT(u.prenom, ' ', u.nom) LIKE :c4
                         OR CONCAT(u.nom, ' ', u.prenom) LIKE :c5)";
      // Recherche par nom, prénom ou email
      $search = '%' . $filters['client'] . '%';
      $params[':c1'] = $search;
      $params[':c2'] = $search;
      $params[':c3'] = $search;
      $params[':c4'] = $search;
      $params[':c5'] = $search;
    }
    // 3. Construction de la requête SQL
    $sql = "SELECT c.*, m.titre as titre_menu, u.nom, u.prenom, u.email,
                (SELECT statut FROM historique_statut WHERE id_commande = c.id_commande ORDER BY date_modification DESC LIMIT 1) as statut_actuel
                FROM commande c
                JOIN menu m ON c.id_menu = m.id_menu
                JOIN utilisateur u ON c.id_utilisateur = u.id_utilisateur";
    // On ajoute les conditions WHERE si nécessaire
    if (!empty($where)) {
      $sql .= " WHERE " . implode(" AND ", $where);
    }
    // 4. Tri par date de création décroissante
    $sql .= " ORDER BY c.date_creation DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
  }

  /**
   * Récupère les commandes d'un utilisateur (Espace Client)
   */
  public static function getByUtilisateur(int $idUtilisateur): array
  {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("
            SELECT c.*, m.titre as titre_menu,
            (SELECT statut FROM historique_statut 
             WHERE id_commande = c.id_commande 
             ORDER BY date_modification DESC LIMIT 1) as statut_actuel,
            (SELECT COUNT(*) FROM avis WHERE id_commande = c.id_commande) as a_un_avis
            FROM commande c
            JOIN menu m ON c.id_menu = m.id_menu
            WHERE c.id_utilisateur = ?
            ORDER BY c.date_creation DESC
        ");
    $stmt->execute([$idUtilisateur]);
    return $stmt->fetchAll();
  }

  /**
   * Machine à états : Transitions autorisées
   */
  public static function transitionsAutorisees(string $statutActuel): array
  {
    $transitions = [
      'en_attente'                => ['accepte', 'annulee'],
      'accepte'                   => ['en_preparation', 'annulee'],
      'en_preparation'            => ['en_cours_de_livraison', 'annulee'],
      'en_cours_de_livraison'     => ['livre'],
      'livre'                     => ['en_attente_retour_materiel', 'terminee'],
      'en_attente_retour_materiel' => ['terminee'],
      'terminee'                  => [],
      'annulee'                   => []
    ];
    return $transitions[$statutActuel] ?? [];
  }

  /**
   * Met à jour le statut (Standard)
   */
  public static function changerStatut(int $id, string $statut, ?int $idEmploye = null): bool
  {
    $pdo = Database::getConnection();
    $sql = "INSERT INTO historique_statut (id_commande, id_employe, statut) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$id, $idEmploye, $statut]);
  }

  /**
   * Annule une commande avec motif (Employé)
   */
  public static function annulerParEmploye(int $id, int $idEmploye, string $motif, string $contact): bool
  {
    $pdo = Database::getConnection();
    try {
      $pdo->beginTransaction();
      $pdo->prepare("INSERT INTO historique_statut (id_commande, id_employe, statut, motif, mode_contact) VALUES (?, ?, 'annulee', ?, ?)")
        ->execute([$id, $idEmploye, $motif, $contact]);
      $pdo->prepare("UPDATE menu SET stock_disponible = stock_disponible + 1 WHERE id_menu = (SELECT id_menu FROM commande WHERE id_commande = ?)")
        ->execute([$id]);
      $pdo->commit();
      return true;
    } catch (Exception $e) {
      $pdo->rollBack();
      return false;
    }
  }

  /**
   * Annule une commande (Client)
   */
  public static function annulerParClient(int $id, int $idUser): bool
  {
    $pdo = Database::getConnection();
    try {
      $pdo->beginTransaction();
      $stmt = $pdo->prepare("SELECT id_commande FROM commande WHERE id_commande = ? AND id_utilisateur = ? AND (SELECT statut FROM historique_statut WHERE id_commande = ? ORDER BY date_modification DESC LIMIT 1) = 'en_attente'");
      $stmt->execute([$id, $idUser, $id]);
      if (!$stmt->fetch()) throw new Exception("Annulation impossible");
      $pdo->prepare("INSERT INTO historique_statut (id_commande, statut) VALUES (?, 'annulee')")->execute([$id]);
      $pdo->prepare("UPDATE menu SET stock_disponible = stock_disponible + 1 WHERE id_menu = (SELECT id_menu FROM commande WHERE id_commande = ?)")->execute([$id]);
      $pdo->commit();
      return true;
    } catch (Exception $e) {
      $pdo->rollBack();
      return false;
    }
  }

  /**
   * Modifie une commande
   */
  public static function modifier(int $id, int $idUser, array $data): bool
  {
    $pdo = Database::getConnection();
    $sql = "UPDATE commande SET nb_personnes = ?, date_prestation = ?, heure_prestation = ?, adresse_livraison = ?, ville_livraison = ?, code_postal_livraison = ?, frais_livraison = ?, reduction_appliquee = ?, prix_total = ?, commentaires = ? WHERE id_commande = ? AND id_utilisateur = ?";
    return $pdo->prepare($sql)->execute([$data['nb_personnes'], $data['date_prestation'], $data['heure_prestation'], $data['adresse_livraison'], $data['ville_livraison'], $data['code_postal_livraison'], $data['frais_livraison'], $data['reduction_appliquee'] ? 1 : 0, $data['prix_total'], $data['commentaires'], $id, $idUser]);
  }
}
