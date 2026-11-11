<?php
// app/src/Controllers/ApiController.php

namespace App\Controllers;

use App\Repositories\MenuRepository;

require_once __DIR__ . '/../Helpers/geolocalisation.php';

class ApiController
{

    public function filterMenus()
    {
        // pour être sûr que rien d'autre n'est envoyé :
        if (ob_get_length()) ob_clean();

        // Validation des paramètres
        $themes_valides  = ['classique', 'paques', 'noel', 'evenement'];
        $regimes_valides = ['classique', 'vegetarien', 'vegan'];

        $theme = (isset($_GET['theme']) && in_array($_GET['theme'], $themes_valides, true)) ? $_GET['theme'] : null;
        $regime = (isset($_GET['regime']) && in_array($_GET['regime'], $regimes_valides, true)) ? $_GET['regime'] : null;
        $prixMax = (isset($_GET['prix_max']) && is_numeric($_GET['prix_max'])) ? (float)$_GET['prix_max'] : null;
        $personnesMin = (isset($_GET['personnes_min']) && ctype_digit((string)$_GET['personnes_min'])) ? (int)$_GET['personnes_min'] : null;

        $repo = new MenuRepository();
        $menus = $repo->findWithFilters($theme, $regime, $prixMax, $personnesMin);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'menus' => $menus]);
        exit;
    }

    public function getFraisLivraison()
    {
        $ville = $_GET['ville'] ?? '';
        $cp = $_GET['code_postal'] ?? '';
        $adr = $_GET['adresse'] ?? '';

        if (empty($ville)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Ville manquante.']);
            return;
        }

        // On appelle la fonction de ton Helper
        $res = calculer_frais_livraison($adr, $cp, $ville);

        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => true], $res));
    }
}
