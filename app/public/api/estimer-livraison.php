<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../src/Helpers/geolocalisation.php';

$adresse = trim($_GET['adresse'] ?? '');
$codePostal = trim($_GET['code_postal'] ?? '');
$ville = trim($_GET['ville'] ?? '');

if ($ville === '') {
    echo json_encode(['success' => false, 'message' => 'Ville manquante.']);
    exit;
}

$resultat = calculer_frais_livraison($adresse, $codePostal, $ville);

echo json_encode(['success' => true] + $resultat);
