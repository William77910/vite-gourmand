<?php
$current_page = 'menus';
$page_title = 'Nos menus';
require_once __DIR__ . '/../views/partials/header.php';
require_once __DIR__ . '/../src/Models/Menu.php';

$menus = Menu::getAll();
?>

<!-- Banniere hero -->
<div class="vg-hero" style="background-image: url('/images/hero-menu.jpg');">
  <div class="vg-hero-overlay">
    <h1>Nos menus</h1>
    <button type="button" class="btn btn-vg-primary mt-3" data-bs-toggle="modal" data-bs-target="#modalFiltre">
      Filtre
    </button>
  </div>
</div>

<!-- Conteneur des menus -->
<div class="container my-5">
  <h2 class="text-center mb-4 vg-menu-title">Nos menus gastronomiques</h2>
  <!-- Conteneur des cartes de menus -->
  <div class="row g-4" id="conteneur-menus">
    <?php foreach ($menus as $menu): ?>
      <div class="col-md-4">
        <div class="vg-menu-card">
          <img src="<?= htmlspecialchars($menu['image_principale'] ?? '/images/menus/placeholder.jpg') ?>"
            alt="Photo du menu : <?= htmlspecialchars($menu['titre']) ?>">
          <div class="vg-menu-card-body">
            <div class="vg-menu-card-titre"><?= htmlspecialchars($menu['titre']) ?></div>
            <p class="mb-1"><?= number_format((float) $menu['prix_personne'], 2, ',', ' ') ?> € / personne</p>
            <p class="vg-text-secondary flex-grow-1"><?= htmlspecialchars($menu['description']) ?></p>
            <a href="/menu-detail.php?id=<?= (int) $menu['id_menu'] ?>" class="btn btn-vg-primary mt-2">
              Voir le menu
            </a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>

    <?php if (empty($menus)): ?>
      <p class="text-center vg-text-secondary">Aucun menu ne correspond à votre recherche pour le moment.</p>
    <?php endif; ?>
  </div>

  <div class="text-center mt-5">
    <a href="/menus.php" class="btn btn-vg-primary">Ouvrez un menu et passez votre commande</a>
  </div>
</div>

<!-- Modale de filtre -->
<div class="modal fade" id="modalFiltre" tabindex="-1" aria-labelledby="modalFiltreLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title" id="modalFiltreLabel">Filtrer les menus</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <p class="vg-label mb-2">Thème</p>
          <div class="d-flex flex-wrap gap-2" data-groupe="theme">
            <button type="button" class="btn btn-vg-filtre" data-valeur="classique">Classique</button>
            <button type="button" class="btn btn-vg-filtre" data-valeur="paques">Pâques</button>
            <button type="button" class="btn btn-vg-filtre" data-valeur="noel">Noël</button>
            <button type="button" class="btn btn-vg-filtre" data-valeur="evenement">Événement</button>
          </div>
        </div>

        <div class="mb-3">
          <p class="vg-label mb-2">Régime</p>
          <div class="d-flex flex-wrap gap-2" data-groupe="regime">
            <button type="button" class="btn btn-vg-filtre" data-valeur="classique">Classique</button>
            <button type="button" class="btn btn-vg-filtre" data-valeur="vegetarien">Végétarien</button>
            <button type="button" class="btn btn-vg-filtre" data-valeur="vegan">Végan</button>
          </div>
        </div>

        <div class="mb-3">
          <label for="filtrePrixMax" class="vg-label mb-2 d-block">
            Prix par personne — maximum <span id="filtrePrixMaxValeur">50</span> €
          </label>
          <input type="range" class="form-range" id="filtrePrixMax" min="0" max="50" step="5" value="50">
        </div>

        <div class="mb-2">
          <p class="vg-label mb-2">Nombre de personnes minimum</p>
          <div class="d-flex flex-wrap gap-2" data-groupe="personnes">
            <button type="button" class="btn btn-vg-filtre" data-valeur="2">2 pers.</button>
            <button type="button" class="btn btn-vg-filtre" data-valeur="4">4 pers.</button>
            <button type="button" class="btn btn-vg-filtre" data-valeur="6">6 pers.</button>
            <button type="button" class="btn btn-vg-filtre" data-valeur="8">8 pers.</button>
            <button type="button" class="btn btn-vg-filtre" data-valeur="10">10 pers.</button>
          </div>
        </div>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-vg-secondary" id="btnAnnulerFiltre">Annuler</button>
        <button type="button" class="btn btn-vg-primary" id="btnValiderFiltre" data-bs-dismiss="modal">Valider</button>
      </div>
    </div>
  </div>
</div>

<script src="/js/menus.js"></script>

<?php
require_once __DIR__ . '/../views/partials/footer.php';
