<?php
require_once __DIR__ . '/../src/Helpers/auth.php';
exiger_role(['employe', 'administrateur']);

require_once __DIR__ . '/../src/Helpers/csrf.php';
require_once __DIR__ . '/../src/Helpers/upload.php';
require_once __DIR__ . '/../src/Models/Menu.php';
require_once __DIR__ . '/../src/Models/Plat.php';

$idMenu = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($_POST['id_menu'] ?? 0);
$modeEdition = $idMenu > 0;
$menu = $modeEdition ? Menu::getById($idMenu) : null;

if ($modeEdition && !$menu) {
  header('Location: /employe-menus.php');
  exit;
}

// RÉCUPÉRATION DES PLATS DISPONIBLES POUR LES LISTES DÉROULANTES
$entreesDispo = Plat::getAll('entree');
$platsDispo   = Plat::getAll('plat');
$dessertsDispo = Plat::getAll('dessert');

$erreurs = [];

// Action de suppression d'une image d'ambiance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'supprimer_image') {
  if (verifier_token_csrf($_POST['csrf_token'] ?? null) && $modeEdition) {
    Menu::supprimerImage((int) ($_POST['id_image'] ?? 0), $idMenu);
  }
  header('Location: /employe-menu-form.php?id=' . $idMenu);
  exit;
}

$valeurs = [
  'titre' => $menu['titre'] ?? '',
  'description' => $menu['description'] ?? '',
  'theme' => $menu['theme'] ?? 'classique',
  'regime' => $menu['regime'] ?? 'classique',
  'nb_personnes_min' => $menu['nb_personnes_min'] ?? 4,
  'prix_personne' => $menu['prix_personne'] ?? '',
  'conditions_menu' => $menu['conditions_menu'] ?? '',
  'stock_disponible' => $menu['stock_disponible'] ?? 10,
];

// TRAITEMENT DU FORMULAIRE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') !== 'supprimer_image') {
  if (!verifier_token_csrf($_POST['csrf_token'] ?? null)) {
    $erreurs[] = "Votre session a expiré, veuillez réessayer.";
  } else {
    foreach (['titre', 'description', 'theme', 'regime', 'conditions_menu'] as $champ) {
      $valeurs[$champ] = trim($_POST[$champ] ?? '');
    }
    $valeurs['nb_personnes_min'] = (int) ($_POST['nb_personnes_min'] ?? 0);
    $valeurs['prix_personne'] = (float) str_replace(',', '.', $_POST['prix_personne'] ?? '0');
    $valeurs['stock_disponible'] = (int) ($_POST['stock_disponible'] ?? 0);

    if ($valeurs['titre'] === '') {
      $erreurs[] = "Le titre est obligatoire.";
    }
    if ($valeurs['nb_personnes_min'] < 1) {
      $erreurs[] = "Le nombre de personnes minimum doit être d'au moins 1.";
    }
    if ($valeurs['prix_personne'] <= 0) {
      $erreurs[] = "Le prix par personne doit être supérieur à 0.";
    }

    if (empty($erreurs)) {
      // 1. Mise à jour ou création du Menu (Table 'menu')
      if ($modeEdition) {
        Menu::update($idMenu, $valeurs);
      } else {
        $idMenu = Menu::create($valeurs);
      }

      // 2. Gestion de la photo d'ambiance (Table 'image_menu')
      if (!empty($_FILES['photo']['name'])) {
        $nomFichier = televerser_image($_FILES['photo'], __DIR__ . '/images/menus');
        if ($nomFichier !== null) {
          Menu::definirPhotoAmbiance($idMenu, '/images/menus/' . $nomFichier);
        }
      }

      // 3. SYNCHRONISATION DES PLATS (Table 'menu_plat')
      // On récupère les IDs sélectionnés dans les <select>
      $idEntreeSelect = (int)($_POST['id_entree'] ?? 0);
      $idPlatSelect   = (int)($_POST['id_plat'] ?? 0);
      $idDessertSelect = (int)($_POST['id_dessert'] ?? 0);

      Plat::synchroniserMenuType($idMenu, 'entree', $idEntreeSelect);
      Plat::synchroniserMenuType($idMenu, 'plat', $idPlatSelect);
      Plat::synchroniserMenuType($idMenu, 'dessert', $idDessertSelect);

      header('Location: /employe-menus.php');
      exit;
    }
  }
}

$current_page = 'employe';
$page_title = $modeEdition ? 'Modifier un menu' : 'Créer un menu';
require_once __DIR__ . '/../views/partials/header.php';

$imagesExistantes = $modeEdition ? Menu::getImagesAvecId($idMenu) : [];
?>

