<?php
$current_page = 'a-propos';
$page_title = 'Notre Histoire';
require_once __DIR__ . '/../views/partials/header.php';
?>

<!-- Section Hero Simple -->
<div class="vg-hero-small bg-light py-5 border-bottom">
  <div class="container text-center">
    <h1 class="display-4 font-italianno text-vg-primary">L'Art de Recevoir par Julie & José</h1>
    <p class="lead text-muted">Depuis 25 ans au service de vos papilles à Bordeaux.</p>
  </div>
</div>

<div class="container my-5">
  <!-- Section Histoire -->
  <div class="row align-items-center mb-5">
    <div class="col-12 col-lg-9 mb-4 mb-lg-0"> <!-- Le texte prend plus de place (9/12) -->
      <h2 class="mb-4">Une passion née à Bordeaux</h2>
      <div class="vg-texte-presentation">
        <p>
          L'aventure <strong>Vite & Gourmand</strong> a commencé il y a un quart de siècle.
          Julie et José, animés par une passion commune pour la gastronomie française,
          ont décidé de mettre leur savoir-faire au profit des bordelais.
        </p>
        <p>
          Ce qui n'était au départ qu'une petite cuisine artisanale est devenu une référence
          incontournable pour tous vos événements.
        </p>
      </div>
    </div>

    <div class="col-12 col-lg-3">
      <!-- Ajout de la classe vg-img-history pour le contrôle précis -->
      <img src="/images/histoire-cuisine.jpg"
        alt="Cuisine artisanale de Julie et José"
        class="img-fluid rounded shadow-lg d-block mx-auto vg-img-history">
    </div>
  </div>

  <!-- Section Équipe (Julie & José) -->
  <h2 class="text-center mb-5">Le cœur de Vite & Gourmand</h2>
  <div class="row g-4 justify-content-center">
    <div class="col-md-5">
      <div class="card border-0 shadow-sm text-center p-4">
        <img src="/images/chef-jose.jpg" alt="José - Chef Co-fondateur" class="rounded-circle mx-auto mb-3" style="width: 150px; height: 150px; object-fit: cover;">
        <div class="card-body">
          <h3 class="h4">José</h3>
          <p class="text-vg-primary fw-bold">Chef Co-fondateur</p>
          <p class="text-muted small">
            Expert en saveurs méditerranéennes, José est le garant de la tradition et de la qualité
            des produits de saison que nous sélectionnons.
          </p>
        </div>
      </div>
    </div>
    <div class="col-md-5">
      <div class="card border-0 shadow-sm text-center p-4">
        <img src="/images/chef-julie.jpg" alt="Julie - Chef Co-fondatrice" class="rounded-circle mx-auto mb-3" style="width: 150px; height: 150px; object-fit: cover;">
        <div class="card-body">
          <h3 class="h4">Julie</h3>
          <p class="text-vg-primary fw-bold">Chef Co-fondatrice</p>
          <p class="text-muted small">
            Spécialiste de l'organisation et du service client, Julie veille à ce que chaque
            commande soit une expérience fluide et gourmande.
          </p>
        </div>
      </div>
    </div>
  </div>

  <!-- Section Valeurs -->
  <div class="mt-5 p-5 bg-vg-light rounded-4 text-center">
    <h2 class="mb-4">Nos Engagements</h2>
    <div class="row g-4">
      <div class="col-md-4">
        <i class="bi bi-award fs-1 text-vg-primary"></i>
        <h4 class="mt-3">Qualité Traiteur</h4>
        <p class="small text-muted">25 ans d'expérience au service de l'excellence gastronomique.</p>
      </div>
      <div class="col-md-4">
        <i class="bi bi-calendar-event fs-1 text-vg-primary"></i>
        <h4 class="mt-3">Menus Évolutifs</h4>
        <p class="small text-muted">Des cartes qui changent au fil des saisons et de vos envies.</p>
      </div>
      <div class="col-md-4">
        <i class="bi bi-geo-alt fs-1 text-vg-primary"></i>
        <h4 class="mt-3">Proximité</h4>
        <p class="small text-muted">Livraison soignée et rapide à Bordeaux et ses environs.</p>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>