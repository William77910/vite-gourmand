<?php

require_once __DIR__ . '/../../config/mongodb.php';

use MongoDB\BSON\UTCDateTime;

/**
 * Helper pour la synchronisation des statistiques vers MongoDB.
 */
function enregistrer_statistique_commande(array $dataCommande, string $titreMenu): void
{
  try {
    $db = MongoConnection::getDatabase();
    $collection = $db->selectCollection('commandes_stats');

    $titreNettoye = trim($titreMenu, " \t\n\r\0\x0B\"'");
    $timestamp = strtotime($dataCommande['date_creation'] ?? 'now');
    $mongoDate = new UTCDateTime($timestamp * 1000);

    $document = [
      'id_commande'   => (int)$dataCommande['id_commande'],
      'id_menu'       => (int)$dataCommande['id_menu'],
      'titre_menu'    => $titreNettoye,
      'montant'       => (float)$dataCommande['prix_total'],
      'nb_personnes'  => (int)$dataCommande['nb_personnes'],
      'date_commande' => $mongoDate,
      'statut'        => 'en_attente'
    ];

    $collection->insertOne($document);
  } catch (Exception $e) {
    error_log("Erreur Stats MongoDB : " . $e->getMessage());
  }
}

/**
 * Met à jour les données d'une commande dans MongoDB.
 */
function mettre_a_jour_commande_mongo(int $idCommande, array $newData): void
{
  try {
    $db = MongoConnection::getDatabase();
    $collection = $db->selectCollection('commandes_stats');

    $updateFields = [];
    if (isset($newData['statut']))       $updateFields['statut'] = $newData['statut'];
    if (isset($newData['montant']))      $updateFields['montant'] = (float)$newData['montant'];
    if (isset($newData['nb_personnes'])) $updateFields['nb_personnes'] = (int)$newData['nb_personnes'];

    if (isset($newData['titre_menu'])) {
      $updateFields['titre_menu'] = trim($newData['titre_menu'], " \t\n\r\0\x0B\"'");
    }

    $collection->updateOne(
      ['id_commande' => $idCommande],
      ['$set' => $updateFields],
      ['upsert' => true]
    );
  } catch (Exception $e) {
    error_log("Erreur Update MongoDB : " . $e->getMessage());
  }
}

/**
 * Récupère le volume de commandes (Activité globale)
 * Filtres : Dates et Titre de menu
 */
function obtenir_stats_commandes_par_menu(?string $dateDebut = null, ?string $dateFin = null, ?string $filtreMenu = null): array
{
  try {
    $db = MongoConnection::getDatabase();
    $collection = $db->selectCollection('commandes_stats');

    $pipeline = [];
    $match = [];

    // 1. Filtre par date
    if ($dateDebut || $dateFin) {
      if ($dateDebut) $match['date_commande']['$gte'] = new UTCDateTime(strtotime($dateDebut . " 00:00:00") * 1000);
      if ($dateFin)   $match['date_commande']['$lte'] = new UTCDateTime(strtotime($dateFin . " 23:59:59") * 1000);
    }

    // 2. Filtre par titre de menu (si renseigné)
    if ($filtreMenu) {
      $match['titre_menu'] = $filtreMenu;
    }

    // 3. On exclut les annulations du volume d'activité
    $match['statut'] = ['$ne' => 'annulee'];

    $pipeline[] = ['$match' => $match];

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
 * Calcule le CA (Uniquement commandes validées)
 * Filtres : Dates et Titre de menu
 */
function obtenir_ca_par_menu(?string $dateDebut = null, ?string $dateFin = null, ?string $filtreMenu = null): array
{
  try {
    $db = MongoConnection::getDatabase();
    $collection = $db->selectCollection('commandes_stats');

    $pipeline = [];
    $match = [];

    // 1. Filtre par date
    if ($dateDebut || $dateFin) {
      if ($dateDebut) $match['date_commande']['$gte'] = new UTCDateTime(strtotime($dateDebut . " 00:00:00") * 1000);
      if ($dateFin)   $match['date_commande']['$lte'] = new UTCDateTime(strtotime($dateFin . " 23:59:59") * 1000);
    }

    // 2. Filtre par titre de menu
    if ($filtreMenu) {
      $match['titre_menu'] = $filtreMenu;
    }

    // 3. LOGIQUE MÉTIER CA : On ne compte que les commandes validées, en cours ou terminées.
    // On exclut 'en_attente' (pas encore validé par l'employé) et 'annulee'.
    $match['statut'] = ['$in' => ['accepte', 'en_preparation', 'en_cours_de_livraison', 'livre', 'terminee', 'en_attente_retour_materiel']];

    $pipeline[] = ['$match' => $match];
    // 4. On regroupe par titre de menu et on calcule le CA total
    $pipeline[] = ['$group' => [
      '_id' => '$titre_menu',
      'ca_total' => ['$sum' => '$montant']
    ]];

    // 5. On retourne les résultats sous forme de tableau associatif [titre_menu => ca_total]
    $cursor = $collection->aggregate($pipeline);
    $resultats = [];
    foreach ($cursor as $doc) { // On convertit le montant en float pour éviter les problèmes de type
      $resultats[$doc['_id']] = (float)$doc['ca_total'];
    }
    return $resultats;
  } catch (Exception $e) { // On log l'erreur pour le débogage
    error_log("Erreur CA MongoDB : " . $e->getMessage());
    return []; // On retourne un tableau vide en cas d'erreur
  }
}
