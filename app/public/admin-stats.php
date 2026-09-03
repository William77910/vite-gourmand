<?php
require_once __DIR__ . '/../src/Helpers/auth.php';
exiger_role(['administrateur']);

// On inclut bien le Modèle Menu pour que la classe soit reconnue
require_once __DIR__ . '/../src/Models/Menu.php';
require_once __DIR__ . '/../src/Helpers/stats_helper.php';

// 1. Récupération des filtres
$dateDebut  = $_GET['date_debut'] ?? null;
$dateFin    = $_GET['date_fin'] ?? null;
$menuFiltre = $_GET['menu_nom'] ?? null;

// 2. Chargement de la liste des menus pour le select
$listeMenus = Menu::getAllActifs();

// 3. Récupération des stats via MongoDB
$statsVolume = obtenir_stats_commandes_par_menu($dateDebut, $dateFin, $menuFiltre);
$statsCA     = obtenir_ca_par_menu($dateDebut, $dateFin, $menuFiltre);

$page_title = "Tableau de bord - Statistiques";
require_once __DIR__ . '/../views/partials/header.php';
?>

<div class="container my-5">
  <h1 class="text-center mb-4">Analyse des ventes</h1>

  <!-- Formulaire de filtres -->
  <form method="get" class="row g-3 mb-5 bg-white p-4 border rounded shadow-sm">
    <div class="col-md-3">
      <label class="form-label small fw-bold">Date de début</label>
      <input type="date" name="date_debut" class="form-control" value="<?= htmlspecialchars($dateDebut ?? '') ?>">
    </div>
    <div class="col-md-3">
      <label class="form-label small fw-bold">Date de fin</label>
      <input type="date" name="date_fin" class="form-control" value="<?= htmlspecialchars($dateFin ?? '') ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label small fw-bold">Filtrer par Menu</label>
      <select name="menu_nom" class="form-select">
        <option value="">Tous les menus</option>
        <?php foreach ($listeMenus as $m): ?>
          <option value="<?= htmlspecialchars($m['titre']) ?>" <?= $menuFiltre === $m['titre'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($m['titre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2 d-flex align-items-end">
      <button type="submit" class="btn btn-vg-primary w-100">Actualiser</button>
    </div>
  </form>

  <div class="row g-4">
    <!-- Graphique Volume -->
    <div class="col-lg-6">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <h5 class="card-title mb-4">Volume de ventes par menu</h5>
          <canvas id="chartVolume"></canvas>
        </div>
      </div>
    </div>

    <!-- Graphique CA -->
    <div class="col-lg-6">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <h5 class="card-title mb-4">Répartition du Chiffre d'Affaires (€)</h5>
          <canvas id="chartCA"></canvas>
        </div>
      </div>
    </div>
  </div>
  <div class="mt-4">
    <a href="/espace-employe.php" class="btn btn-link text-secondary vg-link-accessible">
      <i class="bi bi-arrow-left"></i> Retour à l'espace employé
    </a>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const labels = <?= json_encode(array_keys($statsVolume)) ?>;
  const dataVolume = <?= json_encode(array_values($statsVolume)) ?>;
  const dataCA = <?= json_encode(array_values($statsCA)) ?>;

  // Graphique Barres (Volume)
  new Chart(document.getElementById('chartVolume'), {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Nombre de commandes',
        data: dataVolume,
        backgroundColor: '#7baf8b'
      }]
    }
  });

  // Graphique Doughnut (CA)
  new Chart(document.getElementById('chartCA'), {
    type: 'doughnut',
    data: {
      labels: <?= json_encode(array_keys($statsCA)) ?>,
      datasets: [{
        data: dataCA,
        backgroundColor: ['#4f7f5f', '#7baf8b', '#e8a15b', '#713704', '#6f6f6f']
      }]
    }
  });
</script>

<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>