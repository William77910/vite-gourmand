<?php
session_start();

require_once __DIR__ . '/../src/Helpers/csrf.php';
require_once __DIR__ . '/../src/Helpers/mot_de_passe.php';
require_once __DIR__ . '/../src/Models/Utilisateur.php';

$erreurs = [];
$valeurs = [
  'nom' => '',
  'prenom' => '',
  'email' => '',
  'telephone' => '',
  'adresse_postale' => '',
  'code_postal' => '',
  'ville' => '',
];

// Traitement du formulaire d'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!verifier_token_csrf($_POST['csrf_token'] ?? null)) {
    $erreurs[] = "Votre session a expiré, veuillez réessayer.";
  } else {
    foreach ($valeurs as $champ => $valeur) {
      $valeurs[$champ] = trim($_POST[$champ] ?? '');
    }
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $confirmation = $_POST['confirmation_mot_de_passe'] ?? '';
    $accepte_cgv = isset($_POST['accepte_cgv']);

    // Validation serveur (indispensable : le JS peut être contourne)
    if ($valeurs['nom'] === '' || $valeurs['prenom'] === '') {
      $erreurs[] = "Le nom et le prénom sont obligatoires.";
    }
    if (!filter_var($valeurs['email'], FILTER_VALIDATE_EMAIL)) {
      $erreurs[] = "L'adresse email n'est pas valide.";
    } elseif (Utilisateur::findByEmail($valeurs['email'])) {
      $erreurs[] = "Un compte existe déjà avec cette adresse email.";
    }
    if (!mot_de_passe_valide($mot_de_passe)) {
      $erreurs[] = "Le mot de passe ne respecte pas tous les critères requis.";
    } elseif ($mot_de_passe !== $confirmation) {
      $erreurs[] = "Les deux mots de passe ne correspondent pas.";
    }
    if (!$accepte_cgv) {
      $erreurs[] = "Vous devez accepter les mentions légales et les CGV.";
    }

    if (empty($erreurs)) {
      $id = Utilisateur::create([
        'nom' => $valeurs['nom'],
        'prenom' => $valeurs['prenom'],
        'email' => $valeurs['email'],
        'mot_de_passe' => $mot_de_passe,
        'telephone' => $valeurs['telephone'],
        'adresse_postale' => $valeurs['adresse_postale'],
        'code_postal' => $valeurs['code_postal'],
        'ville' => $valeurs['ville'],
      ]);

      // NOTE : envoi d'un email de bienvenue a prevoir ici (PHPMailer/SMTP)
      // une fois le serveur mail configure - hors scope de l'environnement local.

      $_SESSION['id_utilisateur'] = $id;
      $_SESSION['role'] = 'utilisateur';
      $_SESSION['prenom'] = $valeurs['prenom'];
      unset($_SESSION['csrf_token']);

      header('Location: /index.php');
      exit;
    }
  }
}

$current_page = 'inscription';
$page_title = 'Inscription';
require_once __DIR__ . '/../views/partials/header.php';
?>

<div class="vg-hero" style="background-image: url('/images/hero-menu.jpg');">
  <div class="vg-hero-overlay">
    <h1>Inscription</h1>
  </div>
</div>

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <h2 class="text-center mb-1">Créer un compte</h2>
      <p class="text-center vg-text-secondary mb-4">Inscrivez-vous pour accéder à nos services gourmands.</p>

      <?php if (!empty($erreurs)): ?>
        <div class="alert alert-danger">
          <ul class="mb-0">
            <?php foreach ($erreurs as $erreur): ?>
              <li><?= htmlspecialchars($erreur) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="post" action="/inscription.php" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generer_token_csrf()) ?>">

        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="nom" class="vg-label">Nom*</label>
            <input type="text" class="form-control vg-input" id="nom" name="nom" required
              value="<?= htmlspecialchars($valeurs['nom']) ?>">
          </div>
          <div class="col-md-6 mb-3">
            <label for="prenom" class="vg-label">Prénom*</label>
            <input type="text" class="form-control vg-input" id="prenom" name="prenom" required
              value="<?= htmlspecialchars($valeurs['prenom']) ?>">
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="email" class="vg-label">Adresse email*</label>
            <input type="email" class="form-control vg-input" id="email" name="email" required
              value="<?= htmlspecialchars($valeurs['email']) ?>">
          </div>
          <div class="col-md-6 mb-3">
            <label for="telephone" class="vg-label">Numéro de téléphone (GSM)</label>
            <input type="tel" class="form-control vg-input" id="telephone" name="telephone"
              value="<?= htmlspecialchars($valeurs['telephone']) ?>">
          </div>
        </div>

        <div class="mb-3">
          <label for="adresse_postale" class="vg-label">Adresse postale</label>
          <input type="text" class="form-control vg-input" id="adresse_postale" name="adresse_postale"
            value="<?= htmlspecialchars($valeurs['adresse_postale']) ?>">
        </div>

        <div class="row">
          <div class="col-md-4 mb-3">
            <label for="code_postal" class="vg-label">Code postal</label>
            <input type="text" class="form-control vg-input" id="code_postal" name="code_postal"
              value="<?= htmlspecialchars($valeurs['code_postal']) ?>">
          </div>
          <div class="col-md-8 mb-3">
            <label for="ville" class="vg-label">Ville</label>
            <input type="text" class="form-control vg-input" id="ville" name="ville"
              value="<?= htmlspecialchars($valeurs['ville']) ?>">
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="mot_de_passe" class="vg-label">Mot de passe*</label>
            <input type="password" class="form-control vg-input" id="mot_de_passe" name="mot_de_passe" required>
          </div>
          <div class="col-md-6 mb-3">
            <label for="confirmation_mot_de_passe" class="vg-label">Confirmation du mot de passe*</label>
            <input type="password" class="form-control vg-input" id="confirmation_mot_de_passe" name="confirmation_mot_de_passe" required>
          </div>
        </div>

        <ul class="list-unstyled small mb-3" id="listeCriteresMdp">
          <li data-critere="longueur"><i class="bi bi-x-circle text-danger"></i> 10 caractères minimum</li>
          <li data-critere="majuscule"><i class="bi bi-x-circle text-danger"></i> 1 majuscule minimum</li>
          <li data-critere="minuscule"><i class="bi bi-x-circle text-danger"></i> 1 minuscule minimum</li>
          <li data-critere="chiffre"><i class="bi bi-x-circle text-danger"></i> 1 chiffre minimum</li>
          <li data-critere="special"><i class="bi bi-x-circle text-danger"></i> 1 caractère spécial minimum</li>
        </ul>

        <div class="form-check mb-4">
          <input class="form-check-input" type="checkbox" id="accepte_cgv" name="accepte_cgv" required>
          <label class="form-check-label" for="accepte_cgv">
            J'accepte les <a href="/mentions-legales.php">mentions légales</a> et les <a href="/cgv.php">CGV</a>
          </label>
        </div>

        <div class="text-center">
          <button type="submit" class="btn btn-vg-primary">Créer mon compte</button>
        </div>

        <p class="text-center mt-3 vg-text-secondary">
          Déjà inscrit ? <a href="/connexion.php">Se connecter</a>
        </p>
      </form>
    </div>
  </div>
</div>

<script src="/js/inscription.js"></script>

<?php
require_once __DIR__ . '/../views/partials/footer.php';
