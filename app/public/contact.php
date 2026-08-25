<?php
session_start();
require_once __DIR__ . '/../src/Helpers/csrf.php';
require_once __DIR__ . '/../src/Helpers/contact_helper.php';

$erreurs = [];
$succes = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Vérification CSRF (Sécurité OWASP)
  if (!verifier_token_csrf($_POST['csrf_token'] ?? null)) {
    $erreurs[] = "Votre session a expiré, veuillez réessayer.";
  } else {
    $email = trim($_POST['email'] ?? '');
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // Validations
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $erreurs[] = "L'adresse email n'est pas valide.";
    }
    if (strlen($titre) < 5) {
      $erreurs[] = "Le titre est trop court.";
    }
    if (strlen($description) < 10) {
      $erreurs[] = "Le message doit faire au moins 10 caractères.";
    }

    if (empty($erreurs)) {
      if (traiter_message_contact($email, $titre, $description)) {
        $succes = true;
      } else {
        $erreurs[] = "Une erreur est survenue lors de l'enregistrement de votre message.";
      }
    }
  }
}

$current_page = 'contact';
$page_title = 'Contactez-nous';
require_once __DIR__ . '/../views/partials/header.php';
?>

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-lg-6">
      <h1 class="text-center mb-4 vg-contact-title">Contactez Vite & Gourmand</h1>
      <p class="text-center vg-text-secondary mb-5">Une question ? Un événement particulier ? Julie et José vous répondent.</p>

      <?php if ($succes): ?>
        <div class="alert alert-success text-center p-5 shadow-sm">
          <i class="bi bi-check-circle fs-1"></i>
          <h2 class="h4 mt-3">Message envoyé !</h2>
          <p class="mb-0">Merci, nous reviendrons vers vous dans les plus brefs délais.</p>
          <a href="/index.php" class="btn btn-vg-primary mt-4">Retour à l'accueil</a>
        </div>
      <?php else: ?>

        <?php if (!empty($erreurs)): ?>
          <div class="alert alert-danger">
            <ul class="mb-0">
              <?php foreach ($erreurs as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="post" action="/contact.php" class="vg-texte-presentation p-4 border rounded shadow-sm bg-white" novalidate>
          <input type="hidden" name="csrf_token" value="<?= generer_token_csrf() ?>">

          <div class="mb-3">
            <label for="email" class="vg-label">Votre adresse email*</label>
            <input type="email" name="email" id="email" class="form-control vg-input"
              value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required placeholder="exemple@mail.com">
          </div>

          <div class="mb-3">
            <label for="titre" class="vg-label">Sujet de votre message*</label>
            <input type="text" name="titre" id="titre" class="form-control vg-input"
              value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>" required placeholder="Ex: Devis pour un mariage">
          </div>

          <div class="mb-4">
            <label for="description" class="vg-label">Message*</label>
            <textarea name="description" id="description" class="form-control vg-input"
              rows="6" required placeholder="Détaillez votre demande ici..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
          </div>

          <div class="text-center">
            <button type="submit" class="btn btn-vg-primary btn-lg w-100">Envoyer le message</button>
          </div>
        </form>

      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>