<?php

require_once __DIR__ . '/../../config/database.php';

class Avis
{
  /**
   * Récupère les avis validés pour un menu donné, avec le prénom/nom
   * du client (via la commande associée).
   */
  public static function getByMenu(int $idMenu): array
  {
    $pdo = Database::getConnection();
    $sql = "SELECT av.note, av.commentaire, av.date_creation, u.prenom, u.nom
                FROM avis av
                JOIN commande c ON c.id_commande = av.id_commande
                JOIN utilisateur u ON u.id_utilisateur = c.id_utilisateur
                WHERE c.id_menu = :id AND av.valide = 1
                ORDER BY av.date_creation DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $idMenu]);
    return $stmt->fetchAll();
  }
}
