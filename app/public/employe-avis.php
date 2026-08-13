<?php
require_once __DIR__ . '/../src/Helpers/auth.php';
exiger_role(['employe', 'administrateur']);

require_once __DIR__ . '/../src/Helpers/csrf.php';
require_once __DIR__ . '/../src/Models/Avis.php';

$messages = ['succes' => [], 'erreurs' => []];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!verifier_token_csrf($_POST['csrf_token'] ?? null)) {
    $messages['erreurs'][] = "Votre session a expiré, veuillez réessayer.";
  } else {
    $idAvis = (int) ($_POST['id_avis'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($action === 'valider') {
      Avis::valider($idAvis);
      $messages['succes'][] = "Avis publié.";
    } elseif ($action === 'refuser') {
      Avis::refuser($idAvis);
      $messages['succes'][] = "Avis refusé et supprimé.";
    } elseif ($action === 'depublier') {
      Avis::depublier($idAvis);
      $messages['succes'][] = "Avis dépublié.";
    }
  }
}

$avisEnAttente = Avis::getEnAttente();
$avisValides = Avis::getValides();

$current_page = 'employe';
$page_title = 'Modération des avis';
require_once __DIR__ . '/../views/partials/header.php';
?>

<div class="container my-5">
  <h1 class="text-center mb-4">Modération des avis clients</h1>

  <?php foreach ($messages['succes'] as $m): ?>
    <div class="alert alert-success"><?= htmlspecialchars($m) ?></div>
  <?php endforeach; ?>
  <?php foreach ($messages['erreurs'] as $m): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($m) ?></div>
  <?php endforeach; ?>

  <h2 class="mb-3">En attente de validation (<?= count($avisEnAttente) ?>)</h2>
  <?php if (empty($avisEnAttente)): ?>
    <p class="vg-text-secondary mb-5">Aucun avis en attente.</p>
  <?php endif; ?>

  <div class="row g-3 mb-5">
    <?php foreach ($avisEnAttente as $avis): ?>
      <div class="col-md-6">
        <div class="vg-avis-card">
          <div class="d-flex justify-content-between">
            <strong><?= htmlspecialchars($avis['prenom']) ?> <?= htmlspecialchars($avis['nom']) ?></strong>
            <span class="vg-text-secondary small"><?= htmlspecialchars($avis['titre_menu']) ?></span>
          </div>
          <div class="vg-avis-etoiles"><?= str_repeat('★', (int) $avis['note']) . str_repeat('☆', 5 - (int) $avis['note']) ?></div>
          <p class="mb-2">"<?= htmlspecialchars($avis['commentaire']) ?>"</p>

          <div class="d-flex gap-2">
            <form method="post" action="/employe-avis.php">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generer_token_csrf()) ?>">
              <input type="hidden" name="action" value="valider">
              <input type="hidden" name="id_avis" value="<?= (int) $avis['id_avis'] ?>">
              <button type="submit" class="btn btn-vg-primary">Valider</button>
            </form>
            <form method="post" action="/employe-avis.php" onsubmit="return confirm('Refuser (et supprimer) cet avis ?');">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generer_token_csrf()) ?>">
              <input type="hidden" name="action" value="refuser">
              <input type="hidden" name="id_avis" value="<?= (int) $avis['id_avis'] ?>">
              <button type="submit" class="btn btn-vg-secondary">Refuser</button>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <h2 class="mb-3">Avis publiés (<?= count($avisValides) ?>)</h2>
  <?php if (empty($avisValides)): ?>
    <p class="vg-text-secondary">Aucun avis publié pour le moment.</p>
  <?php endif; ?>

  <div class="row g-3">
    <?php foreach ($avisValides as $avis): ?>
      <div class="col-md-6">
        <div class="vg-avis-card">
          <div class="d-flex justify-content-between">
            <strong><?= htmlspecialchars($avis['prenom']) ?> <?= htmlspecialchars($avis['nom']) ?></strong>
            <span class="vg-text-secondary small"><?= htmlspecialchars($avis['titre_menu']) ?></span>
          </div>
          <div class="vg-avis-etoiles"><?= str_repeat('★', (int) $avis['note']) . str_repeat('☆', 5 - (int) $avis['note']) ?></div>
          <p class="mb-2">"<?= htmlspecialchars($avis['commentaire']) ?>"</p>

          <form method="post" action="/employe-avis.php" onsubmit="return confirm('Dépublier cet avis ?');">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generer_token_csrf()) ?>">
            <input type="hidden" name="action" value="depublier">
            <input type="hidden" name="id_avis" value="<?= (int) $avis['id_avis'] ?>">
            <button type="submit" class="btn btn-vg-secondary">Dépublier</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php
require_once __DIR__ . '/../views/partials/footer.php';
