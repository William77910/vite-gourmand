<?php

/**
 * Mini-Runner de Tests Unitaires Natif
 * Utilisé pour valider les fonctions critiques du système sans dépendance externe.
 */

// 1. Inclusion des helpers à tester
require_once __DIR__ . '/../src/Helpers/mot_de_passe.php';
require_once __DIR__ . '/../src/Helpers/geolocalisation.php';

// 2. Compteurs de résultats
$succes = 0;
$echecs = 0;

/**
 * Fonction d'assertion personnalisée
 */
function affirmer(bool $condition, string $nomDuTest)
{
  global $succes, $echecs;
  if ($condition) {
    echo "✅ [SUCCÈS] : {$nomDuTest}\n";
    $succes++;
  } else {
    echo "❌ [ÉCHEC]  : {$nomDuTest}\n";
    $echecs++;
  }
}

echo "=========================================\n";
echo "    LANCEMENT DES TESTS UNITAIRES       \n";
echo "=========================================\n\n";

// --- TEST 1 : Validation de la complexité du Mot de passe ---
affirmer(
  mot_de_passe_valide("ViteGourmand2026!") === true,
  "Un mot de passe conforme doit être accepté"
);

affirmer(
  mot_de_passe_valide("court") === false,
  "Un mot de passe trop court doit être refusé"
);

affirmer(
  mot_de_passe_valide("sansmajuscule1!") === false,
  "Un mot de passe sans majuscule doit être refusé"
);

affirmer(
  mot_de_passe_valide("SANSCHIFFRE!") === false,
  "Un mot de passe sans chiffre doit être refusé"
);


// --- TEST 2 : Calcul des frais de livraison ---
$fraisBordeaux = calculer_frais_livraison("5, rue du centre", "33000", "Bordeaux");
affirmer(
  is_array($fraisBordeaux) && isset($fraisBordeaux['frais']),
  "Le calcul de frais doit retourner un tableau avec la clé 'frais'"
);

affirmer(
  $fraisBordeaux['frais'] >= 0,
  "Les frais de livraison ne doivent pas être négatifs"
);


// --- RÉCAPITULATIF ---
echo "\n=========================================\n";
echo "RÉSULTAT : {$succes} succès, {$echecs} échec(s).\n";
echo "=========================================\n";
