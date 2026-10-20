<?php
session_start();

require_once __DIR__ . '/../src/Helpers/csrf.php';
require_once __DIR__ . '/../src/Models/Utilisateur.php';

$erreurs = [];
$email_saisi = '';

// Gestion des messages d'information (ex: retour de réinitialisation)
$info_message = "";
if (isset($_GET['info'])) {
  if ($_GET['info'] === 'mdp_mis_a_jour') $info_message = "Votre mot de passe a été réinitialisé. Connectez-vous avec vos nouveaux identifiants.";
  if ($_GET['info'] === 'compte_supprime') $info_message = "Votre compte a bien été supprimé et vos données personnelles effacées.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!verifier_token_csrf($_POST['csrf_token'] ?? null)) {
    $erreurs[] = "Votre session a expiré, veuillez réessayer.";
  } else {
    $email_saisi = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    $utilisateur = Utilisateur::findByEmail($email_saisi);

    // Sécurité OWASP : Message générique pour ne pas révéler si le mail existe
    if (!$utilisateur || !password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
      $erreurs[] = "Adresse email ou mot de passe incorrect.";
    }
    // Vérification si le compte est actif (Exigence Page 9 du PDF)
    elseif (!$utilisateur['actif']) {
      $erreurs[] = "Ce compte a été suspendu par l'administrateur.";
    } else {
      // Sécurité : régénération de l'ID de session pour éviter le "Session Fixation"
      session_regenerate_id(true);

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

<section class="container my-5">
  <div class="row justify-content-center">
    <div class="col-lg-5">
      <h1 class="text-center mb-4 font-italianno" style="font-size: 4rem;">Connexion</h1>

      <?php if ($info_message): ?>
        <div class="alert alert-success shadow-sm"><?= htmlspecialchars($info_message) ?></div>
      <?php endif; ?>

      <?php if (!empty($erreurs)): ?>
        <div class="alert alert-danger shadow-sm">
          <ul class="mb-0">
            <?php foreach ($erreurs as $erreur): ?>
              <li><?= htmlspecialchars($erreur) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="post" action="/connexion.php" class="vg-texte-presentation p-4 border rounded shadow-sm bg-white" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generer_token_csrf()) ?>">

        <div class="mb-3">
          <label for="email" class="vg-label">Adresse email*</label>
          <input type="email" class="form-control vg-input" id="email" name="email" required
            value="<?= htmlspecialchars($email_saisi) ?>" placeholder="nom@exemple.com">
        </div>

        <div class="mb-2">
          <label for="mot_de_passe" class="vg-label">Mot de passe*</label>
          <div class="input-group">
            <input type="password" class="form-control vg-input input-password" id="mot_de_passe" name="mot_de_passe" required>
            <button class="btn btn-outline-secondary toggle-password" type="button"><i class="bi bi-eye"></i></button>
          </div>
        </div>

        <div class="text-end mb-4">
          <a href="/mot-de-passe-oublie.php" class="small text-muted">Mot de passe oublié ?</a>
        </div>

        <div class="text-center">
          <button type="submit" class="btn btn-vg-primary btn-lg w-100">Se connecter</button>
        </div>

        <p class="text-center mt-4 vg-text-secondary">
          Nouveau client ? <a href="/inscription.php" class="text-vg-primary fw-bold">Créer un compte</a>
        </p>
      </form>
    </div>
  </div>
</section>

<?php
require_once __DIR__ . '/../views/partials/footer.php';
