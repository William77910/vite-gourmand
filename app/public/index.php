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
      <img src="/images/icon-livraison.png" alt="icon livraison" height="70">
      <h3 class="mt-2">Livraison rapide</h3>
      <p class="vg-text-secondary">Service rapide et fiable</p>
    </div>
    <div class="col-md-4">
      <img src="/images/icon-menus-perso.png" alt="icon menus personnalisables" height="70">
      <h3 class="mt-2">Menus personnalisables</h3>
      <p class="vg-text-secondary">Des menus sur mesure</p>
    </div>
    <div class="col-md-4">
      <img src="/images/icon-produits-frais.png" alt="icon produits frais" height="70">
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
      <img src="/images/chef-julie.jpg" alt="Julie, cofondatrice" class="vg-photo-ovale">
    </div>
  </div>

  <div class="text-center mb-5">
    <a href="#avis" class="btn btn-vg-primary">Nos clients vous en parlent</a>
  </div>

  <!-- Section Avis Clients Dynamiques -->
  <h2 class="text-center mb-4">L'avis de nos gourmets</h2>
  <div id="avis" class="row g-4 mb-5">
    <?php
    require_once __DIR__ . '/../src/Models/Avis.php';
    $avisValides = Avis::getDerniersAvisValides(3);

    if (empty($avisValides)): ?>
      <p class="text-center text-muted fst-italic">Pas encore d'avis pour le moment. Soyez le premier à commander !</p>
    <?php else: ?>
      <?php foreach ($avisValides as $a): ?>
        <div class="col-md-4">
          <div class="vg-avis-card d-flex flex-column h-100 p-4 shadow-sm border-0 bg-white rounded-3">
            <div class="d-flex align-items-center gap-3 mb-3">
              <!-- Avatar générique basé sur l'initiale -->
              <div class="rounded-circle bg-vg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 50px; height: 50px; flex-shrink:0;">
                <?= substr(htmlspecialchars($a['prenom']), 0, 1) ?>
              </div>
              <div>
                <strong class="d-block text-dark"><?= htmlspecialchars($a['prenom']) ?> <?= substr(htmlspecialchars($a['nom']), 0, 1) ?>.</strong>
                <span class="small text-muted">Sur le <?= htmlspecialchars($a['titre_menu']) ?></span>
              </div>
            </div>
            <div class="vg-avis-etoiles vg-stars-gold mb-2">
              <?= str_repeat('★', (int)$a['note']) ?><?= str_repeat('☆', 5 - (int)$a['note']) ?>
            </div>
            <p class="vg-text-secondary mb-0 flex-grow-1">"<?= htmlspecialchars($a['commentaire']) ?>"</p>
            <small class="text-muted mt-3" style="font-size: 0.7rem;">
              Publié le <?= date('d/m/Y', strtotime($a['date_creation'])) ?>
            </small>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

</div>

<?php
require_once __DIR__ . '/../views/partials/footer.php';
