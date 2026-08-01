<?php

require_once __DIR__ . '/../../config/database.php';

/**
 * Modele Menu : centralise les requetes SQL liees aux menus.
 * Utilise des requetes preparees PDO partout (securite anti-injection).
 */
class Menu
{
    /**
     * Recupere tous les menus avec leur image principale.
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
     * Recupere les menus selon des filtres optionnels.
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
     * Recupere un menu precis par son identifiant.
     */
    public static function getById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM menu WHERE id_menu = :id');
        $stmt->execute([':id' => $id]);
        $menu = $stmt->fetch();
        return $menu ?: null;
    }

    /**
     * Recupere toutes les images d'un menu, dans l'ordre d'affichage prevu.
     */
    public static function getImages(int $idMenu): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            'SELECT url_image FROM image_menu WHERE id_menu = :id ORDER BY ordre_affichage'
        );
        $stmt->execute([':id' => $idMenu]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Recupere les plats composant un menu, avec la liste de leurs
     * allergenes regroupes en une seule chaine (GROUP_CONCAT).
     */
    public static function getPlatsAvecAllergenes(int $idMenu): array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT p.id_plat, p.nom, p.description, p.type_plat, p.photo,
                       GROUP_CONCAT(a.nom SEPARATOR ', ') AS allergenes
                FROM plat p
                JOIN menu_plat mp ON mp.id_plat = p.id_plat
                LEFT JOIN plat_allergene pa ON pa.id_plat = p.id_plat
                LEFT JOIN allergene a ON a.id_allergene = pa.id_allergene
                WHERE mp.id_menu = :id
                GROUP BY p.id_plat
                ORDER BY FIELD(p.type_plat, 'entree', 'plat', 'dessert')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $idMenu]);
        return $stmt->fetchAll();
    }
}
