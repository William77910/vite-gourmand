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
    'bordeaux-33000'      => ['lat' => 44.8372, 'lon' => -0.584345],
    'merignac-33700'      => ['lat' => 44.784130, 'lon' => -0.087226],
    'pessac-33600'        => ['lat' => 44.80583, 'lon' => -0.630386],
    'talence-33400'       => ['lat' => 44.8134094, 'lon' => -0.600507500000049],
    'villenave-dornon-33140' => ['lat' => 44.7695013, 'lon' => -0.544014],
    'begles-33130'        => ['lat' => 44.812649, 'lon' => -0.551078],
    'cenon-33150'         => ['lat' => 44.853209, 'lon' => -0.522079],
    'floirac-33270'       => ['lat' => 44.835601, 'lon' => -0.517768],
    'lormont-33310'       => ['lat' => 44.876407, 'lon' => -0.5182007],
    'le-bouscat-33110'    => ['lat' => 44.865299, 'lon' => -0.594785],
    'eysines-33320'       => ['lat' => 44.879826, 'lon' => -0.635789],
    'bruges-33520'        => ['lat' => 44.8693999, 'lon' => -0.5763499],
    'gradignan-33170'     => ['lat' => 44.7734342, 'lon' => -0.6166045],
    'blanquefort-33290'   => ['lat' => 44.91396, 'lon' => -0.63543],
    'saint-medard-en-jalles-33160' => ['lat' => 44.89359, 'lon' => -0.711531],
    'libourne-33500'               => ['lat' => 44.937431, 'lon' => -0.235289],
    'saint-andre-de-cubzac-33240'  => ['lat' => 44.988606, 'lon' => 0.450383],
    'langon-33210'                 => ['lat' => 44.555169, 'lon' => -0.251069],
    'arcachon-33120'               => ['lat' => 44.6601467, 'lon' => -1.167542],
    'la-teste-de-buch-33260'       => ['lat' => 44.5372009277, 'lon' => -1.15588998795]
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
