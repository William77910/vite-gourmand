<?php
session_start();

if (!isset($_SESSION['id_utilisateur'])) {
  header('Location: /connexion.php');
  exit;
}

require_once __DIR__ . '/../src/Helpers/csrf.php';
require_once __DIR__ . '/../src/Helpers/geolocalisation.php';
require_once __DIR__ . '/../src/Models/Commande.php';
require_once __DIR__ . '/../src/Models/Menu.php';

$idCommande = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$commande = Commande::getById($idCommande);

// SÉCURITÉ : Vérifier l'appartenance et si la commande est encore en attente
if (!$commande || (int)$commande['id_utilisateur'] !== (int)$_SESSION['id_utilisateur'] || $commande['statut_actuel'] !== 'en_attente') {
  header('Location: /compte.php');
  exit;
}

$menu = Menu::getById((int)$commande['id_menu']);
$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!verifier_token_csrf($_POST['csrf_token'] ?? null)) {
    $erreurs[] = "Votre session a expiré, veuillez réessayer.";
  } else {
    $nb = (int)$_POST['nb_personnes'];
    $nb = max($nb, (int)$menu['nb_personnes_min']); // Force le minimum

    // 1. Recalcul de la remise
    $reduction = $nb >= ((int)$menu['nb_personnes_min'] + 5);
    $sousTotal = (float)$menu['prix_personne'] * $nb * ($reduction ? 0.9 : 1);

    // 2. Recalcul des frais de livraison
    $resGeo = calculer_frais_livraison($_POST['adresse'], $_POST['cp'], $_POST['ville']);
    $prixTotal = $sousTotal + $resGeo['frais'];

    // 3. Mise à jour MySQL
    Commande::modifier($idCommande, $_SESSION['id_utilisateur'], [
      'nb_personnes' => $nb,
      'date_prestation' => $_POST['date'],
      'heure_prestation' => $_POST['heure'],
      'adresse_livraison' => $_POST['adresse'],
      'ville_livraison' => $_POST['ville'],
      'code_postal_livraison' => $_POST['cp'],
      'frais_livraison' => $resGeo['frais'],
      'reduction_appliquee' => $reduction,
      'prix_total' => $prixTotal,
      'commentaires' => $_POST['commentaires']
    ]);

    // 4. SYNCHRONISATION MONGODB (CORRIGÉ)
    require_once __DIR__ . '/../src/Helpers/stats_helper.php';
    if (function_exists('mettre_a_jour_commande_mongo')) {
      mettre_a_jour_commande_mongo($idCommande, [
        'montant'      => (float)$prixTotal,
        'nb_personnes' => (int)$nb,
        'statut'       => 'en_attente'
      ]);
    }

    header('Location: /compte.php?success=modifie');
    exit;
  }
}

$page_title = "Modifier ma commande";
require_once __DIR__ . '/../views/partials/header.php';
?>

<section class="container my-5">
  <h1 class="text-center mb-4 font-italianno" style="font-size: 3.5rem; color: var(--vg-vert-fonce);">Modifier ma commande</h1>

  <div class="row justify-content-center">
    <div class="col-lg-8">
      <?php if (!empty($erreurs)): ?>
        <div class="alert alert-danger shadow-sm">
          <ul class="mb-0"><?php foreach ($erreurs as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
        </div>
      <?php endif; ?>

      <form method="post" class="vg-texte-presentation p-4 border rounded shadow-sm bg-white">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generer_token_csrf()) ?>">

        <div class="mb-4 text-center border-bottom pb-3">
          <h2 class="h5 mb-1 text-vg-primary"><?= htmlspecialchars($commande['titre_menu']) ?></h2>
          <p class="text-muted small">Commande #<?= $idCommande ?></p>
        </div>

        <div class="row g-3">
          <div class="col-md-6">
            <label class="vg-label">Nombre de convives*</label>
            <input type="number" name="nb_personnes" class="form-control vg-input"
              value="<?= (int)$commande['nb_personnes'] ?>" min="<?= (int)$menu['nb_personnes_min'] ?>" required>
          </div>
          <div class="col-md-6">
            <label class="vg-label">Date de livraison*</label>
            <input type="date" name="date" class="form-control vg-input" value="<?= htmlspecialchars($commande['date_prestation']) ?>" required>
          </div>
          <div class="col-md-6">
            <label class="vg-label">Heure*</label>
            <input type="time" name="heure" class="form-control vg-input" value="<?= substr($commande['heure_prestation'], 0, 5) ?>" required>
          </div>
          <div class="col-12">
            <label class="vg-label">Adresse*</label>
            <input type="text" name="adresse" class="form-control vg-input" value="<?= htmlspecialchars($commande['adresse_livraison']) ?>" required>
          </div>
          <div class="col-md-4">
            <label class="vg-label">CP</label>
            <input type="text" name="cp" class="form-control vg-input" value="<?= htmlspecialchars($commande['code_postal_livraison']) ?>">
          </div>
          <div class="col-md-8">
            <label class="vg-label">Ville*</label>
            <input type="text" name="ville" class="form-control vg-input" value="<?= htmlspecialchars($commande['ville_livraison']) ?>" required>
          </div>
          <div class="col-12">
            <label class="vg-label">Instructions pour le livreur</label>
            <textarea name="commentaires" class="form-control vg-input" rows="3" style="height:auto;"><?= htmlspecialchars($commande['commentaires'] ?? '') ?></textarea>
          </div>
        </div>

        <div class="mt-4 pt-3 border-top">
          <button type="submit" class="btn btn-vg-primary w-100 btn-lg">Enregistrer les changements</button>
          <div class="text-center mt-3">
            <a href="/compte.php" class="text-secondary small">Annuler et revenir</a>
          </div>
        </div>
      </form>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>