<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/Image.php';

/**
 * Modèle Menu : Gère la logique métier des menus gastronomiques.
 * Intègre la gestion du Soft Delete et la composition dynamique via les plats.
 */
class Menu
{
  /**
   * Récupère tous les menus ACTIFS avec leur image principale.
   * Utilisé pour la page catalogue et la gestion admin.
   */
  public static function getAll(): array
  {
    $pdo = Database::getConnection();

    $sql = "SELECT m.*, 
                (SELECT img.url_image FROM image_menu im 
                 JOIN image img ON img.id_image = im.id_image 
                 WHERE im.id_menu = m.id_menu 
                 ORDER BY im.ordre_affichage LIMIT 1) AS image_principale
                FROM menu m 
                WHERE m.est_actif = 1
                ORDER BY m.titre";

    return $pdo->query($sql)->fetchAll();
  }

  /**
   * Récupère les menus actifs selon des filtres spécifiques.
   */
  public static function getFiltered(array $filtres): array
  {
    $pdo = Database::getConnection();
    $conditions = ['m.est_actif = 1'];
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
                (SELECT img.url_image FROM image_menu im 
                 JOIN image img ON img.id_image = im.id_image 
                 WHERE im.id_menu = m.id_menu 
                 ORDER BY im.ordre_affichage LIMIT 1) AS image_principale
                FROM menu m";

    $sql .= ' WHERE ' . implode(' AND ', $conditions);
    $sql .= ' ORDER BY m.titre';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
  }

  /**
   * Récupère un menu par son ID.
   */
  public static function getById(int $id): ?array
  {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('SELECT * FROM menu WHERE id_menu = :id');
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
  }

  /**
   * NOUVELLE MÉTHODE : Récupère uniquement titres et IDs pour les filtres stats.
   */
  public static function getAllActifs(): array
  {
    $pdo = Database::getConnection();
    $sql = "SELECT id_menu, titre FROM menu WHERE est_actif = 1 ORDER BY titre ASC";
    return $pdo->query($sql)->fetchAll();
  }

