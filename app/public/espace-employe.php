<?php
require_once __DIR__ . '/../src/Helpers/auth.php';
exiger_role(['employe', 'administrateur']);
require_once __DIR__ . '/../src/Models/Avis.php';

$nbAvisEnAttente = count(Avis::getEnAttente());
$current_page = 'employe';
$page_title = 'Espace employé';
require_once __DIR__ . '/../views/partials/header.php';
?>

<div class="container my-5">
  <h1 class="text-center mb-5">Espace de gestion</h1>

  <div class="row g-4 justify-content-center">
    <!-- NOUVEAU BLOC : GESTION DES PLATS -->
    <div class="col-md-3">
      <a href="/employe-plats.php" class="text-decoration-none">
        <div class="vg-menu-card text-center p-4 h-100 shadow-sm">
          <i class="bi bi-egg-fried fs-1" style="color: var(--vg-vert-sauge);"></i>
          <h3 class="mt-2 text-dark">Plats</h3>
          <p class="vg-text-secondary mb-0 small">Gérer la bibliothèque de plats</p>
        </div>
      </a>
    </div>

    <!-- BLOC EXISTANT : GESTION DES MENUS -->
    <div class="col-md-3">
      <a href="/employe-menus.php" class="text-decoration-none">
        <div class="vg-menu-card text-center p-4 h-100 shadow-sm">
          <i class="bi bi-journal-text fs-1" style="color: var(--vg-vert-sauge);"></i>
          <h3 class="mt-2 text-dark">Menus</h3>
          <p class="vg-text-secondary mb-0 small">Assembler et modifier les menus</p>
        </div>
      </a>
    </div>

    <!-- BLOC EXISTANT : COMMANDES -->
    <div class="col-md-3">
      <a href="/employe-commandes.php" class="text-decoration-none">
        <div class="vg-menu-card text-center p-4 h-100 shadow-sm">
          <i class="bi bi-basket fs-1" style="color: var(--vg-vert-sauge);"></i>
          <h3 class="mt-2 text-dark">Commandes</h3>
          <p class="vg-text-secondary mb-0 small">Suivi et statuts</p>
        </div>
      </a>
    </div>

    <!-- BLOC EXISTANT : AVIS -->
    <div class="col-md-3">
      <a href="/employe-avis.php" class="text-decoration-none">
        <div class="vg-menu-card text-center p-4 h-100 shadow-sm">
          <i class="bi bi-star fs-1" style="color: var(--vg-vert-sauge);"></i>
          <h3 class="mt-2 text-dark">Avis</h3>
          <p class="vg-text-secondary mb-0 small"><?= $nbAvisEnAttente ?> en attente</p>
        </div>
      </a>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>