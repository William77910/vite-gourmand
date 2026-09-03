<?php
require_once __DIR__ . '/../src/Helpers/auth.php';
// Sécurité : Seul l'administrateur peut entrer ici
exiger_role(['administrateur']);

require_once __DIR__ . '/../src/Models/Utilisateur.php';
require_once __DIR__ . '/../src/Helpers/csrf.php';

$messages = ['succes' => [], 'erreurs' => []];

// GESTION DU POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!verifier_token_csrf($_POST['csrf_token'] ?? null)) {
    $messages['erreurs'][] = "Session expirée, réessayez.";
  } else {
    $idUser = (int)$_POST['id_utilisateur'];
    $action = $_POST['action'] ?? '';

    if ($action === 'toggle_status') {
      $nouvelEtat = (int)$_POST['etat'];

      // On empêche l'admin de se désactiver lui-même
      if ($idUser === $_SESSION['id_utilisateur']) {
        $messages['erreurs'][] = "Vous ne pouvez pas désactiver votre propre compte.";
      } else {
        Utilisateur::updateStatus($idUser, $nouvelEtat);
        $messages['succes'][] = "Statut mis à jour avec succès.";
      }
    }
  }
}

$personnel = Utilisateur::getAllPersonnel();
$current_page = 'employe';
$page_title = 'Gestion du personnel';
require_once __DIR__ . '/../views/partials/header.php';
?>

<div class="container my-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Gestion du personnel</h1>
    <a href="/admin-employe-form.php" class="btn btn-vg-primary">+ Ajouter un collaborateur</a>
  </div>

  <?php foreach ($messages['succes'] as $m): ?><div class="alert alert-success"><?= $m ?></div><?php endforeach; ?>
  <?php foreach ($messages['erreurs'] as $m): ?><div class="alert alert-danger"><?= $m ?></div><?php endforeach; ?>

  <div class="table-responsive bg-white p-3 border rounded shadow-sm">
    <table class="table table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th>Collaborateur</th>
          <th>Email</th>
          <th>Régime / Rôle</th>
          <th>Statut</th>
          <th class="text-end">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($personnel as $u): ?>
          <tr>
            <td><strong><?= htmlspecialchars($u['nom']) ?></strong> <?= htmlspecialchars($u['prenom']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><span class="badge bg-light text-dark border"><?= ucfirst($u['role']) ?></span></td>
            <td>
              <span class="badge <?= $u['actif'] ? 'bg-success' : 'bg-danger' ?>">
                <?= $u['actif'] ? 'Compte Actif' : 'Compte Suspendu' ?>
              </span>
            </td>
            <td class="text-end">
              <?php if ($u['id_utilisateur'] != $_SESSION['id_utilisateur']): ?>
                <form method="post">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generer_token_csrf()) ?>">
                  <input type="hidden" name="id_utilisateur" value="<?= $u['id_utilisateur'] ?>">
                  <input type="hidden" name="action" value="toggle_status">
                  <input type="hidden" name="etat" value="<?= $u['actif'] ? 0 : 1 ?>">
                  <button type="submit" class="btn btn-sm <?= $u['actif'] ? 'btn-outline-danger' : 'btn-outline-success' ?>">
                    <?= $u['actif'] ? 'Suspendre' : 'Réactiver' ?>
                  </button>
                </form>
              <?php endif; ?>
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

<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>