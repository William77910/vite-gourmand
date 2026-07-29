<?php
$current_page = 'accueil';
$page_title = 'Accueil';
require_once __DIR__ . '/../views/partials/header.php';
?>

<!-- Banniere hero -->
<div class="vg-hero" style="background-image: url('/images/hero-menu.jpg');">
  <div class="vg-hero-overlay">
    <h1>Livraison de<br>Menus gastronomiques</h1>
    <a href="/menus.php" class="btn btn-vg-primary mt-3">Voir les menus</a>
  </div>
</div>

<div class="container my-5">
  <!-- Points forts -->
  <div class="row text-center mb-5">
    <div class="col-md-4">
      <img src="/images/icon-livraison.png" alt="" height="70">
      <h3 class="mt-2">Livraison rapide</h3>
      <p class="vg-text-secondary">Service rapide et fiable</p>
    </div>
    <div class="col-md-4">
      <img src="/images/icon-menus-perso.png" alt="" height="70">
      <h3 class="mt-2">Menus personnalisables</h3>
      <p class="vg-text-secondary">Des menus sur mesure</p>
    </div>
    <div class="col-md-4">
      <img src="/images/icon-produits-frais.png" alt="" height="70">
      <h3 class="mt-2">Produits frais</h3>
      <p class="vg-text-secondary">Ingredients de saison</p>
    </div>
  </div>

  <!-- Presentation avec photos des chefs -->
  <div class="row align-items-center mb-5">
    <div class="col-md-2 text-center mb-3">
      <img src="/images/chef-jose.jpg" alt="Jose, cofondateur" class="vg-photo-ovale">
    </div>
    <div class="col-md-8 text-center vg-texte-presentation mb-3">
      <p>
        Depuis plus de 25 ans, Vite & Gourmand régale Bordeaux et ses environs avec des menus raffinés et évolutifs.
        Fondée par Julie et José, cette entreprise familiale met son savoir-faire au service de tous vos événements,
        qu’il s’agisse d’un simple repas entre amis ou d’une célébration comme Noël ou Pâques.
      </p>
      <p>
        Animés par la passion du goût et le respect des produits, Julie et José imaginent des menus en constante
        évolution, mêlant tradition et créativité.
        Chaque plat est conçu pour offrir une expérience gourmande, conviviale et authentique,
        fidèle à l’esprit de la maison : rapidité, fraîcheur et plaisir.
      </p>
    </div>
    <div class="col-md-2 text-center mb-3">
      <img src="/images/chef-julie.png" alt="Julie, cofondatrice" class="vg-photo-ovale">
    </div>
  </div>

  <div class="text-center mb-5">
    <a href="#avis" class="btn btn-vg-primary">Nos clients vous en parlent</a>
  </div>

  <!-- Avis clients -->
  <div id="avis" class="row g-3 mb-5">
    <div class="col-md-4">
      <div class="vg-avis-card d-flex align-items-start gap-3">
        <img src="/images/avatar-clara.png" alt="Clara R." class="vg-avatar">
        <div>
          <strong>Clara R.</strong>
          <div class="vg-avis-etoiles">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
          <p class="vg-text-secondary mb-0">"Super service et menus parfait !"</p>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="vg-avis-card d-flex align-items-start gap-3">
        <img src="/images/avatar-marc.png" alt="Marc L." class="vg-avatar">
        <div>
          <strong>Marc L.</strong>
          <div class="vg-avis-etoiles">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
          <p class="vg-text-secondary mb-0">"Repas au top ! Super ambiance."</p>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="vg-avis-card d-flex align-items-start gap-3">
        <img src="/images/avatar-sophie.png" alt="Sophie T." class="vg-avatar">
        <div>
          <strong>Sophie T.</strong>
          <div class="vg-avis-etoiles">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
          <p class="vg-text-secondary mb-0">"Livraison rapide et soignee."</p>
        </div>
      </div>
    </div>
  </div>
  <!-- NOTE : avis codes en dur pour l'instant, seront charges depuis la table "avis" (valide = 1) -->
</div>

<?php
require_once __DIR__ . '/../views/partials/footer.php';
