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

  // 2. Repli sur le Simulateur (Résilience en cas de panne API)
  $villes_secours = [
    'bordeaux-33000'      => ['lat' => 44.8378, 'lon' => -0.5792],
    'merignac-33700'      => ['lat' => 44.8415, 'lon' => -0.6464],
    'pessac-33600'        => ['lat' => 44.8058, 'lon' => -0.6312],
    'talence-33400'       => ['lat' => 44.8080, 'lon' => -0.5901],
    'villenave-dornon-33140' => ['lat' => 44.7818, 'lon' => -0.5599],
    'begles-33130'        => ['lat' => 44.8078, 'lon' => -0.5484],
    'cenon-33150'         => ['lat' => 44.8569, 'lon' => -0.5312],
    'floirac-33270'       => ['lat' => 44.8361, 'lon' => -0.5262],
    'lormont-33310'       => ['lat' => 44.8785, 'lon' => -0.5222],
    'le-bouscat-33110'    => ['lat' => 44.8647, 'lon' => -0.5997],
    'eysines-33320'       => ['lat' => 44.8841, 'lon' => -0.6504],
    'bruges-33520'        => ['lat' => 44.8821, 'lon' => -0.6125],
    'gradignan-33170'     => ['lat' => 44.7709, 'lon' => -0.6171],
    'blanquefort-33290'   => ['lat' => 44.9110, 'lon' => -0.6384],
    'saint-medard-en-jalles-33160' => ['lat' => 44.8962, 'lon' => -0.7183],
    'libourne-33500'               => ['lat' => 44.9170, 'lon' => -0.2340],
    'saint-andre-de-cubzac-33240'  => ['lat' => 44.9920, 'lon' => -0.4420],
    'langon-33210'                 => ['lat' => 44.5530, 'lon' => -0.2450],
    'arcachon-33120'               => ['lat' => 44.6580, 'lon' => -1.1700],
    'la-teste-de-buch-33260'       => ['lat' => 44.6290, 'lon' => -1.1410]
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
  $dLat = deg2rad($lat2 - $lat1); // Différence de latitude en radians
  $dLon = deg2rad($lon2 - $lon1); // Différence de longitude en radians
  $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
  return $r * 2 * atan2(sqrt($a), sqrt(1 - $a));
}

/**
 * Calcule les frais de livraison finaux
 */
function calculer_frais_livraison(string $adresse, string $codePostal, string $ville): array
{
  $baseFixe = 5.0; // Frais de livraison de base
  $tarifKm = 0.59; // Tarif par km hors Bordeaux

  if (strtolower(trim($ville)) === 'bordeaux') {
    return ['distance_km' => 0.0, 'frais' => $baseFixe, 'source' => 'Zone Locale'];
  }

  $resultat = geocoder_adresse($adresse, $codePostal, $ville);

  if ($resultat === null) {
    return ['distance_km' => null, 'frais' => $baseFixe + 10.0, 'source' => 'Forfait de secours'];
  }
  // Calcul de la distance à vol d'oiseau
  $dist = distance_haversine(VG_BORDEAUX_LAT, VG_BORDEAUX_LON, $resultat['lat'], $resultat['lon']);
  // Calcul des frais de livraison
  $frais = $baseFixe + ($tarifKm * $dist);

  return [
    'distance_km' => round($dist, 1),
    'frais' => round($frais, 2),
    'source' => $resultat['source']
  ];
}
