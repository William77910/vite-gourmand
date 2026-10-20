<?php
require_once __DIR__ . '/../src/Helpers/auth.php';
exiger_role(['employe', 'administrateur']);
require_once __DIR__ . '/../src/Helpers/csrf.php';
require_once __DIR__ . '/../src/Helpers/upload.php';
require_once __DIR__ . '/../src/Models/Plat.php';
require_once __DIR__ . '/../src/Models/Image.php';
require_once __DIR__ . '/../src/Models/Allergene.php';

// 1. ON DÉFINIT LE CONTEXTE EN PREMIER (ID et Mode)
$idPlat = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$modeEdition = $idPlat > 0;
$plat = $modeEdition ? Plat::getById($idPlat) : null;

// 2. ON RÉCUPÈRE LES ALLERGÈNES (Maintenant que $modeEdition et $idPlat existent)
$tousAllergenes = Allergene::getAll();
$allergenesCoches = $modeEdition ? Allergene::getIdsParPlat($idPlat) : [];

$erreurs = [];
$valeurs = [
  'nom' => $plat['nom'] ?? '',
  'description' => $plat['description'] ?? '',
  'type_plat' => $plat['type_plat'] ?? 'plat',
  'id_image' => $plat['id_image'] ?? null
];

// 3. TRAITEMENT DU FORMULAIRE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!verifier_token_csrf($_POST['csrf_token'] ?? null)) {
    $erreurs[] = "Session expirée.";
  } else {
    $valeurs['nom'] = trim($_POST['nom'] ?? '');
    $valeurs['description'] = trim($_POST['description'] ?? '');
    $valeurs['type_plat'] = $_POST['type_plat'] ?? 'plat';

    if (empty($valeurs['nom'])) $erreurs[] = "Le nom est obligatoire.";

    if (empty($erreurs)) {
      // Gestion de l'image
      if (!empty($_FILES['photo']['name'])) {
        $nomFichier = televerser_image($_FILES['photo'], __DIR__ . '/images/menus');
        if ($nomFichier) {
          $valeurs['id_image'] = Image::creerOuRecuperer('/images/menus/' . $nomFichier);
        }
      }

      // A. ON ENREGISTRE LE PLAT EN PREMIER POUR AVOIR L'ID
      if ($modeEdition) {
        Plat::update($idPlat, $valeurs);
        $idCible = $idPlat;
      } else {
        // La méthode create() renvoie l'ID généré par MySQL
        $idCible = Plat::create($valeurs);
      }

      // B. ON SYNCHRONISE LES ALLERGÈNES (Maintenant on est sûr d'avoir un ID)
      $idsSelectionnes = $_POST['allergenes'] ?? [];
      Plat::synchroniserAllergenes($idCible, $idsSelectionnes);

      header('Location: /employe-plats.php');
      exit;
    }
  }
}

$current_page = 'employe';
$page_title = ($modeEdition ? 'Modifier' : 'Créer') . ' un plat';
require_once __DIR__ . '/../views/partials/header.php';
?>

<section class="container my-5">
  <div class="row justify-content-center">
    <div class="col-lg-6">
      <h1 class="text-center mb-4"><?= $page_title ?></h1>

      <?php if (!empty($erreurs)): ?>
        <div class="alert alert-danger">
          <ul class="mb-0"><?php foreach ($erreurs as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
        </div>
      <?php endif; ?>

      <form method="post" enctype="multipart/form-data" class="vg-texte-presentation p-4 border rounded shadow-sm bg-white" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generer_token_csrf()) ?>">

        <div class="mb-3">
          <label class="vg-label">Nom du plat*</label>
          <input type="text" name="nom" class="form-control vg-input" value="<?= htmlspecialchars($valeurs['nom']) ?>" required>
        </div>

        <div class="mb-3">
          <label class="vg-label">Type de plat*</label>
          <select name="type_plat" class="form-select vg-input">
            <option value="entree" <?= $valeurs['type_plat'] === 'entree' ? 'selected' : '' ?>>Entrée</option>
            <option value="plat" <?= $valeurs['type_plat'] === 'plat' ? 'selected' : '' ?>>Plat principal</option>
            <option value="dessert" <?= $valeurs['type_plat'] === 'dessert' ? 'selected' : '' ?>>Dessert</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="vg-label">Description</label>
          <textarea name="description" class="form-control vg-input" rows="3" style="height:auto;"><?= htmlspecialchars($valeurs['description']) ?></textarea>
        </div>

        <div class="mb-3">
          <label class="vg-label">Photo du plat</label>
          <input type="file" name="photo" class="form-control vg-input" accept="image/*">
          <?php if ($modeEdition && !empty($plat['photo'])): ?>
            <div class="mt-2 text-center">
              <p class="small text-muted">Photo actuelle :</p>
              <img src="<?= htmlspecialchars($plat['photo']) ?>" class="rounded shadow-sm" style="width:150px; height:100px; object-fit:cover;">
            </div>
          <?php endif; ?>
        </div>

        <!-- SECTION ALLERGÈNES -->
        <div class="mb-4">
          <label class="vg-label d-block mb-2">Allergènes présents :</label>
          <div class="row g-2 p-3 border rounded bg-light">
            <?php foreach ($tousAllergenes as $all): ?>
              <div class="col-md-6">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="allergenes[]"
                    value="<?= $all['id_allergene'] ?>"
                    id="all_<?= $all['id_allergene'] ?>"
                    <?= in_array($all['id_allergene'], $allergenesCoches) ? 'checked' : '' ?>>
                  <label class="form-check-label small" for="all_<?= $all['id_allergene'] ?>">
                    <?= htmlspecialchars($all['nom']) ?>
                  </label>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="text-center mt-4">
          <button type="submit" class="btn btn-vg-primary w-100">Enregistrer le plat</button>
          <a href="/employe-plats.php" class="btn btn-link text-secondary mt-2">Annuler et retourner à la liste</a>
        </div>
      </form>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>