<?php

require_once __DIR__ . '/../../config/database.php';

class Commande
{
  /**
   * Crée une commande et décrémente le stock (Transaction SQL).
   */
  public static function create(array $data): int
  {
    $pdo = Database::getConnection();
    $pdo->beginTransaction();
    try {
      $sql = "INSERT INTO commande (id_utilisateur, id_menu, nb_personnes, date_prestation, heure_prestation, adresse_livraison, ville_livraison, code_postal_livraison, frais_livraison, reduction_appliquee, prix_total, commentaires, mode_paiement)
                    VALUES (:id_u, :id_m, :nb, :date_p, :heure_p, :adr, :ville, :cp, :frais, :reduc, :total, :comm, :mode)";
      $stmt = $pdo->prepare($sql);
      $stmt->execute([
        ':id_u' => $data['id_utilisateur'],
        ':id_m' => $data['id_menu'],
        ':nb' => $data['nb_personnes'],
        ':date_p' => $data['date_prestation'],
        ':heure_p' => $data['heure_prestation'],
        ':adr' => $data['adresse_livraison'],
        ':ville' => $data['ville_livraison'],
        ':cp' => $data['code_postal_livraison'],
        ':frais' => $data['frais_livraison'],
        ':reduc' => $data['reduction_appliquee'] ? 1 : 0,
        ':total' => $data['prix_total'],
        ':comm' => $data['commentaires'],
        ':mode' => $data['mode_paiement']
      ]);

      $id = (int)$pdo->lastInsertId();
      $pdo->prepare("INSERT INTO historique_statut (id_commande, statut) VALUES (?, 'en_attente')")->execute([$id]);

      $stmtStock = $pdo->prepare("UPDATE menu SET stock_disponible = stock_disponible - 1 WHERE id_menu = ? AND stock_disponible > 0");
      $stmtStock->execute([$data['id_menu']]);
      if ($stmtStock->rowCount() === 0) throw new Exception("Désolé, ce menu n'est plus disponible en stock.");

      $pdo->commit();
      return $id;
    } catch (Exception $e) {
      $pdo->rollBack();
      throw $e;
    }
  }

  /**
   * Récupère une commande avec son statut actuel (via sous-requête).
   * CORRIGE L'ERREUR "Undefined array key statut_actuel"
   */
  public static function getById(int $id): ?array
  {
    $pdo = Database::getConnection();
    $sql = "SELECT c.*, m.titre AS titre_menu, m.nb_personnes_min, m.prix_personne,
                (SELECT statut FROM historique_statut h 
                 WHERE h.id_commande = c.id_commande 
                 ORDER BY h.date_modification DESC LIMIT 1) AS statut_actuel
                FROM commande c
                JOIN menu m ON c.id_menu = m.id_menu
                WHERE c.id_commande = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    $res = $stmt->fetch();
    return $res ?: null;
  }

  /**
   * Met à jour une commande existante.
   * CORRIGE L'ERREUR "Undefined method modifier"
   */
  public static function modifier(int $id, int $idUser, array $data): bool
  {
    $pdo = Database::getConnection();
    $sql = "UPDATE commande SET 
                nb_personnes = :nb, date_prestation = :d, heure_prestation = :h, 
                adresse_livraison = :adr, ville_livraison = :v, code_postal_livraison = :cp, 
                frais_livraison = :f, reduction_appliquee = :r, prix_total = :t, commentaires = :c
                WHERE id_commande = :id AND id_utilisateur = :id_u";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
      ':nb' => $data['nb_personnes'],
      ':d' => $data['date_prestation'],
      ':h' => $data['heure_prestation'],
      ':adr' => $data['adresse_livraison'],
      ':v' => $data['ville_livraison'],
      ':cp' => $data['code_postal_livraison'],
      ':f' => $data['frais_livraison'],
      ':r' => $data['reduction_appliquee'] ? 1 : 0,
      ':t' => $data['prix_total'],
      ':c' => $data['commentaires'],
      ':id' => $id,
      ':id_u' => $idUser
    ]);
  }

  public static function getByUtilisateur(int $id): array
  {
    $pdo = Database::getConnection();
    $sql = "SELECT c.*, m.titre AS titre_menu,
                (SELECT statut FROM historique_statut h WHERE h.id_commande = c.id_commande ORDER BY h.date_modification DESC LIMIT 1) AS statut_actuel,
                (SELECT COUNT(*) FROM avis a WHERE a.id_commande = c.id_commande) AS a_un_avis
                FROM commande c JOIN menu m ON m.id_menu = c.id_menu WHERE c.id_utilisateur = ? ORDER BY c.date_creation DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetchAll();
  }

  public static function getHistoriqueStatuts(int $id): array
  {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT * FROM historique_statut WHERE id_commande = ? ORDER BY date_modification ASC");
    $stmt->execute([$id]);
    return $stmt->fetchAll();
  }

  public static function getAll(array $filtres = []): array
  {
    $pdo = Database::getConnection();
    $sql = "SELECT * FROM (
                    SELECT c.*, m.titre AS titre_menu, u.nom, u.prenom, u.email,
                           (SELECT statut FROM historique_statut h 
                            WHERE h.id_commande = c.id_commande 
                            ORDER BY h.date_modification DESC LIMIT 1) as statut_actuel
                    FROM commande c
                    JOIN menu m ON c.id_menu = m.id_menu
                    JOIN utilisateur u ON u.id_utilisateur = c.id_utilisateur
                ) as temp WHERE 1=1";

    $params = [];
    if (!empty($filtres['statut'])) {
      $sql .= " AND statut_actuel = :statut";
      $params[':statut'] = $filtres['statut'];
    }
    if (!empty($filtres['client'])) {
      $sql .= " AND (nom LIKE :client OR prenom LIKE :client OR email LIKE :client)";
      $params[':client'] = '%' . $filtres['client'] . '%';
    }
    $sql .= " ORDER BY date_creation DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
  }

  public static function transitionsAutorisees(?string $s): array
  {
    $t = [
      'en_attente' => ['accepte', 'annulee'],
      'accepte' => ['en_preparation', 'annulee'],
      'en_preparation' => ['en_cours_de_livraison', 'annulee'],
      'en_cours_de_livraison' => ['livre', 'annulee'],
      'livre' => ['en_attente_retour_materiel', 'terminee'],
      'en_attente_retour_materiel' => ['terminee']
    ];
    return $t[$s] ?? [];
  }

  public static function changerStatut(int $id, string $s, int $emp): bool
  {
    return Database::getConnection()->prepare("INSERT INTO historique_statut (id_commande, id_employe, statut) VALUES (?, ?, ?)")->execute([$id, $emp, $s]);
  }

  public static function annulerParClient(int $id, int $idU): bool
  {
    $c = self::getById($id);
    if ($c && $c['id_utilisateur'] == $idU && $c['statut_actuel'] === 'en_attente') {
      return Database::getConnection()->prepare("INSERT INTO historique_statut (id_commande, statut) VALUES (?, 'annulee')")->execute([$id]);
    }
    return false;
  }
}
