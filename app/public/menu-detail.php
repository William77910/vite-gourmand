<?php
$current_page = 'menus';
require_once __DIR__ . '/../views/partials/header.php';
require_once __DIR__ . '/../src/Models/Menu.php';
require_once __DIR__ . '/../src/Models/Avis.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$menu = $id > 0 ? Menu::getById($id) : null;

if (!$menu) {
  echo '<div class="container my-5 text-center">';
  echo '<h1>Menu introuvable</h1>';
  echo '<p class="vg-text-secondary">Ce menu n\'existe pas ou a été retiré.</p>';
  echo '<a href="/menus.php" class="btn btn-vg-primary">Retour aux menus</a>';
  echo '</div>';
  require_once __DIR__ . '/../views/partials/footer.php';
  exit;
}

$page_title = htmlspecialchars($menu['titre']);
$images = Menu::getGalerieComplete($id);
if (empty($images)) {
  $images = ['/images/menus/placeholder.jpg'];
}
$plats = Menu::getPlatsAvecAllergenes($id);
$avis = Avis::getByMenu($id);

$labels_theme = ['classique' => 'Classique', 'paques' => 'Pâques', 'noel' => 'Noël', 'evenement' => 'Événement'];
$labels_type_plat = ['entree' => 'Entrée', 'plat' => 'Plat', 'dessert' => 'Dessert'];
?>

