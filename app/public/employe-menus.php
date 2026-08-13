<?php
require_once __DIR__ . '/../src/Helpers/auth.php';
exiger_role(['employe', 'administrateur']);

require_once __DIR__ . '/../src/Helpers/csrf.php';
require_once __DIR__ . '/../src/Models/Menu.php';

$messages = ['succes' => [], 'erreurs' => []];

// TRAITEMENT DE LA SUPPRESSION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'supprimer_menu') {
  if (!verifier_token_csrf($_POST['csrf_token'] ?? null)) {
    $messages['erreurs'][] = "Votre session a expiré.";
  } else {
    $idASupprimer = (int)($_POST['id_menu'] ?? 0);
    if ($idASupprimer > 0) {
      Menu::delete($idASupprimer);
      $messages['succes'][] = "Le menu a été supprimé avec succès.";
    }
  }
}

$menus = Menu::getAll();

$current_page = 'employe';
$page_title = 'Gestion des menus';
require_once __DIR__ . '/../views/partials/header.php';
?>

<div class="container my-5">
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h1 class="mb-0">Gestion des menus</h1>
    <a href="/employe-menu-form.php" class="btn btn-vg-primary">+ Créer un menu</a>
  </div>

  <?php foreach ($messages['succes'] as $m): ?>
    <div class="alert alert-success"><?= htmlspecialchars($m) ?></div>
  <?php endforeach; ?>
  <?php foreach ($messages['erreurs'] as $m): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($m) ?></div>
  <?php endforeach; ?>

  <div class="row g-4">
    <?php if (empty($menus)): ?>
      <p class="text-center vg-text-secondary">Aucun menu enregistré.</p>
    <?php endif; ?>

    <?php foreach ($menus as $menu): ?>
      <div class="col-md-4">
        <div class="vg-menu-card h-100 d-flex flex-column">
          <img src="<?= htmlspecialchars($menu['image_principale'] ?? '/images/menus/placeholder.jpg') ?>"
            alt="<?= htmlspecialchars($menu['titre']) ?>"
            style="height:200px; object-fit:cover;">
          <div class="vg-menu-card-body d-flex flex-column flex-grow-1">
            <div class="vg-menu-card-titre"><?= htmlspecialchars($menu['titre']) ?></div>
            <p class="mb-3"><?= number_format((float)$menu['prix_personne'], 2, ',', ' ') ?> € / personne</p>

            <div class="mt-auto d-flex gap-2">
              <a href="/employe-menu-form.php?id=<?= (int)$menu['id_menu'] ?>"
                class="btn btn-vg-secondary flex-grow-1">Modifier</a>

              <!-- Formulaire de suppression -->
              <form method="post" action="/employe-menus.php"
                onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce menu ? Cette action est irréversible.');">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generer_token_csrf()) ?>">
                <input type="hidden" name="id_menu" value="<?= (int)$menu['id_menu'] ?>">
                <input type="hidden" name="action" value="supprimer_menu">
                <button type="submit" class="btn btn-outline-danger">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>