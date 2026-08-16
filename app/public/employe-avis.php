<?php
require_once __DIR__ . '/../src/Helpers/auth.php';
exiger_role(['employe', 'administrateur']);
require_once __DIR__ . '/../src/Models/Avis.php';
require_once __DIR__ . '/../src/Helpers/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (verifier_token_csrf($_POST['csrf_token'] ?? null)) {
    $id = (int)$_POST['id_avis'];
    if ($_POST['action'] === 'valider') Avis::valider($id);
    if ($_POST['action'] === 'refuser') Avis::refuser($id);
    if ($_POST['action'] === 'depublier') Avis::depublier($id);
  }
}

$enAttente = Avis::getEnAttente();
$valides = Avis::getValides();

$current_page = 'employe';
$page_title = 'Modération des avis';
require_once __DIR__ . '/../views/partials/header.php';
?>

<div class="container my-5">
  <h1 class="text-center mb-5">Modération des avis clients</h1>

  <h2 class="h4 mb-4">En attente de validation (<?= count($enAttente) ?>)</h2>
  <div class="row g-4 mb-5">
    <?php foreach ($enAttente as $a): ?>
      <div class="col-md-6">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-header bg-white border-0 pt-3">
            <span class="badge bg-light text-dark border">Menu : <?= htmlspecialchars($a['titre_menu']) ?></span>
          </div>
          <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($a['prenom'] . ' ' . $a['nom']) ?></h5>
            <div class="text-warning mb-2"><?= str_repeat('★', $a['note']) . str_repeat('☆', 5 - $a['note']) ?></div>
            <p class="card-text">"<?= htmlspecialchars($a['commentaire']) ?>"</p>
          </div>
          <div class="card-footer bg-white border-0 pb-3">
            <form method="post" class="d-flex gap-2">
              <input type="hidden" name="csrf_token" value="<?= generer_token_csrf() ?>">
              <input type="hidden" name="id_avis" value="<?= $a['id_avis'] ?>">
              <button name="action" value="valider" class="btn btn-sm btn-success flex-grow-1">Valider</button>
              <button name="action" value="refuser" class="btn btn-sm btn-outline-danger flex-grow-1">Refuser</button>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <h2 class="h4 mb-4">Avis déjà publiés</h2>
  <div class="row g-4">
    <?php foreach ($valides as $a): ?>
      <div class="col-md-6">
        <div class="card h-100 border-0 shadow-sm opacity-75">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <strong><?= htmlspecialchars($a['prenom'] . ' ' . $a['nom']) ?></strong>
              <small class="text-muted"><?= htmlspecialchars($a['titre_menu']) ?></small>
            </div>
            <p class="mb-2 italic">"<?= htmlspecialchars($a['commentaire']) ?>"</p>
            <form method="post">
              <input type="hidden" name="csrf_token" value="<?= generer_token_csrf() ?>">
              <input type="hidden" name="id_avis" value="<?= $a['id_avis'] ?>">
              <button name="action" value="depublier" class="btn btn-sm btn-link text-danger p-0">Dépublier l'avis</button>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>