<?php
require_once __DIR__ . '/../src/Helpers/auth.php';
exiger_role(['employe', 'administrateur']);

require_once __DIR__ . '/../src/Helpers/csrf.php';
require_once __DIR__ . '/../src/Models/Allergene.php';

$messages = ['succes' => [], 'erreurs' => []];

// --- TRAITEMENT DES ACTIONS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!verifier_token_csrf($_POST['csrf_token'] ?? null)) {
    $messages['erreurs'][] = "Session expirée.";
  } else {
    $action = $_POST['action'] ?? '';

    // AJOUTER UN ALLERGÈNE
    if ($action === 'ajouter') {
      $nom = trim($_POST['nom'] ?? '');
      if (strlen($nom) < 2) {
        $messages['erreurs'][] = "Le nom de l'allergène est trop court.";
      } else {
        if (Allergene::create($nom)) {
          $messages['succes'][] = "Nouvel allergène ajouté : " . htmlspecialchars($nom);
        } else {
          $messages['erreurs'][] = "Cet allergène existe déjà.";
        }
      }
    }

    // SUPPRIMER UN ALLERGÈNE
    if ($action === 'supprimer') {
      $id = (int)($_POST['id_allergene'] ?? 0);
      if (Allergene::delete($id)) {
        $messages['succes'][] = "Allergène supprimé.";
      } else {
        $messages['erreurs'][] = "Erreur lors de la suppression.";
      }
    }
  }
}

$allergenes = Allergene::getAll();
$current_page = 'employe';
$page_title = 'Gestion des allergènes';
require_once __DIR__ . '/../views/partials/header.php';
?>

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-lg-6">
      <h1 class="text-center mb-4">Gestion des Allergènes</h1>

      <?php foreach ($messages['succes'] as $m): ?><div class="alert alert-success"><?= $m ?></div><?php endforeach; ?>
      <?php foreach ($messages['erreurs'] as $m): ?><div class="alert alert-danger"><?= $m ?></div><?php endforeach; ?>

      <!-- FORMULAIRE D'AJOUT -->
      <div class="vg-texte-presentation p-4 border rounded shadow-sm bg-white mb-5">
        <h2 class="h5 mb-3">Ajouter un allergène</h2>
        <form method="post" class="d-flex gap-2">
          <input type="hidden" name="csrf_token" value="<?= generer_token_csrf() ?>">
          <input type="hidden" name="action" value="ajouter">
          <input type="text" name="nom" class="form-control vg-input" placeholder="Ex: Soja, Lupin..." required>
          <button type="submit" class="btn btn-vg-primary">Ajouter</button>
        </form>
      </div>

      <!-- LISTE DES ALLERGÈNES -->
      <div class="bg-white border rounded shadow-sm">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th class="ps-4">Nom de l'allergène</th>
              <th class="text-end pe-4">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($allergenes as $all): ?>
              <tr>
                <td class="ps-4"><?= htmlspecialchars($all['nom']) ?></td>
                <td class="text-end pe-4">
                  <form method="post" onsubmit="return confirm('Supprimer cet allergène ? Cela le retirera de tous les plats liés.');">
                    <input type="hidden" name="csrf_token" value="<?= generer_token_csrf() ?>">
                    <input type="hidden" name="id_allergene" value="<?= $all['id_allergene'] ?>">
                    <input type="hidden" name="action" value="supprimer">
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                      <i class="bi bi-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="mt-4">
        <a href="/espace-employe.php" class="btn btn-link text-secondary vg-link-accessible">
          <i class="bi bi-arrow-left"></i> Retour à l'espace employé
        </a>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>