<div class="container my-5">
  <h1 class="text-center mb-4"><?= $modeEdition ? 'Modifier le menu' : 'Créer un menu' ?></h1>

  <?php if (!empty($erreurs)): ?>
    <div class="alert alert-danger">
      <ul class="mb-0"><?php foreach ($erreurs as $erreur): ?><li><?= htmlspecialchars($erreur) ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>

  <form method="post" action="/employe-menu-form.php<?= $modeEdition ? '?id=' . $idMenu : '' ?>" enctype="multipart/form-data" novalidate>
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generer_token_csrf()) ?>">
    <?php if ($modeEdition): ?><input type="hidden" name="id_menu" value="<?= $idMenu ?>"><?php endif; ?>

    <!-- Section Infos Générales -->
    <div class="row">
      <div class="col-md-12 mb-3">
        <label class="vg-label">Titre du Menu*</label>
        <input type="text" class="form-control vg-input" name="titre" required value="<?= htmlspecialchars($valeurs['titre']) ?>">
      </div>
      <div class="col-md-12 mb-3">
        <label class="vg-label">Description</label>
        <textarea class="form-control vg-input" name="description" rows="2"><?= htmlspecialchars($valeurs['description']) ?></textarea>
      </div>
    </div>

    <hr class="my-4">

    <!-- SECTION SÉLECTION DES PLATS (REFONTE) -->
    <h2 class="mb-3">Composition du menu</h2>
    <p class="vg-text-secondary small">Sélectionnez des plats existants. Si un plat n'est pas dans la liste, créez-le d'abord dans l'espace plat.</p>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="vg-label">Thème du menu*</label>
        <select name="theme" class="form-select vg-input" required>
          <option value="classique" <?= $valeurs['theme'] === 'classique' ? 'selected' : '' ?>>Classique</option>
          <?php /* Attention : les valeurs 'noel' et 'paques' ne doivent pas avoir d'accents (conforme à l'ENUM SQL) */ ?>
          <option value="noel" <?= $valeurs['theme'] === 'noel' ? 'selected' : '' ?>>Noël</option>
          <option value="paques" <?= $valeurs['theme'] === 'paques' ? 'selected' : '' ?>>Pâques</option>
          <option value="evenement" <?= $valeurs['theme'] === 'evenement' ? 'selected' : '' ?>>Événement</option>
        </select>
      </div>
      <div class="col-md-6 mb-3">
        <label class="vg-label">Régime alimentaire*</label>
        <select name="regime" class="form-select vg-input" required>
          <option value="classique" <?= $valeurs['regime'] === 'classique' ? 'selected' : '' ?>>Classique (Viande/Poisson)</option>
          <option value="vegetarien" <?= $valeurs['regime'] === 'vegetarien' ? 'selected' : '' ?>>Végétarien</option>
          <option value="vegan" <?= $valeurs['regime'] === 'vegan' ? 'selected' : '' ?>>Vegan</option>
        </select>
      </div>
    </div>
    <div class="mb-3">
      <label class="vg-label">Conditions spécifiques (délais, stockage...)</label>
      <textarea class="form-control vg-input" name="conditions_menu" rows="2"><?= htmlspecialchars($valeurs['conditions_menu']) ?></textarea>
    </div>


    <div class="row g-3">
      <div class="col-md-4">
        <label class="vg-label">Entrée</label>
        <select name="id_entree" class="form-select vg-input">
          <option value="0">-- Aucune entrée --</option>
          <?php
          $platActuel = $modeEdition ? Plat::getParMenuEtType($idMenu, 'entree') : null;
          foreach ($entreesDispo as $p): ?>
            <option value="<?= $p['id_plat'] ?>" <?= ($platActuel && $platActuel['id_plat'] == $p['id_plat']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($p['nom']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="vg-label">Plat principal</label>
        <select name="id_plat" class="form-select vg-input">
          <option value="0">-- Aucun plat --</option>
          <?php
          $platActuel = $modeEdition ? Plat::getParMenuEtType($idMenu, 'plat') : null;
          foreach ($platsDispo as $p): ?>
            <option value="<?= $p['id_plat'] ?>" <?= ($platActuel && $platActuel['id_plat'] == $p['id_plat']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($p['nom']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="vg-label">Dessert</label>
        <select name="id_dessert" class="form-select vg-input">
          <option value="0">-- Aucun dessert --</option>
          <?php
          $platActuel = $modeEdition ? Plat::getParMenuEtType($idMenu, 'dessert') : null;
          foreach ($dessertsDispo as $p): ?>
            <option value="<?= $p['id_plat'] ?>" <?= ($platActuel && $platActuel['id_plat'] == $p['id_plat']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($p['nom']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <hr class="my-4">

    <!-- Section Tarification et Images d'ambiance -->
    <div class="row">
      <div class="col-md-4 mb-3">
        <label class="vg-label">Prix / personne (€)*</label>
        <input type="number" step="0.01" class="form-control vg-input" name="prix_personne" required value="<?= htmlspecialchars((string)$valeurs['prix_personne']) ?>">
      </div>
      <div class="col-md-4 mb-3">
        <label class="vg-label">Personnes min.*</label>
        <input type="number" class="form-control vg-input" name="nb_personnes_min" value="<?= htmlspecialchars((string)$valeurs['nb_personnes_min']) ?>">
      </div>
      <div class="col-md-4 mb-3">
        <label class="vg-label">Stock disponible</label>
        <input type="number" class="form-control vg-input" name="stock_disponible" value="<?= htmlspecialchars((string)$valeurs['stock_disponible']) ?>">
      </div>
    </div>

    <div class="mb-4">
      <label class="vg-label">Ajouter une photo d'ambiance (galerie)</label>
      <input type="file" class="form-control vg-input" name="photo" accept="image/*">
    </div>

    <?php if (!empty($imagesExistantes)): ?>
      <div class="mb-4">
        <label class="vg-label d-block mb-2">Galerie d'ambiance actuelle</label>
        <div class="d-flex gap-3 flex-wrap">
          <?php foreach ($imagesExistantes as $image): ?>
            <div class="text-center">
              <img src="<?= htmlspecialchars($image['url_image']) ?>" style="width:100px;height:100px;object-fit:cover;border-radius:8px;">
              <br>
              <button type="submit" name="action" value="supprimer_image" onclick="document.getElementById('id_image_del').value='<?= $image['id_image'] ?>';" class="btn btn-danger btn-sm mt-1">Supprimer</button>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <div class="text-center mt-4">
      <button type="submit" class="btn btn-vg-primary">Enregistrer le menu</button>
      <a href="/employe-menus.php" class="btn btn-vg-secondary">Annuler</a>
    </div>

    <!-- Champ caché pour la suppression d'image via le même bouton submit -->
    <input type="hidden" name="id_image" id="id_image_del" value="">
  </form>
</div>

<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>