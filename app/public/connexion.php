<?php
session_start();

require_once __DIR__ . '/../src/Helpers/csrf.php';
require_once __DIR__ . '/../src/Models/Utilisateur.php';

$erreurs = [];
$email_saisi = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!verifier_token_csrf($_POST['csrf_token'] ?? null)) {
    $erreurs[] = "Votre session a expiré, veuillez réessayer.";
  } else {
    $email_saisi = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    $utilisateur = Utilisateur::findByEmail($email_saisi);

    // Message volontairement generique : on ne revele jamais si c'est
    // l'email ou le mot de passe qui est incorrect (bonne pratique de securite)
    if (!$utilisateur || !password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
      $erreurs[] = "Adresse email ou mot de passe incorrect.";
    } elseif (!$utilisateur['actif']) {
      $erreurs[] = "Ce compte a été désactivé. Veuillez contacter l'administrateur.";
    } else {
      session_regenerate_id(true); // limite les risques de fixation de session
      $_SESSION['id_utilisateur'] = $utilisateur['id_utilisateur'];
      $_SESSION['role'] = $utilisateur['role'];
      $_SESSION['prenom'] = $utilisateur['prenom'];

      header('Location: /index.php');
      exit;
    }
  }
}

$current_page = 'connexion';
$page_title = 'Connexion';
require_once __DIR__ . '/../views/partials/header.php';
?>

<div class="vg-hero" style="background-image: url('/images/hero-menu.jpg');">
  <div class="vg-hero-overlay">
    <h1>Connexion</h1>
  </div>
</div>

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-lg-5">
      <?php if (!empty($erreurs)): ?>
        <div class="alert alert-danger">
          <?php foreach ($erreurs as $erreur): ?>
            <div><?= htmlspecialchars($erreur) ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="post" action="/connexion.php" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generer_token_csrf()) ?>">

        <div class="mb-3">
          <label for="email" class="vg-label">Adresse email*</label>
          <input type="email" class="form-control vg-input" id="email" name="email" required
            value="<?= htmlspecialchars($email_saisi) ?>">
        </div>

        <div class="mb-3">
          <label class="vg-label">Mot de passe*</label>
          <div class="input-group">
            <input type="password" class="form-control vg-input input-password" name="mot_de_passe" required>
            <button class="btn btn-outline-secondary toggle-password" type="button">
              <i class="bi bi-eye"></i>
            </button>
          </div>
        </div>

        <div class="text-center">
          <button type="submit" class="btn btn-vg-primary">Se connecter</button>
        </div>

        <p class="text-center mt-3 vg-text-secondary">
          Pas encore inscrit ? <a href="/inscription.php">Créer un compte</a>
        </p>
      </form>
    </div>
  </div>
</div>

<?php
require_once __DIR__ . '/../views/partials/footer.php';
