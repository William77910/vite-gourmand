<?php
require_once __DIR__ . '/../src/Helpers/auth.php';
exiger_role(['employe', 'administrateur']);

require_once __DIR__ . '/../src/Helpers/csrf.php';
require_once __DIR__ . '/../src/Models/Commande.php';

$messages = ['succes' => [], 'erreurs' => []];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!verifier_token_csrf($_POST['csrf_token'] ?? null)) {
    $messages['erreurs'][] = "Votre session a expiré, veuillez réessayer.";
  } else {
    $action = $_POST['action'] ?? '';
    $idCommande = (int) ($_POST['id_commande'] ?? 0);

    if ($action === 'changer_statut') {
      $nouveauStatut = $_POST['nouveau_statut'] ?? '';
      if (Commande::changerStatut($idCommande, $nouveauStatut, $_SESSION['id_utilisateur'])) {

        // --- SYNCHRONISATION MONGODB (ECF) ---
        require_once __DIR__ . '/../src/Helpers/stats_helper.php';
        mettre_a_jour_statut_mongo($idCommande, $nouveauStatut);
        // -------------------------------------

        $messages['succes'][] = "Statut de la commande #$idCommande mis à jour.";
      } else {
        $messages['erreurs'][] = "Transition de statut non autorisée.";
      }
    }

    if ($action === 'annuler') {
      $motif = trim($_POST['motif'] ?? '');
      $modeContact = $_POST['mode_contact'] ?? '';
      if ($motif === '' || !in_array($modeContact, ['appel', 'mail'], true)) {
        $messages['erreurs'][] = "Le motif et le mode de contact sont obligatoires pour annuler une commande.";
      } else {
        Commande::annulerParEmploye($idCommande, $_SESSION['id_utilisateur'], $motif, $modeContact);
        $messages['succes'][] = "Commande #$idCommande annulée.";
      }
    }
  }
}

$filtres = [
  'statut' => $_GET['statut'] ?? '',
  'client' => $_GET['client'] ?? '',
];
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

  <?php foreach ($messages['succes'] as $m): ?>
    <div class="alert alert-success"><?= htmlspecialchars($m) ?></div>
  <?php endforeach; ?>
  <?php foreach ($messages['erreurs'] as $m): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($m) ?></div>
  <?php endforeach; ?>

  <form method="get" action="/employe-commandes.php" class="row g-2 mb-4">
    <div class="col-md-4">
      <select name="statut" class="form-select vg-input">
        <option value="">Tous les statuts</option>
        <?php foreach ($labels_statut as $valeur => $libelle): ?>
          <option value="<?= $valeur ?>" <?= $filtres['statut'] === $valeur ? 'selected' : '' ?>><?= $libelle ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-5">
      <input type="text" name="client" class="form-control vg-input" placeholder="Rechercher un client (nom, prénom, email)"
        value="<?= htmlspecialchars($filtres['client']) ?>">
    </div>
    <div class="col-md-3">
      <button type="submit" class="btn btn-vg-primary w-100">Filtrer</button>
    </div>
  </form>

  <?php if (empty($commandes)): ?>
    <p class="text-center vg-text-secondary">Aucune commande ne correspond à ces critères.</p>
  <?php endif; ?>

  <?php foreach ($commandes as $commande): ?>
    <div class="vg-texte-presentation mb-3">
      <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div>
          <h3 class="mb-1">#<?= (int) $commande['id_commande'] ?> — <?= htmlspecialchars($commande['titre_menu']) ?></h3>
          <p class="mb-1 vg-text-secondary">
            <?= htmlspecialchars($commande['prenom']) ?> <?= htmlspecialchars($commande['nom']) ?> —
            <?= htmlspecialchars($commande['email']) ?>
          </p>
          <p class="mb-1 vg-text-secondary">
            <?= (int) $commande['nb_personnes'] ?> personnes —
            <?= htmlspecialchars($commande['date_prestation']) ?> à <?= substr($commande['heure_prestation'], 0, 5) ?>
          </p>
          <p class="mb-0"><strong><?= number_format((float) $commande['prix_total'], 2, ',', ' ') ?> €</strong></p>
        </div>
        <span class="badge-vg-warning px-2 py-1 rounded">
          <?= $labels_statut[$commande['statut_actuel']] ?? $commande['statut_actuel'] ?>
        </span>
      </div>

      <?php $transitions = Commande::transitionsAutorisees($commande['statut_actuel']); ?>
      <?php if (!empty($transitions)): ?>
        <button type="button" class="btn btn-vg-secondary mt-3" data-bs-toggle="modal" data-bs-target="#modalStatut<?= (int) $commande['id_commande'] ?>">
          Gérer cette commande
        </button>

        <div class="modal fade" id="modalStatut<?= (int) $commande['id_commande'] ?>" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header">
                <h2 class="modal-title">Commande #<?= (int) $commande['id_commande'] ?></h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
              </div>
              <div class="modal-body">
                <!-- Changement de statut -->
                <form method="post" action="/employe-commandes.php" class="mb-4">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generer_token_csrf()) ?>">
                  <input type="hidden" name="action" value="changer_statut">
                  <input type="hidden" name="id_commande" value="<?= (int) $commande['id_commande'] ?>">

                  <label class="vg-label">Faire avancer le statut</label>
                  <div class="d-flex gap-2">
                    <select name="nouveau_statut" class="form-select vg-input">
                      <?php foreach ($transitions as $t): ?>
                        <?php if ($t !== 'annulee'): ?>
                          <option value="<?= $t ?>"><?= $labels_statut[$t] ?></option>
                        <?php endif; ?>
                      <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-vg-primary text-nowrap">Valider</button>
                  </div>
                </form>

                <?php if (in_array('annulee', $transitions, true)): ?>
                  <hr>
                  <!-- Annulation avec motif obligatoire -->
                  <form method="post" action="/employe-commandes.php">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generer_token_csrf()) ?>">
                    <input type="hidden" name="action" value="annuler">
                    <input type="hidden" name="id_commande" value="<?= (int) $commande['id_commande'] ?>">

                    <label class="vg-label">Annuler la commande</label>
                    <p class="vg-text-secondary small">Le client doit être contacté avant toute annulation.</p>

                    <textarea class="form-control vg-input mb-2" name="motif" placeholder="Motif de l'annulation*" required style="height:auto;" rows="2"></textarea>

                    <select name="mode_contact" class="form-select vg-input mb-2" required>
                      <option value="">Mode de contact du client*</option>
                      <option value="appel">Appel téléphonique</option>
                      <option value="mail">E-mail</option>
                    </select>

                    <button type="submit" class="btn btn-vg-secondary w-100" onclick="return confirm('Confirmez-vous l\'annulation de cette commande ?');">
                      Annuler la commande
                    </button>
                  </form>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>

<?php
require_once __DIR__ . '/../views/partials/footer.php';
