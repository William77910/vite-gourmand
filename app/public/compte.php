<?php
session_start();

if (!isset($_SESSION['id_utilisateur'])) {
  header('Location: /connexion.php');
  exit;
}

require_once __DIR__ . '/../src/Helpers/csrf.php';
require_once __DIR__ . '/../src/Models/Utilisateur.php';
require_once __DIR__ . '/../src/Models/Commande.php';
require_once __DIR__ . '/../src/Models/Avis.php';

$idUtilisateur = $_SESSION['id_utilisateur'];
$messages = ['succes' => [], 'erreurs' => []];

// --- TRAITEMENT DES FORMULAIRES ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!verifier_token_csrf($_POST['csrf_token'] ?? null)) {
    $messages['erreurs'][] = "Session expirée, réessayez.";
  } else {
    $action = $_POST['action'] ?? '';

    if ($action === 'maj_infos') {
      $nom = trim($_POST['nom'] ?? '');
      $prenom = trim($_POST['prenom'] ?? '');
      if ($nom && $prenom) {
        Utilisateur::update($idUtilisateur, [
          'nom' => $nom,
          'prenom' => $prenom,
          'telephone' => trim($_POST['telephone'] ?? ''),
          'adresse_postale' => trim($_POST['adresse_postale'] ?? ''),
          'code_postal' => trim($_POST['code_postal'] ?? ''),
          'ville' => trim($_POST['ville'] ?? '')
        ]);
        $_SESSION['prenom'] = $prenom;
        $messages['succes'][] = "Informations mises à jour.";
      }
    }

    if ($action === 'annuler_commande') {
      $idCmd = (int)$_POST['id_commande'];
      if (Commande::annulerParClient($idCmd, $idUtilisateur)) {
        require_once __DIR__ . '/../src/Helpers/stats_helper.php';
        mettre_a_jour_statut_mongo($idCmd, 'annulee');
        $messages['succes'][] = "Commande annulée.";
      }
    }
  }
}

$utilisateur = Utilisateur::findById($idUtilisateur);
$commandes = Commande::getByUtilisateur($idUtilisateur);
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

$page_title = 'Mon compte';
require_once __DIR__ . '/../views/partials/header.php';
?>

