<?php

require_once __DIR__ . '/../../config/database.php';

/**
 * Modèle Plat : Gère les entités culinaires (entrées, plats, desserts).
 * Un plat est indépendant et peut être associé à plusieurs menus.
 */
class Plat
{
  /**
   * Récupère tous les plats avec leur photo associée.
   * 
   * @param string|null $type Optionnel : 'entree', 'plat' ou 'dessert'
   * @return array Liste des plats
   */
  public static function getAll(?string $type = null): array
  {
    $pdo = Database::getConnection();

    $sql = "SELECT p.*, img.url_image AS photo 
                FROM plat p
                LEFT JOIN image img ON p.id_image = img.id_image";

    $params = [];
    if ($type !== null) {
      $sql .= " WHERE p.type_plat = :type";
      $params[':type'] = $type;
    }

    $sql .= " ORDER BY p.nom ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
  }

  /**
   * Récupère un plat par son identifiant unique.
   */
  public static function getById(int $id): ?array
  {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare(
      "SELECT p.*, img.url_image AS photo 
             FROM plat p
             LEFT JOIN image img ON p.id_image = img.id_image
             WHERE p.id_plat = :id"
    );
    $stmt->execute([':id' => $id]);
    $plat = $stmt->fetch();
    return $plat ?: null;
  }

  /**
   * Crée un nouveau plat.
   * @param array $data ['nom', 'description', 'type_plat', 'id_image']
   * @return int L'ID du plat créé.
   */
  public static function create(array $data): int
  {
    $pdo = Database::getConnection();
    $sql = "INSERT INTO plat (nom, description, type_plat, id_image) 
                VALUES (:nom, :description, :type_plat, :id_image)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      ':nom'         => $data['nom'],
      ':description' => $data['description'],
      ':type_plat'   => $data['type_plat'],
      ':id_image'    => $data['id_image'] ?? null
    ]);

    return (int) $pdo->lastInsertId();
  }

  /**
   * Met à jour un plat existant.
   */
  public static function update(int $id, array $data): void
  {
    $pdo = Database::getConnection();
    $sql = "UPDATE plat SET 
                nom = :nom, 
                description = :description, 
                type_plat = :type_plat, 
                id_image = :id_image 
                WHERE id_plat = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      ':nom'         => $data['nom'],
      ':description' => $data['description'],
      ':type_plat'   => $data['type_plat'],
      ':id_image'    => $data['id_image'] ?? null,
      ':id'          => $id
    ]);
  }

  /**
   * Supprime un plat (uniquement s'il n'est rattaché à aucun menu).
   * @return bool True si supprimé, False si le plat est utilisé ailleurs.
   */
  public static function delete(int $id): bool
  {
    $pdo = Database::getConnection();

    // Vérification de l'intégrité : le plat est-il utilisé dans menu_plat ?
    $check = $pdo->prepare("SELECT COUNT(*) FROM menu_plat WHERE id_plat = :id");
    $check->execute([':id' => $id]);

    if ($check->fetchColumn() > 0) {
      return false; // On ne supprime pas un plat utilisé
    }

    $stmt = $pdo->prepare("DELETE FROM plat WHERE id_plat = :id");
    return $stmt->execute([':id' => $id]);
  }

  /**
   * Récupère le plat lié à un menu pour un type précis (ex: 'entree').
   * Très important pour l'édition des menus.
   */
  public static function getParMenuEtType(int $idMenu, string $type): ?array
  {
    $pdo = Database::getConnection();
    $sql = "SELECT p.*, img.url_image AS photo
                FROM plat p
                JOIN menu_plat mp ON mp.id_plat = p.id_plat
                LEFT JOIN image img ON p.id_image = img.id_image
                WHERE mp.id_menu = :id_menu AND p.type_plat = :type_plat
                LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      ':id_menu'   => $idMenu,
      ':type_plat' => $type
    ]);

    $plat = $stmt->fetch();
    return $plat ?: null;
  }

  /**
   * Synchronise le plat d'un menu pour un type donné.
   * Supprime l'ancienne liaison de ce type et insère la nouvelle.
   */
  public static function synchroniserMenuType(int $idMenu, string $type, int $idPlat): void
  {
    $pdo = Database::getConnection();

    // 1. On supprime la liaison existante pour ce TYPE (entrée, plat ou dessert) dans ce MENU
    $sqlDelete = "DELETE mp FROM menu_plat mp
                      JOIN plat p ON mp.id_plat = p.id_plat
                      WHERE mp.id_menu = :id_menu AND p.type_plat = :type_plat";

    $stmtDelete = $pdo->prepare($sqlDelete);
    $stmtDelete->execute([
      ':id_menu'   => $idMenu,
      ':type_plat' => $type
    ]);

    // 2. On ajoute la nouvelle liaison si un plat valide (> 0) a été choisi
    if ($idPlat > 0) {
      $sqlInsert = "INSERT INTO menu_plat (id_menu, id_plat) VALUES (:id_menu, :id_plat)";
      $stmtInsert = $pdo->prepare($sqlInsert);
      $stmtInsert->execute([
        ':id_menu' => $idMenu,
        ':id_plat' => $idPlat
      ]);
    }
  }
  /**
   * Synchronise les allergènes d'un plat.
   * @param int $idPlat
   * @param array $idsAllergenes Tableau d'entiers
   */
  public static function synchroniserAllergenes(int $idPlat, array $idsAllergenes): void
  {
    $pdo = Database::getConnection();

    // 1. On supprime les anciennes liaisons
    $stmt = $pdo->prepare("DELETE FROM plat_allergene WHERE id_plat = :id");
    $stmt->execute([':id' => $idPlat]);

    // 2. On insère les nouvelles
    if (!empty($idsAllergenes)) {
      $sql = "INSERT INTO plat_allergene (id_plat, id_allergene) VALUES (:id_plat, :id_all)";
      $stmtInsert = $pdo->prepare($sql);
      foreach ($idsAllergenes as $idAll) {
        $stmtInsert->execute([':id_plat' => $idPlat, ':id_all' => (int)$idAll]);
      }
    }
  }
}
