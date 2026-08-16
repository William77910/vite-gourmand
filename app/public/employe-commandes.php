<?php
require_once __DIR__ . '/../src/Helpers/auth.php';
exiger_role(['employe', 'administrateur']);

require_once __DIR__ . '/../src/Helpers/csrf.php';
require_once __DIR__ . '/../src/Models/Commande.php';

$messages = ['succes' => [], 'erreurs' => []];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!verifier_token_csrf($_POST['csrf_token'] ?? null)) {
    $messages['erreurs'][] = "Session expirée.";
  } else {
    $action = $_POST['action'] ?? '';
    $idCommande = (int) ($_POST['id_commande'] ?? 0);

    if ($action === 'changer_statut') {
      $nouveauStatut = $_POST['nouveau_statut'] ?? '';
      if (Commande::changerStatut($idCommande, $nouveauStatut, $_SESSION['id_utilisateur'])) {
        require_once __DIR__ . '/../src/Helpers/stats_helper.php';
        mettre_a_jour_statut_mongo($idCommande, $nouveauStatut);
        $messages['succes'][] = "Statut mis à jour.";
      }
    }
    if ($action === 'annuler') {
      $motif = trim($_POST['motif'] ?? '');
      $modeContact = $_POST['mode_contact'] ?? '';
      if ($motif && $modeContact) {
        Commande::annulerParEmploye($idCommande, $_SESSION['id_utilisateur'], $motif, $modeContact);
        require_once __DIR__ . '/../src/Helpers/stats_helper.php';
        mettre_a_jour_statut_mongo($idCommande, 'annulee');
        $messages['succes'][] = "Commande annulée.";
      }
    }
  }
}

$filtres = ['statut' => $_GET['statut'] ?? '', 'client' => $_GET['client'] ?? ''];
$commandes = Commande::getAll(array_filter($filtres));

$labels_statut = [
  'en_attente' => 'En attente',
  'accepte' => 'Acceptée',
  'en_preparation' => 'En préparation',
  'en_cours_de_livraison' => 'En cours de livraison',
  'livre' => 'Livrée',
  'en_attente_retour_materiel' => 'Attente retour matériel',
  'terminee' => 'Terminée',
  'annulee' => 'Annulée',
];

$current_page = 'employe';
$page_title = 'Gestion des commandes';
require_once __DIR__ . '/../views/partials/header.php';
?>

<div class="container my-5">
  <h1 class="text-center mb-4">Gestion des commandes</h1>

  <form method="get" class="row g-2 mb-4">
    <div class="col-md-4">
      <select name="statut" class="form-select vg-input">
        <option value="">Tous les statuts</option>
        <?php foreach ($labels_statut as $val => $lib): ?>
          <option value="<?= $val ?>" <?= $filtres['statut'] === $val ? 'selected' : '' ?>><?= $lib ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-5">
      <input type="text" name="client" class="form-control vg-input" placeholder="Rechercher un client..." value="<?= htmlspecialchars($filtres['client']) ?>">
    </div>
    <div class="col-md-3">
      <button type="submit" class="btn btn-vg-primary w-100">Filtrer</button>
    </div>
  </form>

  <?php foreach ($commandes as $c): ?>
    <?php
    $badgeColor = 'bg-secondary';
    $s = $c['statut_actuel'];
    if ($s === 'en_attente') $badgeColor = 'bg-warning text-dark';
    elseif (in_array($s, ['accepte', 'en_preparation', 'en_cours_de_livraison'])) $badgeColor = 'bg-info text-dark';
    elseif ($s === 'annulee') $badgeColor = 'bg-danger';
    elseif ($s === 'terminee') $badgeColor = 'bg-success';
    ?>
    <div class="card mb-3 shadow-sm border-0">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <h5 class="mb-1">#<?= $c['id_commande'] ?> — <?= htmlspecialchars($c['titre_menu']) ?></h5>
          <p class="small mb-0"><?= htmlspecialchars($c['prenom'] . ' ' . $c['nom']) ?> (<?= htmlspecialchars($c['email']) ?>)</p>
          <strong class="text-dark"><?= number_format($c['prix_total'], 2, ',', ' ') ?> €</strong>
        </div>
        <div class="text-end">
          <span class="badge <?= $badgeColor ?> mb-2 d-block p-2"><?= $labels_statut[$s] ?></span>
          <?php $trans = Commande::transitionsAutorisees($s);
          if (!empty($trans)): ?>
            <button class="btn btn-sm btn-vg-secondary" data-bs-toggle="modal" data-bs-target="#modStatut<?= $c['id_commande'] ?>">Gérer</button>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Modal de changement de statut (omise pour brièveté mais garde ta logique existante) -->
  <?php endforeach; ?>
</div>
<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>