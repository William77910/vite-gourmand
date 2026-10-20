<?php
require_once __DIR__ . '/../src/Helpers/auth.php';
exiger_role(['employe', 'administrateur']);

require_once __DIR__ . '/../src/Helpers/csrf.php';
require_once __DIR__ . '/../src/Models/Menu.php';

$messages = ['succes' => [], 'erreurs' => []];

// TRAITEMENT DE LA SUPPRESSION (Soft Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'supprimer_menu') {
  if (!verifier_token_csrf($_POST['csrf_token'] ?? null)) {
    $messages['erreurs'][] = "Votre session a expiré.";
  } else {
    $idASupprimer = (int)($_POST['id_menu'] ?? 0);
    if ($idASupprimer > 0) {
      Menu::delete($idASupprimer);
      $messages['succes'][] = "Le menu a été archivé avec succès.";
    }
  }
}

$menus = Menu::getAll();

$current_page = 'employe';
$page_title = 'Gestion des menus';
require_once __DIR__ . '/../views/partials/header.php';
?>

<section class="container my-5">
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h1 class="mb-0">Gestion des menus</h1>
    <a href="/employe-menu-form.php" class="btn btn-vg-primary">+ Créer un menu</a>
  </div>

  <?php foreach ($messages['succes'] as $m): ?>
    <div class="alert alert-success"><?= htmlspecialchars($m) ?></div>
  <?php endforeach; ?>

  <div class="row g-4">
    <?php if (empty($menus)): ?>
      <p class="text-center vg-text-secondary">Aucun menu actif enregistré.</p>
    <?php endif; ?>

    <?php foreach ($menus as $menu): ?>
      <div class="col-md-4">
        <div class="vg-menu-card h-100 d-flex flex-column shadow-sm">
          <img src="<?= htmlspecialchars($menu['image_principale'] ?? '/images/menus/placeholder.jpg') ?>"
            alt="<?= htmlspecialchars($menu['titre']) ?>"
            style="height:180px; object-fit:cover;">

          <div class="vg-menu-card-body d-flex flex-column flex-grow-1">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <div class="vg-menu-card-titre h5 mb-0"><?= htmlspecialchars($menu['titre']) ?></div>

              <!-- AFFICHAGE DU STOCK (NOUVEAU) -->
              <?php
              $stock = (int)$menu['stock_disponible'];
              $badgeClass = 'bg-success';
              if ($stock <= 0) $badgeClass = 'bg-danger';
              elseif ($stock <= 5) $badgeClass = 'bg-warning text-dark';
              ?>
              <span class="badge <?= $badgeClass ?>" title="Quantité restante">
                Stock : <?= $stock ?>
              </span>
            </div>

            <p class="mb-3 text-vg-primary fw-bold">
              <?= number_format((float)$menu['prix_personne'], 2, ',', ' ') ?> € / pers.
            </p>

            <div class="mt-auto d-flex gap-2">
              <a href="/employe-menu-form.php?id=<?= (int)$menu['id_menu'] ?>"
                class="btn btn-sm btn-vg-secondary flex-grow-1">Modifier</a>

              <form method="post" action="/employe-menus.php"
                onsubmit="return confirm('Archiver ce menu ? Il ne sera plus visible par les clients.');">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generer_token_csrf()) ?>">
                <input type="hidden" name="id_menu" value="<?= (int)$menu['id_menu'] ?>">
                <input type="hidden" name="action" value="supprimer_menu">
                <button type="submit" class="btn btn-sm btn-outline-danger">
                  <i class="bi bi-archive"></i>
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <div class="mt-4">
    <a href="/espace-employe.php" class="btn btn-link text-secondary vg-link-accessible">
      <i class="bi bi-arrow-left"></i> Retour à l'espace employé
    </a>
  </div>
</section>

<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>