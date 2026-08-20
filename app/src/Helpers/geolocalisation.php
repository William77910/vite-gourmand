<?php

/**
 * Helper de Géolocalisation - Version Finale Propre
 * Gère le calcul des frais de livraison (5€ base + 0.59€/km hors Bordeaux)
 */

if (!defined('VG_BORDEAUX_LAT')) {
  define('VG_BORDEAUX_LAT', 44.837789);
  define('VG_BORDEAUX_LON', -0.579180);
}

/**
 * Convertit une adresse en coordonnées GPS.
 * Tente l'API officielle, sinon bascule sur le simulateur interne.
 */
function geocoder_adresse(string $adresse, string $codePostal, string $ville): ?array
{
  $ville_clean = strtolower(trim($ville));
  $cp_clean = trim($codePostal);
  $requete = trim($adresse . ' ' . $codePostal . ' ' . $ville);

  // 1. Appel API via cURL (plus robuste sous Docker)
  if (function_exists('curl_init')) {
    $url = 'https://api-adresse.data.gouv.fr/search/?q=' . urlencode($requete) . '&limit=1';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Ignore les soucis de certificats SSL en local
    curl_setopt($ch, CURLOPT_USERAGENT, 'ViteEtGourmand-App');
    $reponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($reponse !== false && $httpCode === 200) {
      $donnees = json_decode($reponse, true);
      if (!empty($donnees['features'][0]['geometry']['coordinates'])) {
        [$lon, $lat] = $donnees['features'][0]['geometry']['coordinates'];
        return [
          'lat' => (float)$lat,
          'lon' => (float)$lon,
          'source' => 'API Gouv.fr'
        ];
      }
    }
  }

  // 2. Repli sur le Simulateur (Résilience démo)
  $villes_secours = [
    'paris-75000' => ['lat' => 48.8566, 'lon' => 2.3522],
    'paris-75002' => ['lat' => 48.8661, 'lon' => 2.3399],
    'nice-06300'  => ['lat' => 43.7102, 'lon' => 7.2620],
    'nice-06000'  => ['lat' => 43.7102, 'lon' => 7.2620],
    'lyon-69000'  => ['lat' => 45.7640, 'lon' => 4.8357],
    'aix-les-bains-73100' => ['lat' => 45.6885, 'lon' => 5.9153]
  ];

  $cle = $ville_clean . '-' . $cp_clean;
  if (array_key_exists($cle, $villes_secours)) {
    return array_merge($villes_secours[$cle], ['source' => 'Simulateur Interne']);
  }

  return null;
}

/**
 * Calcul de la distance à vol d'oiseau (Formule de Haversine)
 */
function distance_haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
{
  $r = 6371; // Rayon de la Terre en km
  $dLat = deg2rad($lat2 - $lat1);
  $dLon = deg2rad($lon2 - $lon1);
  $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
  return $r * 2 * atan2(sqrt($a), sqrt(1 - $a));
}

/**
 * Calcule les frais de livraison finaux
 */
function calculer_frais_livraison(string $adresse, string $codePostal, string $ville): array
{
  $baseFixe = 5.0;
  $tarifKm = 0.59;

  if (strtolower(trim($ville)) === 'bordeaux') {
    return ['distance_km' => 0.0, 'frais' => $baseFixe, 'source' => 'Zone Locale'];
  }

  $resultat = geocoder_adresse($adresse, $codePostal, $ville);

  if ($resultat === null) {
    return ['distance_km' => null, 'frais' => $baseFixe + 10.0, 'source' => 'Forfait de secours'];
  }

  $dist = distance_haversine(VG_BORDEAUX_LAT, VG_BORDEAUX_LON, $resultat['lat'], $resultat['lon']);
  $frais = $baseFixe + ($tarifKm * $dist);

  return [
    'distance_km' => round($dist, 1),
    'frais' => round($frais, 2),
    'source' => $resultat['source']
  ];
}