<div class="container my-5">
  <a href="/menus.php" class="vg-text-secondary d-inline-block mb-3">&larr; Retour aux menus</a>

  <div class="row">
    <!-- Galerie Photo -->
    <div class="col-lg-6 mb-4">
      <div id="carouselMenu" class="carousel slide">
        <div class="carousel-inner rounded shadow-sm">
          <?php foreach ($images as $index => $url): ?>
            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
              <img src="<?= htmlspecialchars($url) ?>" class="d-block w-100 vg-zoomable" alt="<?= htmlspecialchars($menu['titre']) ?>" style="height:400px; object-fit:cover;">
            </div>
          <?php endforeach; ?>
        </div>
        <?php if (count($images) > 1): ?>
          <button class="carousel-control-prev" type="button" data-bs-target="#carouselMenu" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
          <button class="carousel-control-next" type="button" data-bs-target="#carouselMenu" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
        <?php endif; ?>
      </div>
      <p class="text-center vg-text-secondary small mt-2"><i class="bi bi-zoom-in"></i> Cliquez pour agrandir</p>
    </div>

    <!-- Infos & Commande -->
    <div class="col-lg-6">
      <h1 class="vg-menu-card-titre vg-title-md mb-2"><?= htmlspecialchars($menu['titre']) ?></h1>
      <p class="vg-text-secondary mb-4"><?= htmlspecialchars($menu['description']) ?></p>

      <p class="mb-1"><strong><?= number_format((float)$menu['prix_personne'], 2, ',', ' ') ?> € / personne</strong> &bull; Min. <?= (int)$menu['nb_personnes_min'] ?> pers.</p>
      <p class="vg-text-secondary mb-2 small">
        <strong>Thème :</strong> <?= htmlspecialchars($labels_theme[$menu['theme']] ?? $menu['theme']) ?>
      </p>

      <!-- CONDITIONS SPÉCIFIQUES -->
      <?php if (!empty($menu['conditions_menu'])): ?>
        <div class="alert alert-info py-2 px-3 mb-4 shadow-none border-0 bg-light-custom" style="border-left: 4px solid var(--vg-vert-sauge) !important;">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-info-circle-fill text-vg-primary"></i>
            <span class="small text-dark">
              <?= nl2br(htmlspecialchars($menu['conditions_menu'])) ?>
            </span>
          </div>
        </div>
      <?php endif; ?>

      <?php foreach ($plats as $plat): ?>
        <div class="vg-texte-presentation mb-3 d-flex gap-3 align-items-start">
          <?php if (!empty($plat['photo'])): ?>
            <img src="<?= htmlspecialchars($plat['photo']) ?>" alt="<?= htmlspecialchars($plat['nom']) ?>" class="vg-zoomable rounded" style="width:80px; height:80px; object-fit:cover;">
          <?php endif; ?>
          <div>
            <p class="vg-label mb-1"><?= $labels_type_plat[$plat['type_plat']] ?? $plat['type_plat'] ?></p>
            <h3 class="h6 fw-bold mb-1"><?= htmlspecialchars($plat['nom']) ?></h3>
            <p class="small text-muted mb-1"><?= htmlspecialchars($plat['description']) ?></p>
            <?php if (!empty($plat['allergenes'])): ?>
              <span class="badge bg-light text-dark border" style="font-size: 0.65rem;">Allergènes : <?= htmlspecialchars($plat['allergenes']) ?></span>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>

      <!-- Sélecteur Quantité -->
      <div class="vg-texte-presentation p-4 border rounded shadow-sm bg-white mt-4">
        <div class="d-flex align-items-center gap-3 mb-3">
          <span class="vg-label mb-0">Nombre de personnes :</span>
          <button type="button" class="btn btn-vg-secondary btn-sm" id="btnMoins">−</button>
          <span id="nbPersonnes" class="fw-bold" data-min="<?= (int) $menu['nb_personnes_min'] ?>" data-prix="<?= (float) $menu['prix_personne'] ?>"><?= (int) $menu['nb_personnes_min'] ?></span>
          <button type="button" class="btn btn-vg-secondary btn-sm" id="btnPlus">+</button>
        </div>
        <div id="ligneRemiseMenu" class="text-success d-none mb-2 small fw-bold"><i class="bi bi-gift"></i> Remise groupe (-10%) : <span id="valeurRemiseMenu">0,00 €</span></div>
        <div class="d-flex justify-content-between align-items-center border-top pt-3">
          <span class="h6">Total estimé :</span>
          <strong id="prixTotal" class="h4 mb-0 text-vg-primary"><?= number_format((float)$menu['prix_personne'] * (int)$menu['nb_personnes_min'], 2, ',', ' ') ?> €</strong>
        </div>
      </div>

      <div class="mt-4">
        <?php if ($est_connecte): ?>
          <a href="/commande.php?menu=<?= $id ?>" class="btn btn-vg-primary btn-lg w-100 shadow-sm" id="lienCommander">Commander maintenant</a>
        <?php else: ?>
          <a href="/connexion.php" class="btn btn-vg-primary btn-lg w-100 shadow-sm">Se connecter pour commander</a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- SECTION AVIS (Centrée et pleine largeur) -->
  <hr class="vg-hr-light">

  <div class="row justify-content-center">
    <div class="col-12 text-center">
      <h2 class="vg-title-cursive vg-title-lg mb-5">L'avis de nos gourmets</h2>
    </div>

    <?php if (empty($avis)): ?>
      <div class="col-md-6">
        <div class="vg-empty-state text-center shadow-sm">
          <i class="bi bi-chat-left-dots fs-1 text-muted opacity-25"></i>
          <p class="vg-text-secondary mt-3 mb-0">Aucun avis pour ce menu pour le moment.</p>
        </div>
      </div>
    <?php else: ?>
      <div class="col-12">
        <div class="row g-4 justify-content-center">
          <?php foreach ($avis as $a): ?>
            <div class="col-md-6 col-lg-4">
              <div class="vg-avis-card d-flex flex-column h-100 p-4 shadow-sm border-0 bg-white rounded-3">
                <div class="d-flex align-items-center gap-3 mb-3">
                  <div class="rounded-circle bg-vg-primary text-white vg-avatar-sm">
                    <?= substr(htmlspecialchars($a['prenom']), 0, 1) ?>
                  </div>
                  <div class="text-start">
                    <strong class="d-block text-dark"><?= htmlspecialchars($a['prenom']) ?> <?= substr(htmlspecialchars($a['nom']), 0, 1) ?>.</strong>
                    <small class="text-muted"><?= date('d/m/Y', strtotime($a['date_creation'])) ?></small>
                  </div>
                </div>
                <div class="text-start vg-stars-gold mb-2">
                  <?= str_repeat('★', (int)$a['note']) ?><?= str_repeat('☆', 5 - (int)$a['note']) ?>
                </div>
                <p class="text-start vg-text-secondary mb-0 flex-grow-1 fst-italic">"<?= htmlspecialchars($a['commentaire']) ?>"</p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Modale Lightbox -->
<div class="modal fade" id="modalImageAgrandie" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content bg-transparent border-0 text-end">
      <button type="button" class="btn-close btn-close-white mb-2" data-bs-dismiss="modal"></button>
      <img src="" alt="" id="imageAgrandie" class="w-100 rounded shadow">
    </div>
  </div>
</div>

<script src="/js/lightbox.js"></script>
<script src="/js/menu-detail.js"></script>

<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>