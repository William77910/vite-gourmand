<?php
// On démarre la mise en tampon pour capturer d'éventuels espaces ou erreurs
ob_start();

// On désactive l'affichage des erreurs qui pollueraient le JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/../../src/Helpers/geolocalisation.php';

    $ville = $_GET['ville'] ?? '';
    $cp = $_GET['code_postal'] ?? '';
    $adr = $_GET['adresse'] ?? '';

    if (empty($ville)) {
        // On vide le tampon avant d'envoyer l'erreur
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Ville manquante.']);
        exit;
    }

    $res = calculer_frais_livraison($adr, $cp, $ville);

    // On vide le tampon pour être certain qu'il ne reste que notre JSON
    ob_clean();
    echo json_encode(array_merge(['success' => true], $res));
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur interne.']);
}

// On termine et on envoie
ob_end_flush();
