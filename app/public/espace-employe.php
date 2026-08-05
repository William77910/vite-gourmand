<?php
require_once __DIR__ . '/../src/Helpers/auth.php';
exiger_role(['employe', 'administrateur']);

require_once __DIR__ . '/../src/Models/Avis.php';
$nbAvisEnAttente = count(Avis::getEnAttente());

$current_page = '';
$page_title = 'Espace employé';
require_once __DIR__ . '/../views/partials/header.php';
?>

<div class="container my-5">
    <h1 class="text-center mb-4">Espace employé</h1>

    <div class="row g-3 justify-content-center">
        <div class="col-md-4">
            <a href="/employe-commandes.php" class="text-decoration-none">
                <div class="vg-menu-card text-center p-4">
                    <i class="bi bi-basket fs-1" style="color: var(--vg-vert-sauge);"></i>
                    <h3 class="mt-2">Commandes</h3>
                    <p class="vg-text-secondary mb-0">Statuts, annulations</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="/employe-avis.php" class="text-decoration-none">
                <div class="vg-menu-card text-center p-4">
                    <i class="bi bi-star fs-1" style="color: var(--vg-vert-sauge);"></i>
                    <h3 class="mt-2">Avis clients</h3>
                    <p class="vg-text-secondary mb-0">
                        <?= $nbAvisEnAttente > 0 ? $nbAvisEnAttente . ' en attente' : 'Aucun en attente' ?>
                    </p>
                </div>
            </a>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../views/partials/footer.php';
