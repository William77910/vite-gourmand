<?php
// Ce fichier est un point d'entrée pour l'API de filtrage des menus. Il reçoit les paramètres de filtrage via la requête GET, valide ces paramètres,
// puis utilise le modèle Menu pour récupérer les menus correspondants depuis la base de données. Les résultats sont renvoyés au format JSON.
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../src/Models/Menu.php';

// Listes blanches : on n'accepte que des valeurs connues (defense en profondeur,
// en plus des requetes preparees deja utilisees dans le modele)
$themes_valides  = ['classique', 'paques', 'noel', 'evenement'];
$regimes_valides = ['classique', 'vegetarien', 'vegan'];

$filtres = [];
// On vérifie si les paramètres sont présents et valides avant de les ajouter aux filtres
if (!empty($_GET['theme']) && in_array($_GET['theme'], $themes_valides, true)) {
    $filtres['theme'] = $_GET['theme'];
}

if (!empty($_GET['regime']) && in_array($_GET['regime'], $regimes_valides, true)) {
    $filtres['regime'] = $_GET['regime'];
}

if (!empty($_GET['prix_max']) && is_numeric($_GET['prix_max'])) {
    $filtres['prix_max'] = (float) $_GET['prix_max'];
}

if (!empty($_GET['personnes_min']) && ctype_digit((string) $_GET['personnes_min'])) {
    $filtres['personnes_min'] = (int) $_GET['personnes_min'];
}

try {
    $menus = Menu::getFiltered($filtres);
    echo json_encode(['success' => true, 'menus' => $menus]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération des menus.']);
}
