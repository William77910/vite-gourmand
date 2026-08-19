<?php
session_start();

if (!isset($_SESSION['id_utilisateur'])) {
  header('Location: /connexion.php');
  exit;
}

require_once __DIR__ . '/../src/Helpers/csrf.php';
require_once __DIR__ . '/../src/Helpers/geolocalisation.php';
require_once __DIR__ . '/../src/Models/Menu.php';
require_once __DIR__ . '/../src/Models/Utilisateur.php';
require_once __DIR__ . '/../src/Models/Commande.php';

$idMenu = isset($_GET['menu']) ? (int) $_GET['menu'] : (int) ($_POST['id_menu'] ?? 0);
$menu = $idMenu > 0 ? Menu::getById($idMenu) : null;

// Redirection si le menu n'existe pas
if (!$menu) {
  header('Location: /menus.php');
  exit;
}

// Récupération des informations de l'utilisateur connecté
$utilisateur = Utilisateur::findById($_SESSION['id_utilisateur']);
$erreurs = [];

// Détermination du nombre de personnes à partir de l'URL ou du minimum requis
$nbPersonnes = isset($_GET['personnes']) ? (int) $_GET['personnes'] : (int) $menu['nb_personnes_min'];
if ($nbPersonnes < (int) $menu['nb_personnes_min']) {
  $nbPersonnes = (int) $menu['nb_personnes_min'];
}

$valeurs = [
  'nb_personnes'      => $nbPersonnes,
  'date_prestation'   => '',
  'heure_prestation'  => '',
  'adresse_livraison' => $utilisateur['adresse_postale'] ?? '',
  'ville_livraison'   => $utilisateur['ville'] ?? '',
  'code_postal'       => $utilisateur['code_postal'] ?? '',
  'commentaires'      => '',
  'mode_paiement'     => '',
];

// Traitement du formulaire de commande
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!verifier_token_csrf($_POST['csrf_token'] ?? null)) {
    $erreurs[] = "Votre session a expiré, veuillez réessayer.";
  } else {
    foreach (['date_prestation', 'heure_prestation', 'adresse_livraison', 'ville_livraison', 'commentaires', 'mode_paiement', 'code_postal'] as $champ) {
      $valeurs[$champ] = trim($_POST[$champ] ?? '');
    }

    $valeurs['nb_personnes'] = max((int) ($_POST['nb_personnes'] ?? $nbPersonnes), (int) $menu['nb_personnes_min']);

    if ($valeurs['date_prestation'] === '' || $valeurs['heure_prestation'] === '') {
      $erreurs[] = "La date et l'heure de prestation sont obligatoires.";
    }
    if ($valeurs['adresse_livraison'] === '' || $valeurs['ville_livraison'] === '') {
      $erreurs[] = "L'adresse de livraison est obligatoire.";
    }
    if (!in_array($valeurs['mode_paiement'], ['carte', 'paypal', 'virement'], true)) {
      $erreurs[] = "Veuillez choisir un mode de paiement.";
    }

    // Vérification de la case à cocher
    if (!isset($_POST['accepte_conditions'])) {
      $erreurs[] = "Vous devez accepter les CGV et prendre connaissance des allergènes pour commander.";
    }
  }

  if (empty($erreurs)) {
    $reductionAppliquee = $valeurs['nb_personnes'] >= ((int) $menu['nb_personnes_min'] + 5);
    $sousTotal = (float) $menu['prix_personne'] * $valeurs['nb_personnes'] * ($reductionAppliquee ? 0.9 : 1);

    $resultatLivraison = calculer_frais_livraison($valeurs['adresse_livraison'], $valeurs['code_postal'], $valeurs['ville_livraison']);
    $fraisLivraison = $resultatLivraison['frais'];
    $prixTotal = $sousTotal + $fraisLivraison;

    try {
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

      // --- SYNCHRONISATION MONGODB ---
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
      $erreurs[] = $e->getMessage(); // Affiche "Désolé, ce menu n'est plus disponible..."
    }
  }
}

$current_page = 'menus';
$page_title = 'Votre commande';
require_once __DIR__ . '/../views/partials/header.php';

$images = Menu::getGalerieComplete($idMenu);
$imagePrincipale = !empty($images) ? $images[0] : '/images/menus/placeholder.jpg';

$resultatGeo = calculer_frais_livraison($valeurs['adresse_livraison'], $valeurs['code_postal'], $valeurs['ville_livraison']);
$reductionActive = $valeurs['nb_personnes'] >= ((int) $menu['nb_personnes_min'] + 5);
$sousTotalAffiche = (float) $menu['prix_personne'] * $valeurs['nb_personnes'] * ($reductionActive ? 0.9 : 1);
?>

