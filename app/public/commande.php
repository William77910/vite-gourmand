<?php
session_start();

// Redirige vers la page de connexion si l'utilisateur n'est pas connecté
if (!isset($_SESSION['id_utilisateur'])) {
  header('Location: /connexion.php');
  exit;
}

require_once __DIR__ . '/../src/Helpers/csrf.php';
require_once __DIR__ . '/../src/Helpers/geolocalisation.php';
require_once __DIR__ . '/../src/Models/Menu.php';
require_once __DIR__ . '/../src/Models/Utilisateur.php';
require_once __DIR__ . '/../src/Models/Commande.php';

// Récupère le menu à partir de l'ID passé en GET ou POST
$idMenu = isset($_GET['menu']) ? (int) $_GET['menu'] : (int) ($_POST['id_menu'] ?? 0);
$menu = $idMenu > 0 ? Menu::getById($idMenu) : null;

if (!$menu) {
  header('Location: /menus.php');
  exit;
}

// Récupère les informations de l'utilisateur connecté
$utilisateur = Utilisateur::findById($_SESSION['id_utilisateur']);
$erreurs = [];

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

// Si le formulaire est soumis, on traite les données
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!verifier_token_csrf($_POST['csrf_token'] ?? null)) {
    $erreurs[] = "Votre session a expiré, veuillez réessayer.";
  } else {
    foreach (['date_prestation', 'heure_prestation', 'adresse_livraison', 'ville_livraison', 'commentaires', 'mode_paiement'] as $champ) {
      $valeurs[$champ === 'code_postal' ? 'code_postal' : $champ] = trim($_POST[$champ] ?? '');
    }
    $valeurs['code_postal'] = trim($_POST['code_postal'] ?? '');
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

    // Calcul du prix (recalcule cote serveur : ne jamais faire confiance
    // a un total envoye par le formulaire cote client)
    $reductionAppliquee = $valeurs['nb_personnes'] >= ((int) $menu['nb_personnes_min'] + 5);
    $sousTotal = (float) $menu['prix_personne'] * $valeurs['nb_personnes'] * ($reductionAppliquee ? 0.9 : 1);

    // Frais de livraison : 5 euros de base + 0,59 euro/km si hors
    // Bordeaux (geocodage via l'API Adresse du gouvernement francais)
    $resultatLivraison = calculer_frais_livraison(
      $valeurs['adresse_livraison'],
      $valeurs['code_postal'],
      $valeurs['ville_livraison']
    );
    $fraisLivraison = $resultatLivraison['frais'];
    $prixTotal = $sousTotal + $fraisLivraison;

    if (empty($erreurs)) {
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

      // NOTE : email de confirmation a envoyer ici une fois le serveur
      // SMTP configure (PHPMailer), hors scope de l'environnement local.

      header('Location: /commande-confirmee.php?id=' . $idCommande);
      exit;
    }
  }
}

// Affichage de la page de commande
$current_page = 'menus';
$page_title = 'Commande';
require_once __DIR__ . '/../views/partials/header.php';

$reductionActive = $valeurs['nb_personnes'] >= ((int) $menu['nb_personnes_min'] + 5);
$sousTotalAffiche = (float) $menu['prix_personne'] * $valeurs['nb_personnes'] * ($reductionActive ? 0.9 : 1);
$estimationLivraison = calculer_frais_livraison($valeurs['adresse_livraison'], $valeurs['code_postal'], $valeurs['ville_livraison']);
$fraisAffiches = $estimationLivraison['frais'];
?>

