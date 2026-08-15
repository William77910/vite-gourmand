<?php

require_once __DIR__ . '/../../config/database.php';

class Commande
{
  /**
   * Crée une commande, initialise son statut et décrémente le stock.
   * Utilise une transaction pour garantir l'intégrité des données.
   */
  public static function create(array $data): int
  {
    $pdo = Database::getConnection();
    $pdo->beginTransaction();

    try {
      $sql = "INSERT INTO commande
                    (id_utilisateur, id_menu, nb_personnes, date_prestation, heure_prestation,
                     adresse_livraison, ville_livraison, code_postal_livraison,
                     frais_livraison, reduction_appliquee, prix_total, commentaires, mode_paiement)
                    VALUES
                    (:id_utilisateur, :id_menu, :nb_personnes, :date_prestation, :heure_prestation,
                     :adresse_livraison, :ville_livraison, :code_postal_livraison,
                     :frais_livraison, :reduction_appliquee, :prix_total, :commentaires, :mode_paiement)";

      $stmt = $pdo->prepare($sql);
      $stmt->execute([
        ':id_utilisateur'        => $data['id_utilisateur'],
        ':id_menu'               => $data['id_menu'],
        ':nb_personnes'          => $data['nb_personnes'],
        ':date_prestation'       => $data['date_prestation'],
        ':heure_prestation'      => $data['heure_prestation'],
        ':adresse_livraison'     => $data['adresse_livraison'],
        ':ville_livraison'       => $data['ville_livraison'],
        ':code_postal_livraison' => $data['code_postal_livraison'],
        ':frais_livraison'       => $data['frais_livraison'],
        ':reduction_appliquee'   => $data['reduction_appliquee'] ? 1 : 0,
        ':prix_total'            => $data['prix_total'],
        ':commentaires'          => $data['commentaires'],
        ':mode_paiement'         => $data['mode_paiement'],
      ]);

      $idCommande = (int) $pdo->lastInsertId();

      $stmtHistorique = $pdo->prepare(
        "INSERT INTO historique_statut (id_commande, statut) VALUES (:id, 'en_attente')"
      );
      $stmtHistorique->execute([':id' => $idCommande]);

      $sqlStock = "UPDATE menu SET stock_disponible = stock_disponible - 1 
                         WHERE id_menu = :id_menu AND stock_disponible > 0";
      $stmtStock = $pdo->prepare($sqlStock);
      $stmtStock->execute([':id_menu' => $data['id_menu']]);

      if ($stmtStock->rowCount() === 0) {
        throw new Exception("Désolé, ce menu n'est plus disponible en stock.");
      }

      $pdo->commit();
      return $idCommande;
    } catch (Exception $e) {
      $pdo->rollBack();
      throw $e;
    }
  }

  /**
   * Liste toutes les commandes pour l'espace employé avec filtres.
   */
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
                ) as temp_table WHERE 1=1";

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

  /**
   * Définit les transitions d'états autorisées (Machine à états).
   * Règle métier de la page 8 du PDF.
   */
  public static function transitionsAutorisees(?string $statutActuel): array
  {
    $transitions = [
      'en_attente'                 => ['accepte', 'annulee'],
      'accepte'                    => ['en_preparation', 'annulee'],
      'en_preparation'             => ['en_cours_de_livraison', 'annulee'],
      'en_cours_de_livraison'      => ['livre', 'annulee'],
      'livre'                      => ['en_attente_retour_materiel', 'terminee'],
      'en_attente_retour_materiel' => ['terminee'],
      'terminee'                   => [],
      'annulee'                    => [],
    ];

    return $transitions[$statutActuel] ?? [];
  }

  /**
   * Annulation par un employé : motif et mode de contact obligatoires.
   * Exigence du cahier des charges (Page 8).
   */
  public static function annulerParEmploye(int $idCommande, int $idEmploye, string $motif, string $modeContact): void
  {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare(
      "INSERT INTO historique_statut (id_commande, id_employe, statut, motif, mode_contact)
             VALUES (:id, :emp, 'annulee', :motif, :mode)"
    );
    $stmt->execute([
      ':id' => $idCommande,
      ':emp' => $idEmploye,
      ':motif' => $motif,
      ':mode' => $modeContact,
    ]);
  }

  public static function changerStatut(int $idCommande, string $nouveauStatut, int $idEmploye): bool
  {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('INSERT INTO historique_statut (id_commande, id_employe, statut) VALUES (:id, :emp, :statut)');
    return $stmt->execute([':id' => $idCommande, ':emp' => $idEmploye, ':statut' => $nouveauStatut]);
  }

  public static function getById(int $id): ?array
  {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('SELECT c.*, m.titre AS titre_menu, m.prix_personne FROM commande c JOIN menu m ON m.id_menu = c.id_menu WHERE c.id_commande = :id');
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
  }

  public static function getByUtilisateur(int $idUtilisateur): array
  {
    $pdo = Database::getConnection();
    $sql = "SELECT c.*, m.titre AS titre_menu,
                (SELECT statut FROM historique_statut h WHERE h.id_commande = c.id_commande ORDER BY h.date_modification DESC LIMIT 1) AS statut_actuel,
                (SELECT COUNT(*) FROM avis a WHERE a.id_commande = c.id_commande) AS a_un_avis
                FROM commande c JOIN menu m ON m.id_menu = c.id_menu WHERE c.id_utilisateur = :id ORDER BY c.date_creation DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $idUtilisateur]);
    return $stmt->fetchAll();
  }

  public static function annulerParClient(int $idCommande, int $idUtilisateur): bool
  {
    $pdo = Database::getConnection();
    $sql = "SELECT (SELECT statut FROM historique_statut h WHERE h.id_commande = c.id_commande ORDER BY h.date_modification DESC LIMIT 1) as dernier_statut
                FROM commande c WHERE c.id_commande = :id_commande AND c.id_utilisateur = :id_user";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_commande' => $idCommande, ':id_user' => $idUtilisateur]);
    $res = $stmt->fetch();

    if ($res && $res['dernier_statut'] === 'en_attente') {
      $stmtInsert = $pdo->prepare("INSERT INTO historique_statut (id_commande, statut) VALUES (:id, 'annulee')");
      return $stmtInsert->execute([':id' => $idCommande]);
    }
    return false;
  }
}
