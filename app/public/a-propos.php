<?php
$current_page = 'a-propos';
$page_title = 'Notre Histoire';
require_once __DIR__ . '/../views/partials/header.php';
?>

<div class="container my-5">
  <!-- SECTION 1 : HISTOIRE -->
  <section class="row align-items-center mb-5">
    <div class="col-12 col-lg-9 mb-4 mb-lg-0">
      <h1 class="display-4 font-italianno mb-4 vg-about-title">Une passion née à Bordeaux</h1>
      <div class="vg-lead-text">
        <p>
          L'aventure <strong>Vite & Gourmand</strong> a commencé il y a un quart de siècle.
          Julie et José, animés par une passion commune pour la gastronomie française,
          ont décidé de mettre leur savoir-faire au profit des bordelais.
        </p>
        <p class="mb-0">
          Ce qui n'était au départ qu'une petite cuisine artisanale est devenu une référence
          incontournable pour tous vos événements : des repas de Noël chaleureux aux
          déjeuners de Pâques, en passant par vos célébrations privées.
        </p>
      </div>
    </div>
    <div class="col-12 col-lg-3 text-center">
      <img src="/images/histoire-cuisine.jpg"
        alt="Cuisine artisanale de Julie et José"
        class="img-fluid rounded shadow-lg vg-img-history">
    </div>
  </section>

  <hr class="my-5 opacity-10">

  <!-- SECTION 2 : ÉQUIPE -->
  <section>
    <h2 class="text-center font-italianno vg-about-subtitle">Le cœur de Vite & Gourmand</h2>
    <div class="row g-4 justify-content-center">

      <!-- CARTE JOSÉ -->
      <div class="col-md-5">
        <div class="card h-100 vg-team-card p-4 text-center">
          <img src="/images/chef-jose.jpg" alt="José, Chef Co-fondateur" class="rounded-circle mx-auto mb-3 vg-team-img">
          <div class="card-body d-flex flex-column p-0">
            <h3 class="h4 mb-1">José</h3>
            <p class="vg-role-label mb-3">Chef Co-fondateur</p>
            <p class="text-muted small flex-grow-1">
              Expert en saveurs méditerranéennes, José est le garant de la tradition et de la qualité
              des produits de saison que nous sélectionnons chaque matin au marché bordelais.
            </p>
          </div>
        </div>
      </div>

      <!-- CARTE JULIE -->
      <div class="col-md-5">
        <div class="card h-100 vg-team-card p-4 text-center">
          <img src="/images/chef-julie.jpg" alt="Julie, Chef Co-fondatrice" class="rounded-circle mx-auto mb-3 vg-team-img">
          <div class="card-body d-flex flex-column p-0">
            <h3 class="h4 mb-1">Julie</h3>
            <p class="vg-role-label mb-3">Chef Co-fondatrice</p>
            <p class="text-muted small flex-grow-1">
              Spécialiste de l'organisation et du service client, Julie veille à ce que chaque
              commande soit une expérience fluide, ponctuelle et surtout gourmande.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- SECTION 3 : ENGAGEMENTS (Points forts harmonisés) -->
  <section class="row mt-5 pt-5 text-center g-4 border-top border-opacity-10">
    <div class="col-md-4">
      <div class="vg-engagement-item">
        <i class="bi bi-patch-check"></i>
        <h4>Qualité Traiteur</h4>
        <p>25 ans d'exigence au service de vos plus beaux événements.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="vg-engagement-item">
        <i class="bi bi-calendar-range"></i>
        <h4>Menus Évolutifs</h4>
        <p>Des créations inspirées par le cycle naturel des saisons.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="vg-engagement-item">
        <i class="bi bi-bicycle"></i>
        <h4>Proximité</h4>
        <p>Une livraison soignée et réactive dans toute l'agglomération.</p>
      </div>
    </div>
  </section>
</div> <!-- FIN DU CONTAINER PRINCIPAL -->

<?php
// Le footer est appelé APRÈS la fermeture du container pour pouvoir prendre toute la largeur
require_once __DIR__ . '/../views/partials/footer.php';
?>