<div class="container my-5">
  <h1 class="text-center mb-1">Votre commande</h1>
  <p class="text-center vg-text-secondary mb-4">Confirmez vos informations avant validation</p>

  <?php if (!empty($erreurs)): ?>
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="alert alert-danger">
          <ul class="mb-0">
            <?php foreach ($erreurs as $erreur): ?>
              <li><?= htmlspecialchars($erreur) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <form method="post" action="/commande.php" novalidate>
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generer_token_csrf()) ?>">
    <input type="hidden" name="id_menu" value="<?= $idMenu ?>">

    <div class="row justify-content-center g-4">
      <div class="col-lg-4">
        <div class="vg-menu-card">
          <img src="<?= htmlspecialchars(Menu::getImages($idMenu)[0] ?? '/images/menus/placeholder.jpg') ?>" alt="<?= htmlspecialchars($menu['titre']) ?>">
          <div class="vg-menu-card-body">
            <div class="vg-menu-card-titre"><?= htmlspecialchars($menu['titre']) ?></div>
            <p class="mb-1"><?= number_format((float) $menu['prix_personne'], 2, ',', ' ') ?> € / personne</p>

            <label for="nb_personnes" class="vg-label">Quantité (personnes)</label>
            <input type="number" class="form-control vg-input mb-2" id="nb_personnes" name="nb_personnes"
              min="<?= (int) $menu['nb_personnes_min'] ?>" value="<?= $valeurs['nb_personnes'] ?>"
              data-prix="<?= (float) $menu['prix_personne'] ?>" data-min="<?= (int) $menu['nb_personnes_min'] ?>">

            <p class="mb-1" id="ligneSousTotal">Sous-total : <strong id="valeurSousTotal">echo htmlentities(number_format($sousTotalAffiche, 2, ',', ' ')); €</strong></p>
            <p class="mb-1" id="ligneFrais">
              Frais de livraison : <strong id="valeurFrais">echo htmlentities(number_format($fraisAffiches, 2, ',', ' ')); €</strong>
              <span id="valeurDistance" class="vg-text-secondary">
                echo htmlentities($estimationLivraison['distance_km'] !== null ? '(' . number_format($estimationLivraison['distance_km'], 1, ',', ' ') . ' km)' : '');
              </span>
            </p>
            <p class="mb-0">Total = <strong id="ligneTotal">echo htmlentities(number_format($sousTotalAffiche + $fraisAffiches, 2, ',', ' ')); €</strong></p>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <h2 class="mb-3">Informations personnelles</h2>
        <p class="mb-1"><?= htmlspecialchars($utilisateur['prenom']) ?> <?= htmlspecialchars($utilisateur['nom']) ?></p>
        <p class="mb-3 vg-text-secondary"><?= htmlspecialchars($utilisateur['email']) ?> — <?= htmlspecialchars($utilisateur['telephone'] ?? '') ?></p>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="date_prestation" class="vg-label">Date de prestation*</label>
            <input type="date" class="form-control vg-input" id="date_prestation" name="date_prestation" required
              value="<?= htmlspecialchars($valeurs['date_prestation']) ?>">
          </div>
          <div class="col-md-6 mb-3">
            <label for="heure_prestation" class="vg-label">Heure*</label>
            <input type="time" class="form-control vg-input" id="heure_prestation" name="heure_prestation" required
              value="<?= htmlspecialchars($valeurs['heure_prestation']) ?>">
          </div>
        </div>

        <div class="mb-3">
          <label for="commentaires" class="vg-label">Commentaires (optionnel)</label>
          <textarea class="form-control vg-input" id="commentaires" name="commentaires" rows="2" style="height:auto;"><?= htmlspecialchars($valeurs['commentaires']) ?>
          </textarea>
        </div>
      </div>

      <div class="col-lg-4">
        <h2 class="mb-3">Adresse de livraison</h2>
        <div class="mb-3">
          <label for="adresse_livraison" class="vg-label">Adresse*</label>
          <input type="text" class="form-control vg-input" id="adresse_livraison" name="adresse_livraison" required
            value="<?= htmlspecialchars($valeurs['adresse_livraison']) ?>">
        </div>
        <div class="row">
          <div class="col-md-4 mb-3">
            <label for="code_postal" class="vg-label">Code postal</label>
            <input type="text" class="form-control vg-input" id="code_postal" name="code_postal"
              value="<?= htmlspecialchars($valeurs['code_postal']) ?>">
          </div>
          <div class="col-md-8 mb-3">
            <label for="ville_livraison" class="vg-label">Ville*</label>
            <input type="text" class="form-control vg-input" id="ville_livraison" name="ville_livraison" required
              value="<?= htmlspecialchars($valeurs['ville_livraison']) ?>">
          </div>
        </div>
        <p class="vg-text-secondary small" id="texteLivraison">5 € de base, + 0,59 €/km hors Bordeaux (calculé automatiquement selon votre adresse).</p>

        <h2 class="mb-3 mt-4">Mode de paiement</h2>
        <div class="d-flex flex-wrap gap-2">
          <label class="btn btn-vg-filtre <?= $valeurs['mode_paiement'] === 'carte' ? 'active' : '' ?>">
            <input type="radio" name="mode_paiement" value="carte" class="d-none" <?= $valeurs['mode_paiement'] === 'carte' ? 'checked' : '' ?>>
            <i class="bi bi-credit-card"></i> Carte bancaire
          </label>
          <label class="btn btn-vg-filtre <?= $valeurs['mode_paiement'] === 'paypal' ? 'active' : '' ?>">
            <input type="radio" name="mode_paiement" value="paypal" class="d-none" <?= $valeurs['mode_paiement'] === 'paypal' ? 'checked' : '' ?>>
            <i class="bi bi-paypal"></i> PayPal
          </label>
          <label class="btn btn-vg-filtre <?= $valeurs['mode_paiement'] === 'virement' ? 'active' : '' ?>">
            <input type="radio" name="mode_paiement" value="virement" class="d-none" <?= $valeurs['mode_paiement'] === 'virement' ? 'checked' : '' ?>>
            <i class="bi bi-bank"></i> Virement
          </label>
        </div>
      </div>
    </div>

    <div class="text-center mt-4">
      <button type="submit" class="btn btn-vg-primary">Valider la commande</button>
    </div>
  </form>
</div>

<script src="/js/commande.js"></script>

<?php
require_once __DIR__ . '/../views/partials/footer.php';