<div class="container my-5">
  <h1 class="text-center mb-4">Mon compte</h1>

  <?php foreach ($messages['succes'] as $m): ?><div class="alert alert-success"><?= $m ?></div><?php endforeach; ?>
  <?php foreach ($messages['erreurs'] as $m): ?><div class="alert alert-danger"><?= $m ?></div><?php endforeach; ?>

  <!-- ONGLETS -->
  <ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item">
      <button class="nav-link active" id="btn-infos" data-bs-toggle="tab" data-bs-target="#pane-infos" type="button" role="tab">Mes informations</button>
    </li>
    <li class="nav-item">
      <button class="nav-link" id="btn-commandes" data-bs-toggle="tab" data-bs-target="#pane-commandes" type="button" role="tab">Mes commandes</button>
    </li>
  </ul>

  <div class="tab-content">
    <!-- CONTENU 1 : INFOS -->
    <div class="tab-pane fade show active" id="pane-infos" role="tabpanel">
      <div class="row justify-content-center">
        <div class="col-lg-7">
          <form method="post" class="bg-white p-4 border rounded shadow-sm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generer_token_csrf()) ?>">
            <input type="hidden" name="action" value="maj_infos">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="vg-label">Nom*</label>
                <input type="text" name="nom" class="form-control vg-input" value="<?= htmlspecialchars($utilisateur['nom']) ?>" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="vg-label">Prénom*</label>
                <input type="text" name="prenom" class="form-control vg-input" value="<?= htmlspecialchars($utilisateur['prenom']) ?>" required>
              </div>
            </div>
            <div class="mb-3">
              <label class="vg-label">Téléphone</label>
              <input type="tel" name="telephone" class="form-control vg-input" value="<?= htmlspecialchars($utilisateur['telephone'] ?? '') ?>">
            </div>
            <div class="mb-3">
              <label class="vg-label">Adresse postale</label>
              <input type="text" name="adresse_postale" class="form-control vg-input" value="<?= htmlspecialchars($utilisateur['adresse_postale'] ?? '') ?>">
            </div>
            <div class="row">
              <div class="col-md-4 mb-3">
                <label class="vg-label">Code postal</label>
                <input type="text" name="code_postal" class="form-control vg-input" value="<?= htmlspecialchars($utilisateur['code_postal'] ?? '') ?>">
              </div>
              <div class="col-md-8 mb-3">
                <label class="vg-label">Ville</label>
                <input type="text" name="ville" class="form-control vg-input" value="<?= htmlspecialchars($utilisateur['ville'] ?? '') ?>">
              </div>
            </div>
            <button type="submit" class="btn btn-vg-primary w-100">Enregistrer les modifications</button>
          </form>
        </div>
      </div>
    </div>

    <!-- ONGLET 2 : COMMANDES -->
    <div class="tab-pane fade" id="pane-commandes" role="tabpanel">
      <div class="row justify-content-center">
        <div class="col-lg-9">
          <?php if (empty($commandes)): ?>
            <p class="text-center text-muted py-5">Vous n'avez pas encore passé de commande.</p>
          <?php else: ?>
            <?php foreach ($commandes as $c): ?>
              <?php
              $badgeColor = 'bg-secondary';
              $statut = $c['statut_actuel'];
              if ($statut === 'en_attente') $badgeColor = 'bg-warning text-dark';
              if (in_array($statut, ['accepte', 'en_preparation', 'en_cours_de_livraison'])) $badgeColor = 'bg-info text-dark';
              if ($statut === 'livre' || $statut === 'terminee') $badgeColor = 'bg-success';
              if ($statut === 'annulee') $badgeColor = 'bg-danger';
              ?>
              <div class="card mb-3 shadow-sm border-0">
                <div class="card-body d-flex justify-content-between align-items-center">
                  <div>
                    <h5 class="mb-1"><?= htmlspecialchars($c['titre_menu']) ?></h5>
                    <p class="small text-muted mb-1">
                      Le <?= date('d/m/Y', strtotime($c['date_prestation'])) ?> (<?= (int)$c['nb_personnes'] ?> pers.)
                    </p>
                    <strong class="text-dark"><?= number_format($c['prix_total'], 2, ',', ' ') ?> €</strong>
                  </div>
                  <div class="text-end" style="min-width: 150px;">
                    <span class="badge <?= $badgeColor ?> mb-2 d-inline-block w-100 p-2">
                      <?= $labels_statut[$statut] ?? $statut ?>
                    </span>
                    <?php if ($statut === 'en_attente'): ?>
                      <form method="post" onsubmit="return confirm('Voulez-vous vraiment annuler cette commande ?');">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generer_token_csrf()) ?>">
                        <input type="hidden" name="id_commande" value="<?= $c['id_commande'] ?>">
                        <input type="hidden" name="action" value="annuler_commande">
                        <button type="submit" class="btn btn-sm btn-outline-danger w-100">Annuler</button>
                      </form>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div> <!-- Fin du tab-content -->
</div> <!-- Fin du container principal -->

<!-- LE FOOTER EST INCLUS ICI : À L'EXTÉRIEUR DE TOUTE DIV RESTRUCTIVE -->
<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>

<!-- SCRIPT DE SECOURS -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('[data-bs-toggle="tab"]');
    tabs.forEach(tab => {
      tab.addEventListener('click', function(e) {
        e.preventDefault();
        const targetId = this.getAttribute('data-bs-target');
        document.querySelectorAll('.nav-link').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('show', 'active'));
        this.classList.add('active');
        const targetPane = document.querySelector(targetId);
        if (targetPane) targetPane.classList.add('show', 'active');
      });
    });
  });
</script>