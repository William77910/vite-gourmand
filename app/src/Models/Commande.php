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
      'SELECT c.*, m.titre AS titre_menu
             FROM commande c
             JOIN menu m ON m.id_menu = c.id_menu
             WHERE c.id_commande = :id'
    );
    $stmt->execute([':id' => $id]);
    $commande = $stmt->fetch();
    return $commande ?: null;
  }
}
