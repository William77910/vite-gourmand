<?php
session_start();

// 1. SÉCURITÉ : Accès réservé aux utilisateurs connectés
if (!isset($_SESSION['id_utilisateur'])) {
  header('Location: /connexion.php');
  exit;
}

require_once __DIR__ . '/../src/Helpers/csrf.php';
require_once __DIR__ . '/../src/Helpers/geolocalisation.php';
require_once __DIR__ . '/../src/Models/Menu.php';
require_once __DIR__ . '/../src/Models/Utilisateur.php';
require_once __DIR__ . '/../src/Models/Commande.php';

// 2. RÉCUPÉRATION DU MENU
$idMenu = isset($_GET['menu']) ? (int) $_GET['menu'] : (int) ($_POST['id_menu'] ?? 0);
$menu = $idMenu > 0 ? Menu::getById($idMenu) : null;

if (!$menu) {
  header('Location: /menus.php');
  exit;
}

// 3. INITIALISATION DES DONNÉES
$utilisateur = Utilisateur::findById($_SESSION['id_utilisateur']);
$erreurs = [];

// Gestion du nombre de personnes (minimum imposé par le menu)
$nbPersonnes = isset($_GET['personnes']) ? (int) $_GET['personnes'] : (int) $menu['nb_personnes_min'];
if ($nbPersonnes < (int) $menu['nb_personnes_min']) {
  $nbPersonnes = (int) $menu['nb_personnes_min'];
}

// Valeurs par défaut du formulaire (pré-remplissage avec les infos du compte)
$valeurs = [
  'nb_personnes' => $_POST['nb_personnes'] ?? (isset($_GET['personnes']) ? (int)$_GET['personnes'] : (int)$menu['nb_personnes_min']),
  'date_prestation'   => $_POST['date_prestation'] ?? '',
  'heure_prestation'  => $_POST['heure_prestation'] ?? '',
  'adresse_livraison' => $_POST['adresse_livraison'] ?? ($utilisateur['adresse_postale'] ?? ''),
  'code_postal'       => $_POST['code_postal'] ?? ($utilisateur['code_postal'] ?? ''),
  'ville_livraison'   => $_POST['ville_livraison'] ?? ($utilisateur['ville'] ?? ''),
  'commentaires'      => $_POST['commentaires'] ?? '',
  'mode_paiement'     => $_POST['mode_paiement'] ?? '',
];

// 4. TRAITEMENT DE LA COMMANDE (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Vérification CSRF
  if (!verifier_token_csrf($_POST['csrf_token'] ?? null)) {
    $erreurs[] = "Votre session a expiré, veuillez réessayer.";
  } else {
    // Vérification de la prise de connaissance des allergènes (Légal)
    if (!isset($_POST['accepte_conditions'])) {
      $erreurs[] = "Vous devez accepter les CGV et prendre connaissance des allergènes.";
    }

    // Validation des champs obligatoires
    if (empty($valeurs['date_prestation']) || empty($valeurs['heure_prestation'])) {
      $erreurs[] = "La date et l'heure de prestation sont obligatoires.";
    }
    if (empty($valeurs['adresse_livraison']) || empty($valeurs['ville_livraison'])) {
      $erreurs[] = "L'adresse de livraison est obligatoire.";
    }
    if (empty($valeurs['mode_paiement'])) {
      $erreurs[] = "Veuillez choisir un mode de paiement.";
    }

    if (empty($erreurs)) {
      // CALCUL DU PRIX (Côté serveur pour éviter la fraude)
      $reductionAppliquee = $valeurs['nb_personnes'] >= ((int) $menu['nb_personnes_min'] + 5);
      $sousTotal = (float) $menu['prix_personne'] * $valeurs['nb_personnes'] * ($reductionAppliquee ? 0.9 : 1);

      // Calcul des frais de livraison
      $resLivraison = calculer_frais_livraison($valeurs['adresse_livraison'], $valeurs['code_postal'], $valeurs['ville_livraison']);
      $fraisLivraison = $resLivraison['frais'];
      $prixTotal = $sousTotal + $fraisLivraison;

      try {
        // Création de la commande avec Transaction (Gestion du stock incluse)
        $idCommande = Commande::create([
          'id_utilisateur'        => $_SESSION['id_utilisateur'],
          'id_menu'               => $idMenu,
          'nb_personnes'          => $valeurs['nb_personnes'],
          'date_prestation'       => $valeurs['date_prestation'],
          'heure_prestation'      => $valeurs['heure_prestation'],
          'adresse_livraison'     => $valeurs['adresse_livraison'],
          'ville_livraison'       => $valeurs['ville_livraison'],
          'code_postal_livraison' => $valeurs['code_postal'],
          'frais_livraison'       => $fraisLivraison,
          'reduction_appliquee'   => $reductionAppliquee,
          'prix_total'            => $prixTotal,
          'commentaires'          => $valeurs['commentaires'] !== '' ? $valeurs['commentaires'] : null,
          'mode_paiement'         => $valeurs['mode_paiement'],
        ]);

        // SYNCHRONISATION MONGODB (Statistiques)
        require_once __DIR__ . '/../src/Helpers/stats_helper.php';
        $infosStats = [
          'id_commande'  => $idCommande,
          'id_menu'      => $idMenu,
          'prix_total'   => $prixTotal,
          'nb_personnes' => $valeurs['nb_personnes'],
          'date_creation' => date('Y-m-d H:i:s')
        ];
        enregistrer_statistique_commande($infosStats, $menu['titre']);

        header('Location: /commande-confirmee.php?id=' . $idCommande);
        exit;
      } catch (Exception $e) {
        $erreurs[] = $e->getMessage(); // Affiche l'erreur de stock épuisé
      }
    }
  }
}

