<?php

require_once __DIR__ . '/../../config/database.php';

/**
 * Modèle Menu : centralise les requêtes SQL liées aux menus.
 * Utilise des requêtes préparées PDO partout (sécurité anti-injection).
 */
class Menu
{
  /**
   * Récupère tous les menus avec leur image principale.
   */
  public static function getAll(): array
  {
    $pdo = Database::getConnection();

    $sql = "SELECT m.*,
                       (SELECT url_image FROM image_menu im
                        WHERE im.id_menu = m.id_menu
                        ORDER BY im.ordre_affichage LIMIT 1) AS image_principale
                FROM menu m
                ORDER BY m.titre";

    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
  }

  /**
   * Récupère les menus selon des filtres optionnels.
   * $filtres peut contenir : theme, regime, prix_max, personnes_min
   */
  public static function getFiltered(array $filtres): array
  {
    $pdo = Database::getConnection();

    $conditions = [];
    $params = [];

    if (!empty($filtres['theme'])) {
      $conditions[] = 'm.theme = :theme';
      $params[':theme'] = $filtres['theme'];
    }

    if (!empty($filtres['regime'])) {
      $conditions[] = 'm.regime = :regime';
      $params[':regime'] = $filtres['regime'];
    }

    if (!empty($filtres['prix_max'])) {
      $conditions[] = 'm.prix_personne <= :prix_max';
      $params[':prix_max'] = $filtres['prix_max'];
    }

    if (!empty($filtres['personnes_min'])) {
      $conditions[] = 'm.nb_personnes_min <= :personnes_min';
      $params[':personnes_min'] = $filtres['personnes_min'];
    }

    $sql = "SELECT m.*,
                       (SELECT url_image FROM image_menu im
                        WHERE im.id_menu = m.id_menu
                        ORDER BY im.ordre_affichage LIMIT 1) AS image_principale
                FROM menu m";

    if (!empty($conditions)) {
      $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    $sql .= ' ORDER BY m.titre';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
  }

  /**
   * Récupère un menu précis par son identifiant.
   */
  public static function getById(int $id): ?array
  {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('SELECT * FROM menu WHERE id_menu = :id');
    $stmt->execute([':id' => $id]);
    $menu = $stmt->fetch();
    return $menu ?: null;
  }
}