  /**
   * Crée un nouveau menu.
   */
  public static function create(array $data): int
  {
    $pdo = Database::getConnection();
    $sql = "INSERT INTO menu (titre, description, theme, regime, nb_personnes_min, prix_personne, conditions_menu, stock_disponible, est_actif)
                VALUES (:titre, :description, :theme, :regime, :nb_personnes_min, :prix_personne, :conditions_menu, :stock_disponible, 1)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      ':titre' => $data['titre'],
      ':description' => $data['description'],
      ':theme' => $data['theme'],
      ':regime' => $data['regime'],
      ':nb_personnes_min' => $data['nb_personnes_min'],
      ':prix_personne' => $data['prix_personne'],
      ':conditions_menu' => $data['conditions_menu'],
      ':stock_disponible' => $data['stock_disponible'],
    ]);
    return (int) $pdo->lastInsertId();
  }

  /**
   * Met à jour un menu existant.
   */
  public static function update(int $id, array $data): void
  {
    $pdo = Database::getConnection();
    $sql = "UPDATE menu SET
                titre = :titre, description = :description, theme = :theme, regime = :regime,
                nb_personnes_min = :nb_personnes_min, prix_personne = :prix_personne,
                conditions_menu = :conditions_menu, stock_disponible = :stock_disponible
                WHERE id_menu = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      ':titre' => $data['titre'],
      ':description' => $data['description'],
      ':theme' => $data['theme'],
      ':regime' => $data['regime'],
      ':nb_personnes_min' => $data['nb_personnes_min'],
      ':prix_personne' => $data['prix_personne'],
      ':conditions_menu' => $data['conditions_menu'],
      ':stock_disponible' => $data['stock_disponible'],
      ':id' => $id,
    ]);
  }

  /**
   * Soft Delete : On désactive le menu au lieu de le supprimer physiquement.
   */
  public static function delete(int $id): void
  {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("UPDATE menu SET est_actif = 0 WHERE id_menu = :id");
    $stmt->execute([':id' => $id]);
  }

  /**
   * Récupère les IDs et URLs des images d'ambiance pour la galerie admin.
   */
  public static function getImagesAvecId(int $idMenu): array
  {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare(
      "SELECT im.id_image_menu AS id_image, img.url_image 
             FROM image_menu im
             JOIN image img ON img.id_image = im.id_image
             WHERE im.id_menu = :id ORDER BY im.ordre_affichage"
    );
    $stmt->execute([':id' => $idMenu]);
    return $stmt->fetchAll();
  }

  /**
   * Ajoute une photo à la galerie du menu.
   */
  public static function definirPhotoAmbiance(int $idMenu, string $urlImage): void
  {
    $pdo = Database::getConnection();
    $idImage = Image::creerOuRecuperer($urlImage);

    $stmtOrdre = $pdo->prepare('SELECT COALESCE(MAX(ordre_affichage), -1) + 1 FROM image_menu WHERE id_menu = :id');
    $stmtOrdre->execute([':id' => $idMenu]);
    $ordre = (int) $stmtOrdre->fetchColumn();

    $stmt = $pdo->prepare('INSERT INTO image_menu (id_menu, id_image, ordre_affichage) VALUES (:menu, :image, :ordre)');
    $stmt->execute([':menu' => $idMenu, ':image' => $idImage, ':ordre' => $ordre]);
  }

  /**
   * Supprime une liaison image_menu.
   */
  public static function supprimerImage(int $idImageMenu, int $idMenu): void
  {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('DELETE FROM image_menu WHERE id_image_menu = :id AND id_menu = :menu');
    $stmt->execute([':id' => $idImageMenu, ':menu' => $idMenu]);
  }

  /**
   * Assemble la galerie complète (Ambiance + Photos des plats liés).
   */
  public static function getGalerieComplete(int $idMenu): array
  {
    $pdo = Database::getConnection();

    // 1. Photos d'ambiance
    $stmtAmbiance = $pdo->prepare(
      "SELECT img.url_image FROM image_menu im
             JOIN image img ON img.id_image = im.id_image
             WHERE im.id_menu = :id ORDER BY im.ordre_affichage"
    );
    $stmtAmbiance->execute([':id' => $idMenu]);
    $ambiance = $stmtAmbiance->fetchAll(PDO::FETCH_COLUMN);

    // 2. Photos des plats liés
    $stmtPlats = $pdo->prepare(
      "SELECT img.url_image FROM menu_plat mp
             JOIN plat p ON p.id_plat = mp.id_plat
             JOIN image img ON img.id_image = p.id_image
             WHERE mp.id_menu = :id
             ORDER BY FIELD(p.type_plat, 'entree', 'plat', 'dessert')"
    );
    $stmtPlats->execute([':id' => $idMenu]);
    $platsImages = $stmtPlats->fetchAll(PDO::FETCH_COLUMN);

    return array_merge($ambiance, $platsImages);
  }

  /**
   * Récupère les plats d'un menu avec leurs allergènes groupés.
   */
  public static function getPlatsAvecAllergenes(int $idMenu): array
  {
    $pdo = Database::getConnection();
    $sql = "SELECT p.*, img.url_image AS photo,
                       GROUP_CONCAT(a.nom SEPARATOR ', ') AS allergenes
                FROM plat p
                JOIN menu_plat mp ON mp.id_plat = p.id_plat
                LEFT JOIN image img ON p.id_image = img.id_image
                LEFT JOIN plat_allergene pa ON p.id_plat = pa.id_plat
                LEFT JOIN allergene a ON pa.id_allergene = a.id_allergene
                WHERE mp.id_menu = :id
                GROUP BY p.id_plat
                ORDER BY FIELD(p.type_plat, 'entree', 'plat', 'dessert')";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $idMenu]);
    return $stmt->fetchAll();
  }
}