// 5. CALCULS POUR L'AFFICHAGE INITIAL
$resInitial = calculer_frais_livraison($valeurs['adresse_livraison'], $valeurs['code_postal'], $valeurs['ville_livraison']);
$fraisAffiches = $resInitial['frais'];
$distanceInitiale = $resInitial['distance_km'];
$sourceInitiale = $resInitial['source'];

$reductionActive = $valeurs['nb_personnes'] >= ((int) $menu['nb_personnes_min'] + 5);
$sousTotalAffiche = (float) $menu['prix_personne'] * $valeurs['nb_personnes'] * ($reductionActive ? 0.9 : 1);

// Récupération de l'image pour le récapitulatif
$images = Menu::getGalerieComplete($idMenu);
$imagePrincipale = !empty($images) ? $images[0] : '/images/menus/placeholder.jpg';

$page_title = 'Ma commande';
require_once __DIR__ . '/../views/partials/header.php';
?>

<section class="container my-5">
  <h1 class="text-center mb-1 font-italianno" style="font-size: 4rem;">Votre commande</h1>
  <p class="text-center vg-text-secondary mb-5">Dernière étape avant la dégustation</p>

  <?php if (!empty($erreurs)): ?>
    <div class="alert alert-danger shadow-sm">
      <ul class="mb-0"><?php foreach ($erreurs as $erreur): ?><li><?= htmlspecialchars($erreur) ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>

  <form method="post" action="/commande.php" novalidate>
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generer_token_csrf()) ?>">
    <input type="hidden" name="id_menu" value="<?= $idMenu ?>">

    <!-- GRILLE PRINCIPALE EN 3 COLONNES -->
    <div class="row g-4">

      <!-- 1. COLONNE GAUCHE : RÉCAPITULATIF MENU -->
      <div class="col-lg-4">
        <div class="vg-menu-card">
          <img src="<?= htmlspecialchars($imagePrincipale) ?>" alt="<?= htmlspecialchars($menu['titre']) ?>">
          <div class="vg-menu-card-body">
            <h5 class="vg-menu-card-titre"><?= htmlspecialchars($menu['titre']) ?></h5>
            <p class="small text-muted mb-3"><?= number_format((float)$menu['prix_personne'], 2, ',', ' ') ?> € / personne</p>

            <div class="mb-3 text-start">
              <label for="nb_personnes" class="vg-label">Nombre de convives*</label>
              <input type="number" class="form-control vg-input" id="nb_personnes" name="nb_personnes"
                min="<?= (int) $menu['nb_personnes_min'] ?>" value="<?= (int)$valeurs['nb_personnes'] ?>"
                data-prix="<?= (float) $menu['prix_personne'] ?>" data-min="<?= (int) $menu['nb_personnes_min'] ?>">
            </div>

            <div class="p-3 bg-light rounded shadow-sm text-start">
              <p class="mb-1 d-flex justify-content-between">Sous-total : <strong id="valeurSousTotal"><?= number_format($sousTotalAffiche, 2, ',', ' ') ?> €</strong></p>
              <p class="mb-1 d-flex justify-content-between">
                Livraison : <strong id="valeurFrais"><?= number_format($fraisAffiches, 2, ',', ' ') ?> €</strong>
              </p>
              <div id="valeurDistance" class="text-center">
                <?php if ($distanceInitiale !== null): ?>
                  <small class="text-muted">Distance : <strong><?= $distanceInitiale ?> km</strong> <span class="badge bg-white text-dark border ms-1" style="font-size: 0.6rem;">Source : <?= $sourceInitiale ?></span></small>
                <?php endif; ?>
              </div>
              <hr>
              <p class="mb-1 d-flex justify-content-between text-success d-none" id="ligneRemise">
                Remise groupe (-10%) : <strong id="valeurRemise">0,00 €</strong>
              </p>
              <h4 class="mb-0 d-flex justify-content-between h5">Total : <strong id="ligneTotal" class="text-vg-primary"><?= number_format($sousTotalAffiche + $fraisAffiches, 2, ',', ' ') ?> €</strong></h4>
            </div>
          </div>
        </div>
      </div>

      <!-- 2. COLONNE MILIEU : INFORMATIONS DE LIVRAISON -->
      <div class="col-lg-4">
        <div class="bg-white p-4 border rounded shadow-sm h-100">
          <h2 class="h5 mb-3 border-bottom pb-2">Informations de livraison</h2>
          <div class="mb-3">
            <label class="vg-label">Adresse de livraison*</label>
            <input type="text" class="form-control vg-input" id="adresse_livraison" name="adresse_livraison" required value="<?= htmlspecialchars($valeurs['adresse_livraison']) ?>">
          </div>
          <div class="row">
            <div class="col-md-5 mb-3">
              <label class="vg-label">Code postal</label>
              <input type="text" class="form-control vg-input" id="code_postal" name="code_postal" value="<?= htmlspecialchars($valeurs['code_postal']) ?>">
            </div>
            <div class="col-md-7 mb-3">
              <label class="vg-label">Ville*</label>
              <input type="text" class="form-control vg-input" id="ville_livraison" name="ville_livraison" required value="<?= htmlspecialchars($valeurs['ville_livraison']) ?>">
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="vg-label">Date*</label>
              <input type="date" class="form-control vg-input" name="date_prestation" required value="<?= htmlspecialchars($valeurs['date_prestation']) ?>">
            </div>
            <div class="col-md-6 mb-3">
              <label class="vg-label">Heure*</label>
              <input type="time" class="form-control vg-input" name="heure_prestation" required value="<?= htmlspecialchars($valeurs['heure_prestation']) ?>">
            </div>
          </div>
          <div class="mb-0">
            <label class="vg-label">Commentaires pour le livreur</label>
            <textarea class="form-control vg-input" name="commentaires" rows="4" style="height:auto;"><?= htmlspecialchars($valeurs['commentaires']) ?></textarea>
          </div>
        </div>
      </div>

      <!-- 3. COLONNE DROITE : MODE DE PAIEMENT & VALIDATION -->
      <div class="col-lg-4">
        <div class="bg-white p-4 border rounded shadow-sm h-100 d-flex flex-column">
          <h2 class="h5 mb-3 border-bottom pb-2">Mode de paiement</h2>

          <div class="payment-methods flex-grow-1">
            <!-- Option Carte -->
            <label class="vg-payment-option <?= $valeurs['mode_paiement'] === 'carte' ? 'active' : '' ?>">
              <input type="radio" name="mode_paiement" value="carte" class="d-none" required <?= $valeurs['mode_paiement'] === 'carte' ? 'checked' : '' ?>>
              <i class="bi bi-credit-card"></i>
              <div>
                <span class="d-block fw-bold">Carte bancaire</span>
                <span class="small text-muted" style="font-size: 0.7rem;">Visa, Mastercard, AMEX</span>
              </div>
              <i class="bi bi-check-circle-fill check-icon"></i>
            </label>

            <!-- Option PayPal -->
            <label class="vg-payment-option <?= $valeurs['mode_paiement'] === 'paypal' ? 'active' : '' ?>">
              <input type="radio" name="mode_paiement" value="paypal" class="d-none" <?= $valeurs['mode_paiement'] === 'paypal' ? 'checked' : '' ?>>
              <i class="bi bi-paypal"></i>
              <div>
                <span class="d-block fw-bold">PayPal</span>
                <span class="small text-muted" style="font-size: 0.7rem;">Paiement sécurisé</span>
              </div>
              <i class="bi bi-check-circle-fill check-icon"></i>
            </label>

            <!-- Option Virement -->
            <label class="vg-payment-option <?= $valeurs['mode_paiement'] === 'virement' ? 'active' : '' ?>">
              <input type="radio" name="mode_paiement" value="virement" class="d-none" <?= $valeurs['mode_paiement'] === 'virement' ? 'checked' : '' ?>>
              <i class="bi bi-bank"></i>
              <div>
                <span class="d-block fw-bold">Virement</span>
                <span class="small text-muted" style="font-size: 0.7rem;">Traitement sous 48h</span>
              </div>
              <i class="bi bi-check-circle-fill check-icon"></i>
            </label>
          </div>

          <!-- ZONE LÉGALE & BOUTON -->
          <div class="mt-4">
            <div class="form-check mb-3 small">
              <input class="form-check-input" type="checkbox" name="accepte_conditions" id="accepte_conditions" required>
              <label class="form-check-label text-muted" for="accepte_conditions">
                J'accepte les <a href="/cgv.php" target="_blank">CGV</a> et les infos allergènes.
              </label>
            </div>
            <button type="submit" class="btn btn-vg-primary btn-lg w-100 shadow-sm">Confirmer et Payer</button>
          </div>
        </div>
      </div>

    </div>
  </form>
</section>

<script src="/js/commande.js"></script>

<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>