<?php
require_once __DIR__ . '/../src/Helpers/auth.php';
exiger_role(['employe', 'administrateur']);
require_once __DIR__ . '/../src/Models/Horaire.php';
require_once __DIR__ . '/../src/Helpers/csrf.php';

$messages = ['succes' => [], 'erreurs' => []];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Vérification du jeton CSRF avant toute opération SQL
  if (!verifier_token_csrf($_POST['csrf_token'] ?? null)) {
    $messages['erreurs'][] = "Erreur de sécurité : session expirée ou requête invalide.";
  } else {
    // Le jeton est valide, on traite les données
    if (isset($_POST['horaires']) && is_array($_POST['horaires'])) {
      foreach ($_POST['horaires'] as $id => $data) {
        Horaire::update((int)$id, [
          'ouverture'   => $data['ouverture'],
          'fermeture'   => $data['fermeture'],
          'ouvert'      => isset($data['ouvert']),
          'commentaire' => $data['commentaire']
        ]);
      }
      $messages['succes'][] = "Les horaires ont été mis à jour avec succès.";
    }
  }
}

$horaires = Horaire::getAll();
$current_page = 'employe';
$page_title = 'Gestion des horaires';
require_once __DIR__ . '/../views/partials/header.php';
?>

<div class="container my-5">
  <h1 class="text-center mb-5">Gestion des horaires d'ouverture</h1>

  <?php foreach ($messages['succes'] as $m): ?><div class="alert alert-success"><?= $m ?></div><?php endforeach; ?>

  <form method="post" class="bg-white p-4 border rounded shadow-sm">
    <!-- Jeton CSRF pour sécuriser le formulaire -->
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generer_token_csrf()) ?>">

    <div class="table-responsive">
      <table class="table align-middle">
        <thead class="table-light">
          <tr>
            <th>Jour</th>
            <th>Service</th>
            <th>Ouvert</th>
            <th>Ouverture</th>
            <th>Fermeture</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($horaires as $h): ?>
            <tr>
              <td class="fw-bold"><?= ucfirst($h['jour_semaine']) ?> (<?= $h['service'] ?>)</td>
              <td>
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" name="horaires[<?= $h['id_horaire'] ?>][ouvert]" <?= $h['ouvert'] ? 'checked' : '' ?>>
                </div>
              </td>
              <td>
                <input type="time" name="horaires[<?= $h['id_horaire'] ?>][ouverture]" class="form-control form-control-sm" value="<?= $h['heure_ouverture'] ?>">
              </td>
              <td>
                <input type="time" name="horaires[<?= $h['id_horaire'] ?>][fermeture]" class="form-control form-control-sm" value="<?= $h['heure_fermeture'] ?>">
              </td>
              <td>
                <input type="text" name="horaires[<?= $h['id_horaire'] ?>][commentaire]" class="form-control form-control-sm" placeholder="Ex: Fermé pour réception..." value="<?= htmlspecialchars($h['commentaire_fermeture'] ?? '') ?>">
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="text-center mt-4">
      <button type="submit" class="btn btn-vg-primary">Enregistrer tous les horaires</button>
      <a href="/espace-employe.php" class="btn btn-vg-secondary">Annuler</a>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>