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
$images = Menu::getImages($id);
if (empty($images)) {
    $images = ['/images/menus/placeholder.jpg'];
}
$plats = Menu::getPlatsAvecAllergenes($id);
$avis = Avis::getByMenu($id);

$labels_theme = [
    'classique' => 'Classique', 'paques' => 'Pâques',
    'noel' => 'Noël', 'evenement' => 'Événement',
];
$labels_type_plat = ['entree' => 'Entrée', 'plat' => 'Plat', 'dessert' => 'Dessert'];
?>

<div class="container my-5">
    <a href="/menus.php" class="vg-text-secondary d-inline-block mb-3">&larr; Retour aux menus</a>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div id="carouselMenu" class="carousel slide">
                <div class="carousel-inner rounded">
                    <?php foreach ($images as $index => $url): ?>
                        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                            <img src="<?= htmlspecialchars($url) ?>" class="d-block w-100 vg-zoomable" alt="<?= htmlspecialchars($menu['titre']) ?>" style="height:400px; object-fit:cover;">
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($images) > 1): ?>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselMenu" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carouselMenu" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                <?php endif; ?>
            </div>
            <p class="text-center vg-text-secondary small mt-2 mb-0">
                <i class="bi bi-zoom-in"></i> Cliquez sur une photo pour l'agrandir
            </p>
        </div>

        <div class="col-lg-6">
            <div class="vg-menu-card-titre" style="font-size:28px;"><?= htmlspecialchars($menu['titre']) ?></div>
            <p class="vg-text-secondary"><?= htmlspecialchars($menu['description']) ?></p>

            <p class="mb-1">
                <strong><?= number_format((float) $menu['prix_personne'], 2, ',', ' ') ?> € / personne</strong>
                &nbsp;•&nbsp; Minimum <?= (int) $menu['nb_personnes_min'] ?> personnes
            </p>
            <p class="vg-text-secondary mb-3">
                Thème : <?= htmlspecialchars($labels_theme[$menu['theme']] ?? $menu['theme']) ?>
                <?php if (!empty($menu['conditions_menu'])): ?>
                    <br><?= htmlspecialchars($menu['conditions_menu']) ?>
                <?php endif; ?>
            </p>

            <?php foreach ($plats as $plat): ?>
                <div class="vg-texte-presentation mb-3 d-flex gap-3 align-items-start">
                    <?php if (!empty($plat['photo'])): ?>
                        <img src="<?= htmlspecialchars($plat['photo']) ?>" alt="<?= htmlspecialchars($plat['nom']) ?>" class="vg-zoomable"
                             style="width:90px; height:90px; object-fit:cover; border-radius:8px; flex-shrink:0;">
                    <?php endif; ?>
                    <div>
                        <p class="vg-label mb-1"><?= $labels_type_plat[$plat['type_plat']] ?? $plat['type_plat'] ?></p>
                        <h3 class="mb-1"><?= htmlspecialchars($plat['nom']) ?></h3>
                        <p class="mb-2"><?= htmlspecialchars($plat['description']) ?></p>
                        <?php if (!empty($plat['allergenes'])): ?>
                            <span class="badge-vg-warning px-2 py-1 rounded">
                                Allergènes : <?= htmlspecialchars($plat['allergenes']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="d-flex align-items-center gap-3 my-3">
                <span class="vg-label mb-0">Nombre de personnes :</span>
                <button type="button" class="btn btn-vg-secondary" id="btnMoins">−</button>
                <span id="nbPersonnes" data-min="<?= (int) $menu['nb_personnes_min'] ?>" data-prix="<?= (float) $menu['prix_personne'] ?>">
                    <?= (int) $menu['nb_personnes_min'] ?>
                </span>
                <button type="button" class="btn btn-vg-secondary" id="btnPlus">+</button>
                <span class="ms-auto">Total : <strong id="prixTotal"><?= number_format((float) $menu['prix_personne'] * (int) $menu['nb_personnes_min'], 2, ',', ' ') ?> €</strong></span>
            </div>

            <?php if ($est_connecte): ?>
                <a href="/commande.php?menu=<?= $id ?>&personnes=<?= (int) $menu['nb_personnes_min'] ?>" class="btn btn-vg-primary w-100" id="lienCommander">
                    Commander maintenant
                </a>
            <?php else: ?>
                <a href="/connexion.php" class="btn btn-vg-primary w-100">
                    Se connecter pour commander
                </a>
            <?php endif; ?>
        </div>
    </div>

    <hr class="my-5">

    <h2 class="text-center mb-4">Avis de nos clients pour ce menu</h2>
    <?php if (empty($avis)): ?>
        <p class="text-center vg-text-secondary">Aucun avis pour ce menu pour le moment.</p>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($avis as $a): ?>
                <div class="col-md-4">
                    <div class="vg-avis-card">
                        <strong><?= htmlspecialchars($a['prenom']) ?> <?= htmlspecialchars(substr($a['nom'], 0, 1)) ?>.</strong>
                        <div class="vg-avis-etoiles"><?= str_repeat('★', (int) $a['note']) . str_repeat('☆', 5 - (int) $a['note']) ?></div>
                        <p class="vg-text-secondary mb-0">"<?= htmlspecialchars($a['commentaire']) ?>"</p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modale d'agrandissement d'image (lightbox) -->
<div class="modal fade" id="modalImageAgrandie" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0">
            <button type="button" class="btn-close btn-close-white ms-auto me-2 mt-2" data-bs-dismiss="modal" aria-label="Fermer"></button>
            <img src="" alt="" id="imageAgrandie" class="w-100 rounded">
        </div>
    </div>
</div>

<script src="/js/lightbox.js"></script>
<script src="/js/menu-detail.js"></script>

<?php
require_once __DIR__ . '/../views/partials/footer.php';