<div class="container my-5">
  <h1 class="text-center mb-1">Votre commande</h1>
  <p class="text-center vg-text-secondary mb-4">Confirmez vos informations avant validation</p>

  <?php if (!empty($erreurs)): ?>
    <div class="alert alert-danger">
      <ul class="mb-0"><?php foreach ($erreurs as $erreur): ?><li><?= htmlspecialchars($erreur) ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>

  <form method="post" action="/commande.php" novalidate>
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generer_token_csrf()) ?>">
    <input type="hidden" name="id_menu" value="<?= $idMenu ?>">

    <div class="row justify-content-center g-4">
      <div class="col-lg-4">
        <div class="vg-menu-card">
          <img src="<?= htmlspecialchars($imagePrincipale) ?>" alt="<?= htmlspecialchars($menu['titre']) ?>">
          <div class="vg-menu-card-body">
            <div class="vg-menu-card-titre"><?= htmlspecialchars($menu['titre']) ?></div>
            <p class="mb-1"><?= number_format((float) $menu['prix_personne'], 2, ',', ' ') ?> € / personne</p>

            <label for="nb_personnes" class="vg-label">Quantité (personnes)</label>
            <input type="number" class="form-control vg-input mb-2" id="nb_personnes" name="nb_personnes"
              min="<?= (int) $menu['nb_personnes_min'] ?>" value="<?= $valeurs['nb_personnes'] ?>"
              data-prix="<?= (float) $menu['prix_personne'] ?>" data-min="<?= (int) $menu['nb_personnes_min'] ?>">

            <p class="mb-1">Sous-total : <strong id="valeurSousTotal"><?= number_format($sousTotalAffiche, 2, ',', ' ') ?> €</strong></p>
            <p class="mb-1">Frais livraison : <strong id="valeurFrais"><?= number_format($resultatGeo['frais'], 2, ',', ' ') ?> €</strong></p>
            <p class="mb-0">Total = <strong id="ligneTotal"><?= number_format($sousTotalAffiche + $resultatGeo['frais'], 2, ',', ' ') ?> €</strong></p>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <h2 class="mb-3">Informations de livraison</h2>
        <div class="mb-3">
          <label for="adresse_livraison" class="vg-label">Adresse*</label>
          <input type="text" class="form-control vg-input" id="adresse_livraison" name="adresse_livraison" required value="<?= htmlspecialchars($valeurs['adresse_livraison']) ?>">
        </div>
        <div class="row">
          <div class="col-md-4 mb-3">
            <label for="code_postal" class="vg-label">Code postal</label>
            <input type="text" class="form-control vg-input" id="code_postal" name="code_postal" value="<?= htmlspecialchars($valeurs['code_postal']) ?>">
          </div>
          <div class="col-md-8 mb-3">
            <label for="ville_livraison" class="vg-label">Ville*</label>
            <input type="text" class="form-control vg-input" id="ville_livraison" name="ville_livraison" required value="<?= htmlspecialchars($valeurs['ville_livraison']) ?>">
          </div>
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="date_prestation" class="vg-label">Date*</label>
            <input type="date" class="form-control vg-input" id="date_prestation" name="date_prestation" required value="<?= htmlspecialchars($valeurs['date_prestation']) ?>">
          </div>
          <div class="col-md-6 mb-3">
            <label for="heure_prestation" class="vg-label">Heure*</label>
            <input type="time" class="form-control vg-input" id="heure_prestation" name="heure_prestation" required value="<?= htmlspecialchars($valeurs['heure_prestation']) ?>">
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <h2 class="mb-3">Mode de paiement</h2>
        <div class="d-flex flex-column gap-2">
          <label class="btn btn-vg-filtre text-start">
            <input type="radio" name="mode_paiement" value="carte" required> <i class="bi bi-credit-card"></i> Carte bancaire
          </label>
          <label class="btn btn-vg-filtre text-start">
            <input type="radio" name="mode_paiement" value="paypal"> <i class="bi bi-paypal"></i> PayPal
          </label>
          <label class="btn btn-vg-filtre text-start">
            <input type="radio" name="mode_paiement" value="virement"> <i class="bi bi-bank"></i> Virement
          </label>
        </div>
        <div class="mt-4">
          <label for="commentaires" class="vg-label">Commentaires</label>
          <textarea class="form-control vg-input" id="commentaires" name="commentaires" rows="3"><?= htmlspecialchars($valeurs['commentaires']) ?></textarea>
        </div>
      </div>
    </div>

    <!-- Case à cocher pour accepter les conditions générales de vente et les informations sur les allergènes -->
    <div class="form-check mb-3 mt-4 p-3 border rounded bg-light">
      <input class="form-check-input" type="checkbox" name="accepte_conditions" id="accepte_conditions" required>
      <label class="form-check-label small" for="accepte_conditions">
        En cochant cette case, je reconnais avoir pris connaissance des
        <a href="/cgv.php" target="_blank">Conditions Générales de Vente</a> et des
        <strong>informations relatives aux allergènes</strong> mentionnées dans le détail du menu.
      </label>
    </div>

    <div class="text-center mt-5">
      <button type="submit" class="btn btn-vg-primary btn-lg">Confirmer et Payer</button>
    </div>
  </form>
</div>

<script src="/js/commande.js"></script>

<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>