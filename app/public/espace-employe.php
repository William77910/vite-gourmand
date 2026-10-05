<?php
require_once __DIR__ . '/../src/Helpers/auth.php';
// Sécurité : Employés et Admins peuvent entrer ici
exiger_role(['employe', 'administrateur']);

require_once __DIR__ . '/../src/Models/Avis.php';
$nbAvisEnAttente = count(Avis::getEnAttente());
require_once __DIR__ . '/../src/Models/Contact.php';
$nbMessagesNonTraites = count(Contact::getNonTraited());

$current_page = 'employe';
$page_title = 'Espace de gestion';
require_once __DIR__ . '/../views/partials/header.php';
?>

<div class="container my-5">
  <h1 class="text-center mb-5">Tableau de bord de gestion</h1>

  <div class="row g-4 justify-content-center">
    <!-- 1. GESTION DES PLATS (Commun) -->
    <div class="col-md-4 col-lg-3">
      <a href="/employe-plats.php" class="text-decoration-none">
        <div class="vg-menu-card text-center p-4 h-100 shadow-sm border-0">
          <i class="bi bi-egg-fried fs-1" style="color: var(--vg-vert-sauge);"></i>
          <h3 class="mt-2 text-dark h5">Bibliothèque de Plats</h3>
          <p class="vg-badge-text mb-0 small">Créer et modifier les entrées, plats et desserts.</p>
        </div>
      </a>
    </div>

    <!-- 2.GESTION DES ALLERGÈNES -->
    <div class="col-md-4 col-lg-3">
      <a href="/employe-allergenes.php" class="text-decoration-none">
        <div class="vg-menu-card text-center p-4 h-100 shadow-sm border-0 bg-light">
          <i class="bi bi-exclamation-triangle fs-1" style="color: var(--vg-orange);"></i>
          <h3 class="mt-2 text-dark h5">Allergènes</h3>
          <p class="vg-badge-text mb-0 small">Gérer la liste globale des allergènes.</p>
        </div>
      </a>
    </div>

    <!-- 3. GESTION DES MENUS (Commun) -->
    <div class="col-md-4 col-lg-3">
      <a href="/employe-menus.php" class="text-decoration-none">
        <div class="vg-menu-card text-center p-4 h-100 shadow-sm border-0">
          <i class="bi bi-journal-text fs-1" style="color: var(--vg-vert-sauge);"></i>
          <h3 class="mt-2 text-dark h5">Gestion des Menus</h3>
          <p class="vg-badge-text mb-0 small">Assembler les plats pour créer des menus complets.</p>
        </div>
      </a>
    </div>

    <!-- 4. COMMANDES (Commun) -->
    <div class="col-md-4 col-lg-3">
      <a href="/employe-commandes.php" class="text-decoration-none">
        <div class="vg-menu-card text-center p-4 h-100 shadow-sm border-0">
          <i class="bi bi-basket fs-1" style="color: var(--vg-vert-sauge);"></i>
          <h3 class="mt-2 text-dark h5">Suivi Commandes</h3>
          <p class="vg-badge-text mb-0 small">Gérer les statuts de livraison et les annulations.</p>
        </div>
      </a>
    </div>

    <!-- 5. AVIS CLIENTS (Commun) -->
    <div class="col-md-4 col-lg-3">
      <a href="/employe-avis.php" class="text-decoration-none">
        <div class="vg-menu-card text-center p-4 h-100 shadow-sm border-0">
          <i class="bi bi-star fs-1" style="color: var(--vg-vert-sauge);"></i>
          <h3 class="mt-2 text-dark h5">Modération Avis</h3>
          <p class="vg-text-secondary mb-0 small">
            <span class="badge vg-badge-avis <?= $nbAvisEnAttente > 0 ?>">
              <?= $nbAvisEnAttente ?> en attente
            </span>
          </p>
        </div>
      </a>
    </div>

    <!-- 6. STATISTIQUES (SÉCURISÉ : Uniquement Administrateur) -->
    <?php if ($_SESSION['role'] === 'administrateur'): ?>
      <div class="col-md-4 col-lg-3">
        <a href="/admin-stats.php" class="text-decoration-none">
          <div class="vg-menu-card text-center p-4 h-100 shadow-sm border-0 bg-light">
            <i class="bi bi-bar-chart-line-fill fs-1" style="color: var(--vg-vert-fonce);"></i>
            <h3 class="mt-2 text-dark h5">Statistiques Ventes</h3>
            <p class="vg-badge-text mb-0 small">Analyse NoSQL des performances par menu.</p>
            <div class="badge bg-dark mt-2">Admin uniquement</div>
          </div>
        </a>
      </div>
    <?php endif; ?>

    <!-- 7. GESTION DES HORAIRES -->
    <div class="col-md-4 col-lg-3">
      <a href="/employe-horaires.php" class="text-decoration-none">
        <div class="vg-menu-card text-center p-4 h-100 shadow-sm border-0">
          <i class="bi bi-clock-history fs-1" style="color: var(--vg-vert-sauge);"></i>
          <h3 class="mt-2 text-dark h5">Horaires</h3>
          <p class="vg-badge-text mb-0 small">Modifier les heures d'ouverture du restaurant.</p>
        </div>
      </a>
    </div>

    <!-- 8. GESTION DES CONTACTS -->
    <div class="col-md-4 col-lg-3">
      <a href="/employe-contact.php" class="text-decoration-none">
        <div class="vg-menu-card text-center p-4 h-100 shadow-sm border-0">
          <i class="bi bi-envelope-paper fs-1" style="color: var(--vg-vert-sauge);"></i>
          <h3 class="mt-2 text-dark h5">Messages</h3>
          <p class="vg-badge-text mb-0 small">Consulter et traiter les demandes clients.</p>
          <!-- Affichage du nombre de messages non traités -->
          <span class="badge bg-danger"><?= $nbMessagesNonTraites ?> non traité(s)</span>
        </div>
      </a>
    </div>
    <!-- GESTION DES EMPLOYÉS (SÉCURISÉ : Uniquement Administrateur) -->
    <?php if ($_SESSION['role'] === 'administrateur'): ?>
      <div class="col-md-4 col-lg-3">
        <a href="/admin-employes.php" class="text-decoration-none">
          <div class="vg-menu-card text-center p-4 h-100 shadow-sm border-0 bg-light">
            <i class="bi bi-people-fill fs-1" style="color: #2c3e50;"></i>
            <h3 class="mt-2 text-dark h5">Gestion Personnel</h3>
            <p class="vg-badge-text mb-0 small">Activer ou suspendre les accès collaborateurs.</p>
          </div>
        </a>
      </div>
    <?php endif; ?>

  </div>

  <!-- Lien de retour spécifique pour les employés/admins -->
  <div class="text-center mt-5">
    <a href="/index.php" class="btn btn-link vg-link-accessible text-decoration-none">
      <i class="bi bi-house"></i> Retour au site public
    </a>
  </div>
</div>

<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>