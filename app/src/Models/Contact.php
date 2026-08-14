<?php

require_once __DIR__ . '/../../config/database.php';

class Contact
{
  /**
   * Récupère tous les messages de contact, les plus récents en premier.
   */
  public static function getAll(): array
  {
    $pdo = Database::getConnection();
    $sql = "SELECT * FROM message_contact ORDER BY date_creation DESC";
    return $pdo->query($sql)->fetchAll();
  }

  /**
   * Marque un message comme "traité".
   */
  public static function marquerCommeTraite(int $id): void
  {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("UPDATE message_contact SET traite = 1 WHERE id_contact = :id");
    $stmt->execute([':id' => $id]);
  }

  /**
   * Supprime un message.
   */
  public static function delete(int $id): void
  {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("DELETE FROM message_contact WHERE id_contact = :id");
    $stmt->execute([':id' => $id]);
  }
}
