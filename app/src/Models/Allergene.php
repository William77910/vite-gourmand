<?php
require_once __DIR__ . '/../../config/database.php';

class Allergene
{
  public static function getAll(): array
  {
    $pdo = Database::getConnection();
    return $pdo->query("SELECT * FROM allergene ORDER BY nom ASC")->fetchAll();
  }

  public static function create(string $nom): bool
  {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("INSERT INTO allergene (nom) VALUES (:nom)");
    return $stmt->execute([':nom' => $nom]);
  }

  public static function delete(int $id): bool
  {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("DELETE FROM allergene WHERE id_allergene = :id");
    return $stmt->execute([':id' => $id]);
  }

  /**
   * Récupère les IDs des allergènes liés à un plat spécifique.
   */
  public static function getIdsParPlat(int $idPlat): array
  {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT id_allergene FROM plat_allergene WHERE id_plat = :id");
    $stmt->execute([':id' => $idPlat]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
  }
}
