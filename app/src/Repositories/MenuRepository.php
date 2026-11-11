<?php
// app/src/Repositories/MenuRepository.php

namespace App\Repositories;

use App\Services\Database;
use PDO;

class MenuRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findWithFilters($theme = null, $regime = null, $prixMax = null, $personnesMin = null)
    {
        $sql = "SELECT m.*, i.url_image as image_principale 
        FROM menu m 
        LEFT JOIN image_menu im ON m.id_menu = im.id_menu AND im.ordre_affichage = 0
        LEFT JOIN image i ON im.id_image = i.id_image
        WHERE m.est_actif = 1";

        $params = [];

        if ($theme) {
            $sql .= " AND m.theme = :theme";
            $params['theme'] = $theme;
        }
        if ($regime) {
            $sql .= " AND m.regime = :regime";
            $params['regime'] = $regime;
        }
        if ($prixMax) {
            $sql .= " AND m.prix_personne <= :prixMax";
            $params['prixMax'] = $prixMax;
        }
        if ($personnesMin) {
            $sql .= " AND m.nb_personnes_min <= :personnesMin";
            $params['personnesMin'] = $personnesMin;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
