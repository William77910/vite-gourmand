<?php

require_once __DIR__ . '/../../config/mongodb.php';

// On utilise le namespace complet pour éviter les alias non détectés
use MongoDB\BSON\UTCDateTime;

/**
 * Helper pour la synchronisation des statistiques vers MongoDB.
 */
function enregistrer_statistique_commande(array $dataCommande, string $titreMenu): void
{
  try {
    $db = MongoConnection::getDatabase();
    $collection = $db->selectCollection('commandes_stats');

    // Sécurité : Si la date est invalide, on prend l'heure actuelle
    $timestamp = strtotime($dataCommande['date_creation'] ?? 'now');
    if ($timestamp === false) {
      $timestamp = time();
    }

    // Création de l'objet date MongoDB
    $mongoDate = new UTCDateTime($timestamp * 1000);
    // l'alert sur "UTCDateTime" est normale, c'est un objet BSON spécifique à MongoDB qui installé dans docker, mais pas dans l'IDE. Il n'y a pas d'erreur à craindre.

    $document = [
      'id_commande'   => (int)$dataCommande['id_commande'],
      'id_menu'       => (int)$dataCommande['id_menu'],
      'titre_menu'    => $titreMenu,
      'montant'       => (float)$dataCommande['prix_total'],
      'nb_personnes'  => (int)$dataCommande['nb_personnes'],
      'date_commande' => $mongoDate,
      'statut'        => 'en_attente'
    ];

    $collection->insertOne($document);
  } catch (Exception $e) {
    // On log l'erreur sans l'afficher pour ne pas bloquer les headers HTTP
    error_log("Erreur Stats MongoDB : " . $e->getMessage());
  }
}

/**
 * Récupère le volume de commandes par menu (uniquement celles non annulées) avec filtres de dates.
 */
function obtenir_stats_commandes_par_menu(?string $dateDebut = null, ?string $dateFin = null): array
{
  try {
    $db = MongoConnection::getDatabase();
    $collection = $db->selectCollection('commandes_stats');

    $pipeline = [];

    // 1. Filtre par date
    if ($dateDebut || $dateFin) {
      $filtreDate = [];
      if ($dateDebut) $filtreDate['$gte'] = new MongoDB\BSON\UTCDateTime(strtotime($dateDebut) * 1000);
      if ($dateFin)   $filtreDate['$lte'] = new MongoDB\BSON\UTCDateTime(strtotime($dateFin . " 23:59:59") * 1000);
      $pipeline[] = ['$match' => ['date_commande' => $filtreDate]];
    }

    // 2. IMPORTANT : On exclut les commandes annulées du volume
    $pipeline[] = ['$match' => ['statut' => ['$ne' => 'annulee']]];

    // 3. Groupement
    $pipeline[] = ['$group' => [
      '_id' => '$titre_menu',
      'total_commandes' => ['$sum' => 1]
    ]];

    $pipeline[] = ['$sort' => ['total_commandes' => -1]];

    $cursor = $collection->aggregate($pipeline);

    $resultats = [];
    foreach ($cursor as $doc) {
      $resultats[$doc['_id']] = $doc['total_commandes'];
    }
    return $resultats;
  } catch (Exception $e) {
    error_log("Erreur Volume MongoDB : " . $e->getMessage());
    return [];
  }
}

/**
 * Met à jour le statut d'une commande dans MongoDB.
 * Si la commande n'existe pas (ancienne commande MySQL), elle est créée.
 */
/**
 * Met à jour le statut et le montant d'une commande dans MongoDB.
 */
function mettre_a_jour_statut_mongo(int $idCommande, string $nouveauStatut, float $nouveauMontant = null): void
{
  try {
    $db = MongoConnection::getDatabase();
    $collection = $db->selectCollection('commandes_stats');

    $updateData = ['statut' => $nouveauStatut];

    // Si un nouveau montant est fourni (cas d'une modification), on le met à jour
    if ($nouveauMontant !== null) {
      $updateData['montant'] = $nouveauMontant;
    }

    $collection->updateOne(
      ['id_commande' => $idCommande],
      ['$set' => $updateData],
      ['upsert' => true]
    );
  } catch (Exception $e) {
    error_log("Erreur Update MongoDB : " . $e->getMessage());
  }
}

/**
 * Calcule le CA par menu avec filtres optionnels.
 * @param string|null $dateDebut Format Y-m-d
 * @param string|null $dateFin Format Y-m-d
 */
function obtenir_ca_par_menu(?string $dateDebut = null, ?string $dateFin = null): array
{
  try {
    $db = MongoConnection::getDatabase();
    $collection = $db->selectCollection('commandes_stats');

    $pipeline = [];

    // 1. Filtre par durée (si précisée)
    if ($dateDebut || $dateFin) {
      $filtreDate = [];
      if ($dateDebut) $filtreDate['$gte'] = new MongoDB\BSON\UTCDateTime(strtotime($dateDebut) * 1000);
      if ($dateFin)   $filtreDate['$lte'] = new MongoDB\BSON\UTCDateTime(strtotime($dateFin . " 23:59:59") * 1000);

      $pipeline[] = ['$match' => ['date_commande' => $filtreDate]];
    }

    // 2. Filtre pour exclure les commandes annulées (le CA ne compte que le validé/terminé)
    $pipeline[] = ['$match' => ['statut' => ['$ne' => 'annulee']]];

    // 3. Groupement et Somme
    $pipeline[] = ['$group' => [
      '_id' => '$titre_menu',
      'ca_total' => ['$sum' => '$montant']
    ]];

    $cursor = $collection->aggregate($pipeline);

    $resultats = [];
    foreach ($cursor as $doc) {
      $resultats[$doc['_id']] = (float)$doc['ca_total'];
    }
    return $resultats;
  } catch (Exception $e) {
    error_log("Erreur CA MongoDB : " . $e->getMessage());
    return [];
  }
}
