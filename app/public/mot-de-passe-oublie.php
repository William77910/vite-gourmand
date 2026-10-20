<?php
session_start();
require_once __DIR__ . '/../src/Models/Utilisateur.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $u = Utilisateur::findByEmail($email);

  if ($u) {
    $token = bin2hex(random_bytes(32));
    Utilisateur::saveResetToken($email, $token);

    // --- VÉRIFIE BIEN CETTE URL ---
    // Elle doit pointer vers ton fichier de réinitialisation avec le token
    $lien = "http://localhost:8080/reinitialiser-mot-de-passe.php?token=" . $token;

    $message = "Un mail de récupération a été envoyé (Simulation : <a href='$lien'>Cliquez ici</a>)";
  } else {
    $message = "Un mail de récupération a été envoyé.";
  }
}

require_once __DIR__ . '/../views/partials/header.php';
?>
<section class="container my-5 text-center">
  <h1>Mot de passe oublié</h1>
  <?php if ($message) echo "<div class='alert alert-info'>$message</div>"; ?>
  <form method="post" class="mx-auto" style="max-width: 400px;">
    <input type="email" name="email" class="form-control mb-3" placeholder="Votre email" required>
    <button type="submit" class="btn btn-vg-primary w-100">Recevoir le lien</button>
  </form>
</section>
<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>