<?php
require_once __DIR__ . '/../src/Helpers/auth.php';
exiger_role(['administrateur']); // Sécurité : Admin uniquement

require_once __DIR__ . '/../src/Helpers/csrf.php';
require_once __DIR__ . '/../src/Models/Utilisateur.php';

$erreurs = [];
$succes = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!verifier_token_csrf($_POST['csrf_token'] ?? null)) {
    $erreurs[] = "Session expirée.";
  } else {
    $data = [
      'nom'     => trim($_POST['nom'] ?? ''),
      'prenom'  => trim($_POST['prenom'] ?? ''),
      'email'   => trim($_POST['email'] ?? ''),
      'mot_de_passe' => $_POST['mot_de_passe'] ?? '',
      'role'    => 'employe' // On force le rôle employé par sécurité
    ];

    // Validation minimale
    if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
      $erreurs[] = "L'adresse email est invalide.";
    } elseif (Utilisateur::findByEmail($data['email'])) {
      $erreurs[] = "Cet email est déjà utilisé par un autre compte.";
    }

    if (empty($erreurs)) {
      Utilisateur::create($data);
      $succes = true;
      // NOTE : C'est ici que l'on enverrait le mail à l'employé (Page 9 du PDF)
    }
  }
}

$current_page = 'employe';
$page_title = 'Ajouter un collaborateur';
require_once __DIR__ . '/../views/partials/header.php';
?>

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-lg-6">
      <h1 class="text-center mb-4">Nouveau Collaborateur</h1>

      <?php if ($succes): ?>
        <div class="alert alert-success text-center">
          <p>Le compte employé a été créé avec succès.</p>
          <a href="/admin-employes.php" class="btn btn-sm btn-success">Retour à la liste</a>
        </div>
      <?php else: ?>

        <?php if (!empty($erreurs)): ?>
          <div class="alert alert-danger">
            <ul class="mb-0"><?php foreach ($erreurs as $e): ?><li><?= $e ?></li><?php endforeach; ?></ul>
          </div>
        <?php endif; ?>

        <form method="post" class="vg-texte-presentation p-4 border rounded shadow-sm bg-white">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generer_token_csrf()) ?>">

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="vg-label">Nom*</label>
              <input type="text" name="nom" class="form-control vg-input" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="vg-label">Prénom*</label>
              <input type="text" name="prenom" class="form-control vg-input" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="vg-label">Adresse email (Username)*</label>
            <input type="email" name="email" class="form-control vg-input" required>
          </div>

          <div class="mb-3">
            <label class="vg-label">Mot de passe provisoire*</label>
            <div class="input-group">
              <input type="password" name="mot_de_passe" class="form-control vg-input input-password" required>
              <button class="btn btn-outline-secondary toggle-password" type="button"><i class="bi bi-eye"></i></button>
            </div>
            <small class="text-muted italic">L'employé devra utiliser ce mot de passe pour sa première connexion.</small>
          </div>

          <div class="mb-4">
            <label class="vg-label">Rôle attribué</label>
            <input type="text" class="form-control vg-input bg-light" value="Employé" readonly>
          </div>

          <button type="submit" class="btn btn-vg-primary w-100">Créer le compte employé</button>
          <div class="text-center mt-3">
            <a href="/admin-employes.php" class="text-secondary small">Annuler et retourner à la liste</a>
          </div>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>