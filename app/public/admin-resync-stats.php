<!--
* Ce script est destiné à être exécuté par un administrateur pour resynchroniser les statistiques des commandes
* depuis la base de données MySQL vers MongoDB.
* Il vide d'abord la collection MongoDB, puis récupère toutes les commandes MySQL avec les informations nécessaires,
* et les insère dans MongoDB.
*
* Sécurité : Seul un administrateur peut exécuter ce script.

* NOTE : Ce script est destiné à un usage ponctuel et ne doit pas être exposé publiquement.
* Il est recommandé de le protéger par un mot de passe ou de le supprimer après utilisation.
-->
<!-- mettre toute la page en commantaire pour éviter qu'elle ne soit exécutée par un navigateur -->

<?php
/**
 * ATTENTION : Ce script de maintenance est neutralisé par sécurité.
 * Il a servi à la resynchronisation initiale entre MySQL et MongoDB.
 * Pour le réactiver, décommentez le bloc ci-dessous.
 */

/*
require_once __DIR__ . '/../src/Helpers/auth.php';
exiger_role(['administrateur']);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mongodb.php';


try {
  $pdo = Database::getConnection();
  $mongo = MongoConnection::getDatabase();
  $collection = $mongo->selectCollection('commandes_stats');

  // 1. On vide la collection MongoDB pour repartir de zéro
  $collection->deleteMany([]);

  // 2. On récupère TOUTES les commandes MySQL avec les infos nécessaires
  // On joint 'menu' pour le titre et on cherche le DERNIER statut dans l'historique
  $sql = "SELECT 
                c.id_commande, 
                c.id_menu, 
                c.prix_total as montant, 
                c.nb_personnes, 
                c.date_creation,
                m.titre as titre_menu,
                (SELECT statut FROM historique_statut h 
                 WHERE h.id_commande = c.id_commande 
                 ORDER BY h.date_modification DESC LIMIT 1) as dernier_statut
            FROM commande c
            JOIN menu m ON c.id_menu = m.id_menu";

  $stmt = $pdo->query($sql);
  $commandes = $stmt->fetchAll();

  $count = 0;
  foreach ($commandes as $cmd) {
    // 3. On prépare le document pour MongoDB (Format identique à notre helper)
    $timestamp = strtotime($cmd['date_creation']);

    $document = [
      'id_commande'   => (int)$cmd['id_commande'],
      'id_menu'       => (int)$cmd['id_menu'],
      'titre_menu'    => $cmd['titre_menu'],
      'montant'       => (float)$cmd['montant'],
      'nb_personnes'  => (int)$cmd['nb_personnes'],
      'date_commande' => new MongoDB\BSON\UTCDateTime($timestamp * 1000),
      'statut'        => $cmd['dernier_statut'] ?? 'en_attente'
    ];

    $collection->insertOne($document);
    $count++;
  }

  $message = "Synchronisation terminée ! $count commandes ont été transférées vers MongoDB.";
  $type = "success";
} catch (Exception $e) {
  $message = "Erreur lors de la synchronisation : " . $e->getMessage();
  $type = "danger";
}

$page_title = "Maintenance Stats";
require_once __DIR__ . '/../views/partials/header.php';
?>

<div class="container my-5 text-center">
  <div class="alert alert-<?= $type ?> p-5">
    <i class="bi <?= $type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle' ?> fs-1"></i>
    <h1 class="mt-3"><?= $message ?></h1>
    <a href="/admin-stats.php" class="btn btn-primary mt-4">Voir les statistiques à jour</a>
  </div>
</div>

<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>
*/
header('HTTP/1.0 403 Forbidden');
die("Accès interdit : ce script de maintenance est désactivé.");
