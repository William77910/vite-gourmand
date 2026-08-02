<?php
session_start();
// Vérification de la connexion de l'utilisateur
if (!isset($_SESSION['id_utilisateur'])) {
  header('Location: /connexion.php');
  exit;
}

require_once __DIR__ . '/../src/Helpers/csrf.php';
require_once __DIR__ . '/../src/Helpers/geolocalisation.php';
require_once __DIR__ . '/../src/Models/Commande.php';
require_once __DIR__ . '/../src/Models/Menu.php';

$idCommande = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($_POST['id_commande'] ?? 0);
$commande = $idCommande > 0 ? Commande::getById($idCommande) : null;

if (!$commande || $commande['id_utilisateur'] !== $_SESSION['id_utilisateur']) {
  header('Location: /compte.php');
  exit;
}

$statutActuel = Commande::getHistoriqueStatuts($idCommande);
$dernierStatut = end($statutActuel)['statut'] ?? 'en_attente';
if ($dernierStatut !== 'en_attente') {
  // La commande a deja ete acceptee : plus modifiable
  header('Location: /compte.php');
  exit;
}

$menu = Menu::getById((int) $commande['id_menu']);
$erreurs = [];

$valeurs = [
  'nb_personnes' => (int) $commande['nb_personnes'],
  'date_prestation' => $commande['date_prestation'],
  'heure_prestation' => substr($commande['heure_prestation'], 0, 5),
  'adresse_livraison' => $commande['adresse_livraison'],
  'ville_livraison' => $commande['ville_livraison'],
  'code_postal' => $commande['code_postal_livraison'],
  'commentaires' => $commande['commentaires'] ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!verifier_token_csrf($_POST['csrf_token'] ?? null)) {
    $erreurs[] = "Votre session a expiré, veuillez réessayer.";
  } else {
    foreach (['date_prestation', 'heure_prestation', 'adresse_livraison', 'ville_livraison', 'commentaires'] as $champ) {
      $valeurs[$champ] = trim($_POST[$champ] ?? '');
    }
    $valeurs['code_postal'] = trim($_POST['code_postal'] ?? '');
    $valeurs['nb_personnes'] = max((int) ($_POST['nb_personnes'] ?? 0), (int) $menu['nb_personnes_min']);

    if ($valeurs['date_prestation'] === '' || $valeurs['heure_prestation'] === '') {
      $erreurs[] = "La date et l'heure de prestation sont obligatoires.";
    }
    if ($valeurs['adresse_livraison'] === '' || $valeurs['ville_livraison'] === '') {
      $erreurs[] = "L'adresse de livraison est obligatoire.";
    }

    $reductionAppliquee = $valeurs['nb_personnes'] >= ((int) $menu['nb_personnes_min'] + 5);
    $sousTotal = (float) $menu['prix_personne'] * $valeurs['nb_personnes'] * ($reductionAppliquee ? 0.9 : 1);
    $resultatLivraison = calculer_frais_livraison($valeurs['adresse_livraison'], $valeurs['code_postal'], $valeurs['ville_livraison']);
    $prixTotal = $sousTotal + $resultatLivraison['frais'];

    if (empty($erreurs)) {
      Commande::modifier($idCommande, $_SESSION['id_utilisateur'], [
        'nb_personnes' => $valeurs['nb_personnes'],
        'date_prestation' => $valeurs['date_prestation'],
        'heure_prestation' => $valeurs['heure_prestation'],
        'adresse_livraison' => $valeurs['adresse_livraison'],
        'ville_livraison' => $valeurs['ville_livraison'],
        'code_postal_livraison' => $valeurs['code_postal'],
        'frais_livraison' => $resultatLivraison['frais'],
        'reduction_appliquee' => $reductionAppliquee,
        'prix_total' => $prixTotal,
        'commentaires' => $valeurs['commentaires'] !== '' ? $valeurs['commentaires'] : null,
      ]);

      header('Location: /compte.php');
      exit;
    }
  }
}

$current_page = 'compte';
$page_title = 'Modifier ma commande';
require_once __DIR__ . '/../views/partials/header.php';

$reductionActive = $valeurs['nb_personnes'] >= ((int) $menu['nb_personnes_min'] + 5);
$sousTotalAffiche = (float) $menu['prix_personne'] * $valeurs['nb_personnes'] * ($reductionActive ? 0.9 : 1);
$estimationLivraison = calculer_frais_livraison($valeurs['adresse_livraison'], $valeurs['code_postal'], $valeurs['ville_livraison']);
$fraisAffiches = $estimationLivraison['frais'];
?>

<div class="container my-5">
  <h1 class="text-center mb-1">Modifier ma commande</h1>
  <p class="text-center vg-text-secondary mb-4">Le menu choisi ne peut pas être changé.</p>

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

  <form method="post" action="/commande-modifier.php?id=<?= $idCommande ?>" novalidate>
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generer_token_csrf()) ?>">
    <input type="hidden" name="id_commande" value="<?= $idCommande ?>">

    <div class="row justify-content-center g-4">
      <div class="col-lg-4">
        <div class="vg-menu-card">
          <div class="vg-menu-card-body">
            <div class="vg-menu-card-titre"><?= htmlspecialchars($menu['titre']) ?></div>
            <p class="mb-1"><?= number_format((float) $menu['prix_personne'], 2, ',', ' ') ?> € / personne</p>

            <label for="nb_personnes" class="vg-label">Quantité (personnes)</label>
            <input type="number" class="form-control vg-input mb-2" id="nb_personnes" name="nb_personnes"
              min="<?= (int) $menu['nb_personnes_min'] ?>" value="<?= $valeurs['nb_personnes'] ?>"
              data-prix="<?= (float) $menu['prix_personne'] ?>" data-min="<?= (int) $menu['nb_personnes_min'] ?>">

            <p class="mb-1" id="ligneSousTotal">Sous-total : <strong id="valeurSousTotal"><?= number_format($sousTotalAffiche, 2, ',', ' ') ?> €</strong></p>
            <p class="mb-1" id="ligneFrais">
              Frais de livraison : <strong id="valeurFrais"><?= number_format($fraisAffiches, 2, ',', ' ') ?> €</strong>
              <span id="valeurDistance" class="vg-text-secondary">
                <?= $estimationLivraison['distance_km'] !== null ? '(' . number_format($estimationLivraison['distance_km'], 1, ',', ' ') . ' km)' : '' ?>
              </span>
            </p>
            <p class="mb-0">Total = <strong id="ligneTotal"><?= number_format($sousTotalAffiche + $fraisAffiches, 2, ',', ' ') ?> €</strong></p>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <h2 class="mb-3">Prestation</h2>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="date_prestation" class="vg-label">Date*</label>
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
          <label for="commentaires" class="vg-label">Commentaires</label>
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
      </div>
    </div>

    <div class="text-center mt-4">
      <button type="submit" class="btn btn-vg-primary">Enregistrer les modifications</button>
      <a href="/compte.php" class="btn btn-vg-secondary">Annuler</a>
    </div>
  </form>
</div>

<script src="/js/commande.js"></script>

<?php
require_once __DIR__ . '/../views/partials/footer.php';
