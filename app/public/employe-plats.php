<?php
require_once __DIR__ . '/../src/Helpers/auth.php';
// Sécurité : Seuls les employés et admins accèdent à cette page
exiger_role(['employe', 'administrateur']);

require_once __DIR__ . '/../src/Models/Plat.php';
require_once __DIR__ . '/../src/Helpers/csrf.php';

$messages = ['succes' => [], 'erreurs' => []];

// GESTION DE LA SUPPRESSION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'supprimer_plat') {
  if (verifier_token_csrf($_POST['csrf_token'] ?? null)) {
    $idPlat = (int)$_POST['id_plat'];

    // La méthode delete() renvoie false si le plat est utilisé dans un menu
    if (Plat::delete($idPlat)) {
      $messages['succes'][] = "Le plat a été supprimé avec succès.";
    } else {
      $messages['erreurs'][] = "Impossible de supprimer ce plat : il est actuellement utilisé dans un menu actif. Retirez-le du menu avant de le supprimer.";
    }
  }
}

// Récupération de tous les plats pour l'affichage
$plats = Plat::getAll();

$current_page = 'employe';
$page_title = 'Gestion des plats';
require_once __DIR__ . '/../views/partials/header.php';
?>

<div class="container my-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Gestion de la carte (Plats)</h1>
    <a href="/employe-plat-form.php" class="btn btn-vg-primary">+ Ajouter un nouveau plat</a>
  </div>

  <!-- Affichage des messages de confirmation ou d'erreur -->
  <?php foreach ($messages['succes'] as $m): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($m) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endforeach; ?>

  <?php foreach ($messages['erreurs'] as $m): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($m) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endforeach; ?>

  <div class="vg-texte-presentation p-2 border rounded shadow-sm bg-white">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th style="width: 80px;">Photo</th>
            <th>Nom du plat</th>
            <th>Catégorie</th>
            <th>Description</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($plats)): ?>
            <tr>
              <td colspan="5" class="text-center py-4 text-muted">Aucun plat enregistré pour le moment.</td>
            </tr>
          <?php endif; ?>

          <?php foreach ($plats as $p): ?>
            <tr>
              <td>
                <img src="<?= $p['photo'] ?? '/images/menus/placeholder.jpg' ?>"
                  class="rounded shadow-sm"
                  style="width:60px; height:60px; object-fit:cover;"
                  alt="<?= htmlspecialchars($p['nom']) ?>">
              </td>
              <td>
                <strong><?= htmlspecialchars($p['nom']) ?></strong>
              </td>
              <td>
                <span class="badge rounded-pill bg-info text-dark">
                  <?= ucfirst(htmlspecialchars($p['type_plat'])) ?>
                </span>
              </td>
              <td>
                <div class="text-muted small" style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                  <?= htmlspecialchars($p['description']) ?>
                </div>
              </td>
              <td class="text-end">
                <div class="d-flex justify-content-end gap-2">
                  <a href="/employe-plat-form.php?id=<?= $p['id_plat'] ?>"
                    class="btn btn-sm btn-outline-secondary"
                    title="Modifier">
                    <i class="bi bi-pencil"></i>
                  </a>

                  <form method="post" onsubmit="return confirm('Supprimer définitivement ce plat ?');" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= generer_token_csrf() ?>">
                    <input type="hidden" name="id_plat" value="<?= $p['id_plat'] ?>">
                    <input type="hidden" name="action" value="supprimer_plat">
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                      <i class="bi bi-trash"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-4">
    <a href="/espace-employe.php" class="btn btn-link text-secondary">
      <i class="bi bi-arrow-left"></i> Retour à l'espace employé
    </a>
  </div>
</div>

<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>