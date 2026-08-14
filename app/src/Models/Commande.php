<?php

require_once __DIR__ . '/../../config/database.php';

class Commande
{
  /**
   * Crée une commande et son premier historique de statut ("en_attente")
   * dans une transaction : soit tout est enregistré, soit rien ne l'est.
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

      $pdo->commit();
      return $idCommande;
    } catch (Exception $e) {
      $pdo->rollBack();
      throw $e;
    }
  }

  public static function getById(int $id): ?array
  {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare(
      'SELECT c.*, m.titre AS titre_menu, m.prix_personne, m.nb_personnes_min
             FROM commande c
             JOIN menu m ON m.id_menu = c.id_menu
             WHERE c.id_commande = :id'
    );
    $stmt->execute([':id' => $id]);
    $commande = $stmt->fetch();
    return $commande ?: null;
  }

  /**
   * Liste des commandes d'un utilisateur, avec le statut actuel de chacune.
   */
  public static function getByUtilisateur(int $idUtilisateur): array
  {
    $pdo = Database::getConnection();
    $sql = "SELECT c.*, m.titre AS titre_menu,
                       (SELECT statut FROM historique_statut h
                        WHERE h.id_commande = c.id_commande
                        ORDER BY h.date_modification DESC LIMIT 1) AS statut_actuel,
                       (SELECT COUNT(*) FROM avis a WHERE a.id_commande = c.id_commande) AS a_un_avis
                FROM commande c
                JOIN menu m ON m.id_menu = c.id_menu
                WHERE c.id_utilisateur = :id
                ORDER BY c.date_creation DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $idUtilisateur]);
    return $stmt->fetchAll();
  }

  /**
   * Historique horodaté des statuts d'une commande.
   */
  public static function getHistoriqueStatuts(int $idCommande): array
  {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare(
      'SELECT statut, date_modification FROM historique_statut
             WHERE id_commande = :id ORDER BY date_modification ASC'
    );
    $stmt->execute([':id' => $idCommande]);
    return $stmt->fetchAll();
  }

  /**
   * Liste des commandes pour l'espace employé, avec filtres optionnels
   * par statut et/ou par client (nom, prenom, email).
   */
  public static function getAll(array $filtres = []): array
  {
    $pdo = Database::getConnection();

    $sql = "SELECT * FROM (
                    SELECT c.*, m.titre AS titre_menu,
                           u.nom, u.prenom, u.email,
                           (SELECT statut FROM historique_statut h
                            WHERE h.id_commande = c.id_commande
                            ORDER BY h.date_modification DESC LIMIT 1) AS statut_actuel
                    FROM commande c
                    JOIN menu m ON m.id_menu = c.id_menu
                    JOIN utilisateur u ON u.id_utilisateur = c.id_utilisateur
                ) AS t
                WHERE 1 = 1";

    $params = [];

    if (!empty($filtres['statut'])) {
      $sql .= ' AND statut_actuel = :statut';
      $params[':statut'] = $filtres['statut'];
    }

    if (!empty($filtres['client'])) {
      $sql .= ' AND (nom LIKE :client OR prenom LIKE :client OR email LIKE :client)';
      $params[':client'] = '%' . $filtres['client'] . '%';
    }

    $sql .= ' ORDER BY date_creation DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
  }

  /**
   * Transitions de statut autorisées, dans l'ordre du cahier des charges.
   */
  public static function transitionsAutorisees(?string $statutActuel): array
  {

    $transitions = [
      'en_attente'                 => ['accepte', 'annulee'],
      'accepte'                    => ['en_preparation', 'annulee'],
      'en_preparation'              => ['en_cours_de_livraison', 'annulee'],
      'en_cours_de_livraison'       => ['livre', 'annulee'],
      'livre'                       => ['en_attente_retour_materiel', 'terminee'],
      'en_attente_retour_materiel'  => ['terminee'],
      'terminee'                    => [],
      'annulee'                     => [],
    ];

    return $transitions[$statutActuel] ?? [];
  }

  /**
   * Fait avancer le statut d'une commande (employe/admin uniquement).
   * Vérifie que la transition demandée est bien autorisée.
   */
  public static function changerStatut(int $idCommande, string $nouveauStatut, int $idEmploye): bool
  {
    $pdo = Database::getConnection();

    $stmtActuel = $pdo->prepare(
      'SELECT statut FROM historique_statut WHERE id_commande = :id ORDER BY date_modification DESC LIMIT 1'
    );
    $stmtActuel->execute([':id' => $idCommande]);
    $statutActuel = $stmtActuel->fetchColumn();

    if (!in_array($nouveauStatut, self::transitionsAutorisees((string) $statutActuel), true)) {
      return false;
    }

    $stmt = $pdo->prepare(
      'INSERT INTO historique_statut (id_commande, id_employe, statut) VALUES (:id, :emp, :statut)'
    );
    $stmt->execute([':id' => $idCommande, ':emp' => $idEmploye, ':statut' => $nouveauStatut]);
    return true;
  }

  /**
   * Annulation par un employe : motif et mode de contact obligatoires
   * (exigence du cahier des charges - le client doit avoir été prévenu).
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

  /**
   * Annule une commande si elle est encore en attente.
   * Utilisé par le client depuis son espace compte.
   */
  public static function annulerParClient(int $idCommande, int $idUtilisateur): bool
  {
    $pdo = Database::getConnection();

    // 1. Vérification stricte : la commande appartient-elle à l'utilisateur ET quel est son dernier statut ?
    $sql = "SELECT 
                    (SELECT statut FROM historique_statut h 
                     WHERE h.id_commande = c.id_commande 
                     ORDER BY h.date_modification DESC LIMIT 1) as dernier_statut
                 FROM commande c
                 WHERE c.id_commande = :id_commande AND c.id_utilisateur = :id_user";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_commande' => $idCommande, ':id_user' => $idUtilisateur]);
    $res = $stmt->fetch();

    // Règle métier : Annulation possible UNIQUEMENT si 'en_attente'
    if ($res && $res['dernier_statut'] === 'en_attente') {
      // 2. On insère le nouveau statut dans l'historique MySQL
      $sqlInsert = "INSERT INTO historique_statut (id_commande, statut, date_modification) 
                          VALUES (:id, 'annulee', NOW())";
      $stmtInsert = $pdo->prepare($sqlInsert);
      return $stmtInsert->execute([':id' => $idCommande]);
    }

    return false;
  }

  /**
   * Modifie une commande existante (jamais le menu choisi), uniquement si
   * elle appartient à l'utilisateur et n'a pas encore été acceptée.
   */
  public static function modifier(int $idCommande, int $idUtilisateur, array $data): bool
  {
    $commande = self::getById($idCommande);
    if (!$commande || $commande['id_utilisateur'] !== $idUtilisateur) {
      return false;
    }

    $pdo = Database::getConnection();
    $stmtStatut = $pdo->prepare(
      'SELECT statut FROM historique_statut WHERE id_commande = :id ORDER BY date_modification DESC LIMIT 1'
    );
    $stmtStatut->execute([':id' => $idCommande]);
    if ($stmtStatut->fetchColumn() !== 'en_attente') {
      return false;
    }

    $sql = "UPDATE commande SET
                    nb_personnes = :nb_personnes, date_prestation = :date_prestation,
                    heure_prestation = :heure_prestation, adresse_livraison = :adresse_livraison,
                    ville_livraison = :ville_livraison, code_postal_livraison = :code_postal_livraison,
                    frais_livraison = :frais_livraison, reduction_appliquee = :reduction_appliquee,
                    prix_total = :prix_total, commentaires = :commentaires
                WHERE id_commande = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      ':nb_personnes' => $data['nb_personnes'],
      ':date_prestation' => $data['date_prestation'],
      ':heure_prestation' => $data['heure_prestation'],
      ':adresse_livraison' => $data['adresse_livraison'],
      ':ville_livraison' => $data['ville_livraison'],
      ':code_postal_livraison' => $data['code_postal_livraison'],
      ':frais_livraison' => $data['frais_livraison'],
      ':reduction_appliquee' => $data['reduction_appliquee'] ? 1 : 0,
      ':prix_total' => $data['prix_total'],
      ':commentaires' => $data['commentaires'],
      ':id' => $idCommande,
    ]);
    return true;
  }
}
