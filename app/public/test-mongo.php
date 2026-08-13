<?php
require_once __DIR__ . '/../src/Helpers/auth.php';
// Sécurité : Réservé à l'admin ou aux employés
exiger_role(['employe', 'administrateur']);

require_once __DIR__ . '/../config/mongodb.php';

$commandesMongo = [];
$erreur = null;

try {
  $db = MongoConnection::getDatabase();
  $collection = $db->selectCollection('commandes_stats');

  // Récupération de tous les documents MongoDB, triés par date décroissante
  $commandesMongo = $collection->find([], ['sort' => ['date_commande' => -1]])->toArray();
} catch (Exception $e) {
  $erreur = $e->getMessage();
}

$current_page = 'employe';
$page_title = 'Test MongoDB';
require_once __DIR__ . '/../views/partials/header.php';
?>

<div class="container my-5">
  <h1>Contenu de MongoDB (`commandes_stats`)</h1>

  <?php if ($erreur): ?>
    <div class="alert alert-danger">
      <strong>Erreur MongoDB :</strong> <?= htmlspecialchars($erreur) ?>
    </div>
  <?php endif; ?>

  <p class="text-muted">Nombre de documents trouvés : <strong><?= count($commandesMongo) ?></strong></p>

  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead class="table-dark">
        <tr>
          <th>ID MySQL</th>
          <th>ID Menu</th>
          <th>Titre Menu</th>
          <th>Montant</th>
          <th>Personnes</th>
          <th>Date Commande</th>
          <th>Statut</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($commandesMongo)): ?>
          <tr>
            <td colspan="7" class="text-center text-muted">Aucune donnée statistique dans MongoDB.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($commandesMongo as $doc): ?>
            <tr>
              <td>#<?= $doc['id_commande'] ?? 'N/A' ?></td>
              <td><?= $doc['id_menu'] ?? 'N/A' ?></td>
              <td><strong><?= htmlspecialchars($doc['titre_menu'] ?? 'Inconnu') ?></strong></td>
              <td><?= number_format((float)($doc['montant'] ?? 0), 2, ',', ' ') ?> €</td>
              <td><?= $doc['nb_personnes'] ?? 'N/A' ?></td>
              <td>
                <?php
                if (isset($doc['date_commande']) && $doc['date_commande'] instanceof MongoDB\BSON\UTCDateTime) {
                  echo $doc['date_commande']->toDateTime()->format('d/m/Y H:i:s');
                } else {
                  echo 'Date invalide';
                }
                ?>
              </td>
              <!--l'alert sur "MongoDB\BSON\UTCDateTime" est normale, c'est un objet BSON spécifique à MongoDB qui installé dans docker, pas dans l'IDE. Il n'y a rien à craindre.
              -->
              <td><span class="badge bg-info text-dark"><?= htmlspecialchars($doc['statut'] ?? '') ?></span></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>