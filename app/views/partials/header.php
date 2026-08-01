<?php

/**
 * Header commun à toutes les pages.
 * Variable attendue (optionnelle) : $current_page (string) pour
 * mettre en surbrillance le lien de navigation actif.
 *
 * La vérification de connexion est volontairement simple pour
 * l'instant (session basique) : elle sera reliée au système
 * d'authentification complet à l'étape suivante.
 */
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$current_page = $current_page ?? '';
$est_connecte = isset($_SESSION['id_utilisateur']);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vite & Gourmand<?= isset($page_title) ? ' - ' . htmlspecialchars($page_title) : '' ?></title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Italianno&family=Montserrat:wght@500;600;700&family=Open+Sans:wght@300;400&family=Playfair+Display:ital@1&display=swap" rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/css/style.css">
</head>

<body>

  <nav class="navbar navbar-expand-lg vg-header py-3">
    <div class="container-fluid">
      <a href="/index.php" class="navbar-brand vg-logo d-flex align-items-center gap-2">
        <img src="/images/logo.png" alt="Logo Vite &amp; Gourmand" height="100">
        Vite &amp; Gourmand
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#vgNavCollapse" aria-controls="vgNavCollapse" aria-expanded="false" aria-label="Ouvrir le menu de navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="vgNavCollapse">
        <ul class="navbar-nav ms-auto vg-nav">
          <li class="nav-item"><a href="/index.php" class="nav-link <?= $current_page === 'accueil' ? 'active' : '' ?>">Accueil</a></li>
          <li class="nav-item"><a href="/menus.php" class="nav-link <?= $current_page === 'menus' ? 'active' : '' ?>">Nos menus</a></li>
          <li class="nav-item"><a href="/a-propos.php" class="nav-link <?= $current_page === 'a-propos' ? 'active' : '' ?>">A propos</a></li>
          <li class="nav-item"><a href="/contact.php" class="nav-link <?= $current_page === 'contact' ? 'active' : '' ?>">Contact</a></li>
          <li class="nav-item"><a href="/compte.php" class="nav-link <?= $current_page === 'compte' ? 'active' : '' ?>">Mon compte</a></li>
          <?php if ($est_connecte): ?>
            <li class="nav-item"><a href="/deconnexion.php" class="nav-link">Déconnexion</a></li>
          <?php else: ?>
            <li class="nav-item"><a href="/connexion.php" class="nav-link <?= $current_page === 'connexion' ? 'active' : '' ?>">Connexion</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>