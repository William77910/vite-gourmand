<?php

/**
 * Calcul des frais de livraison selon la règle du cahier des charges :
 * 5 € de base + 0,59 €/km si l'adresse est hors Bordeaux.
 *
 * Le géocodage (conversion adresse -> coordonnées GPS) utilise l'API
 * Adresse du gouvernement français (api-adresse.data.gouv.fr), qui est
 * gratuite, publique, ne nécessite aucune clé, et est bien adaptée aux
 * adresses françaises.
 * Documentation : https://adresse.data.gouv.fr/api-doc/adresse
 */

// Définit les coordonnées GPS du centre de Bordeaux pour le calcul de distance
const VG_BORDEAUX_LAT = 44.837789;
const VG_BORDEAUX_LON = -0.579180;

/**
 * Convertit une adresse en coordonnées GPS via l'API Adresse.
 * Retourne null si l'adresse est introuvable ou si le service est indisponible
 * (on ne bloque jamais une commande a cause d'un service externe en panne).
 */
function geocoder_adresse(string $adresse, string $codePostal, string $ville): ?array
{
  $requete = trim($adresse . ' ' . $codePostal . ' ' . $ville);
  if ($requete === '') {
    return null;
  }

  $url = 'https://api-adresse.data.gouv.fr/search/?q=' . urlencode($requete) . '&limit=1';

  $contexte = stream_context_create(['http' => ['timeout' => 4]]);
  $reponse = @file_get_contents($url, false, $contexte);

  if ($reponse === false) {
    return null;
  }

  $donnees = json_decode($reponse, true);
  if (empty($donnees['features'][0]['geometry']['coordinates'])) {
    return null;
  }

  [$lon, $lat] = $donnees['features'][0]['geometry']['coordinates'];
  return ['lat' => (float) $lat, 'lon' => (float) $lon];
}

/**
 * Distance a vol d'oiseau entre deux points GPS (formule de Haversine),
 * suffisamment precise pour un calcul de frais de livraison.
 */
function distance_haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
{
  $rayonTerreKm = 6371;
  $dLat = deg2rad($lat2 - $lat1);
  $dLon = deg2rad($lon2 - $lon1);

  $a = sin($dLat / 2) ** 2
    + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
  $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

  return $rayonTerreKm * $c;
}

/**
 * Calcule les frais de livraison pour une adresse donnee.
 * Retourne : ['distance_km' => float|null, 'frais' => float, 'methode' => string]
 */
function calculer_frais_livraison(string $adresse, string $codePostal, string $ville): array
{
  $baseFixe = 5.0;
  $tarifKm = 0.59;

  if (mb_strtolower(trim($ville)) === 'bordeaux') {
    return ['distance_km' => 0.0, 'frais' => $baseFixe, 'methode' => 'bordeaux'];
  }

  $coordonnees = geocoder_adresse($adresse, $codePostal, $ville);

  if ($coordonnees === null) {
    // Adresse non geocodable ou service indisponible : forfait de
    // secours plutot que de bloquer la commande.
    return ['distance_km' => null, 'frais' => $baseFixe + 10.0, 'methode' => 'forfait_secours'];
  }

  $distance = distance_haversine(VG_BORDEAUX_LAT, VG_BORDEAUX_LON, $coordonnees['lat'], $coordonnees['lon']);
  $frais = $baseFixe + ($tarifKm * $distance);

  return [
    'distance_km' => round($distance, 1),
    'frais' => round($frais, 2),
    'methode' => 'km',
  ];
}
