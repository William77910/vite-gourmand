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
    <link href="https://fonts.googleapis.com/css2?family=Italianno&family=Montserrat:wght@500;600;700&family=Open+Sans:wght@300;400&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>

<body>

    <header class="vg-header py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="/index.php" class="vg-logo d-flex align-items-center gap-2">
                <img src="/images/logo.png" alt="Logo Vite et Gourmand" height="80">
                Vite et Gourmand
            </a>

            <nav class="vg-nav d-none d-md-flex">
                <a href="/index.php" class="<?= $current_page === 'accueil' ? 'active' : '' ?>">Accueil</a>
                <a href="/menus.php" class="<?= $current_page === 'menus' ? 'active' : '' ?>">Nos menus</a>
                <a href="/a-propos.php" class="<?= $current_page === 'a-propos' ? 'active' : '' ?>">A propos</a>
                <a href="/contact.php" class="<?= $current_page === 'contact' ? 'active' : '' ?>">Contact</a>
                <a href="/compte.php" class="<?= $current_page === 'compte' ? 'active' : '' ?>">Mon compte</a>
                <?php if ($est_connecte): ?>
                    <a href="/deconnexion.php">Déconnexion</a>
                <?php else: ?>
                    <a href="/connexion.php" class="<?= $current_page === 'connexion' ? 'active' : '' ?>">Connexion</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>