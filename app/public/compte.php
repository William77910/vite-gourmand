<?php
session_start();
// Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
if (!isset($_SESSION['id_utilisateur'])) {
  header('Location: /connexion.php');
  exit;
}

require_once __DIR__ . '/../src/Helpers/csrf.php';
require_once __DIR__ . '/../src/Models/Utilisateur.php';
require_once __DIR__ . '/../src/Models/Commande.php';
require_once __DIR__ . '/../src/Models/Avis.php';

// Récupérer l'ID de l'utilisateur connecté
$idUtilisateur = $_SESSION['id_utilisateur'];
$messages = ['succes' => [], 'erreurs' => []];

// Gestion des actions POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!verifier_token_csrf($_POST['csrf_token'] ?? null)) {
    $messages['erreurs'][] = "Votre session a expiré, veuillez réessayer.";
  } else {
    $action = $_POST['action'] ?? '';

    if ($action === 'maj_infos') {
      $nom = trim($_POST['nom'] ?? '');
      $prenom = trim($_POST['prenom'] ?? '');
      if ($nom === '' || $prenom === '') {
        $messages['erreurs'][] = "Le nom et le prénom sont obligatoires.";
      } else {
        Utilisateur::update($idUtilisateur, [
          'nom' => $nom,
          'prenom' => $prenom,
          'telephone' => trim($_POST['telephone'] ?? ''),
          'adresse_postale' => trim($_POST['adresse_postale'] ?? ''),
          'code_postal' => trim($_POST['code_postal'] ?? ''),
          'ville' => trim($_POST['ville'] ?? ''),
        ]);
        $_SESSION['prenom'] = $prenom;
        $messages['succes'][] = "Vos informations ont été mises à jour.";
      }
    }

    // Gestion de la mise à jour de l'email
    if ($action === 'maj_email') {
      $nouvelEmail = trim($_POST['nouvel_email'] ?? '');
      $motDePasseActuel = $_POST['mot_de_passe_actuel'] ?? '';
      $utilisateurActuel = Utilisateur::findById($idUtilisateur);

      if (!filter_var($nouvelEmail, FILTER_VALIDATE_EMAIL)) {
        $messages['erreurs'][] = "L'adresse email n'est pas valide.";
      } elseif (!password_verify($motDePasseActuel, $utilisateurActuel['mot_de_passe'])) {
        $messages['erreurs'][] = "Mot de passe incorrect.";
      } elseif ($nouvelEmail !== $utilisateurActuel['email'] && Utilisateur::findByEmail($nouvelEmail)) {
        $messages['erreurs'][] = "Cette adresse email est déjà utilisée par un autre compte.";
      } else {
        Utilisateur::updateEmail($idUtilisateur, $nouvelEmail);
        $messages['succes'][] = "Votre adresse email a été mise à jour.";
      }
    }

    if ($action === 'annuler_commande') {
      $idCommande = (int) ($_POST['id_commande'] ?? 0);
      if (Commande::annulerParClient($idCommande, $idUtilisateur)) {
        // --- SYNCHRONISATION MONGODB (POINT 2.4 DU PDF) ---
        require_once __DIR__ . '/../src/Helpers/stats_helper.php';
        mettre_a_jour_statut_mongo($idCommande, 'annulee');
        // --------------------------------------------------
        $messages['succes'][] = "Votre commande a bien été annulée.";
      } else {
        $messages['erreurs'][] = "Cette commande ne peut plus être annulée (déjà acceptée, en cours de préparation ou introuvable).";
      }
    }

    if ($action === 'laisser_avis') {
      $idCommande = (int) ($_POST['id_commande'] ?? 0);
      $note = (int) ($_POST['note'] ?? 0);
      $commentaire = trim($_POST['commentaire'] ?? '');

      if ($note < 1 || $note > 5 || $commentaire === '') {
        $messages['erreurs'][] = "Merci de donner une note et un commentaire.";
      } elseif (Avis::create($idCommande, $idUtilisateur, $note, $commentaire)) {
        $messages['succes'][] = "Merci pour votre avis ! Il sera visible après validation.";
      } else {
        $messages['erreurs'][] = "Impossible d'enregistrer cet avis.";
      }
    }
  }
}

$utilisateur = Utilisateur::findById($idUtilisateur);
$commandes = Commande::getByUtilisateur($idUtilisateur);

// Labels pour les statuts de commande
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

// Chargement du header
$current_page = 'compte';
$page_title = 'Mon compte';
require_once __DIR__ . '/../views/partials/header.php';
?>

<div class="container my-5">
  <h1 class="text-center mb-4">Mon compte</h1>

  <?php foreach ($messages['succes'] as $m): ?>
    <div class="alert alert-success"><?= htmlspecialchars($m) ?></div>
  <?php endforeach; ?>
  <?php foreach ($messages['erreurs'] as $m): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($m) ?></div>
  <?php endforeach; ?>

  <ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#ongletInfos" type="button">Mes informations</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" data-bs-toggle="tab" data-bs-target="#ongletCommandes" type="button">Mes commandes</button>
    </li>
  </ul>

  <div class="tab-content">
    <!-- Onglet informations personnelles -->
    <div class="tab-pane fade show active" id="ongletInfos">
      <div class="row justify-content-center">
        <div class="col-lg-6">
          <form method="post" action="/compte.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generer_token_csrf()) ?>">
            <input type="hidden" name="action" value="maj_infos">

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="vg-label">Nom*</label>
                <input type="text" class="form-control vg-input" name="nom" required
                  value="<?= htmlspecialchars($utilisateur['nom']) ?>">
              </div>
              <div class="col-md-6 mb-3">
                <label class="vg-label">Prénom*</label>
                <input type="text" class="form-control vg-input" name="prenom" required
                  value="<?= htmlspecialchars($utilisateur['prenom']) ?>">
              </div>
            </div>

            <div class="mb-3">
              <label class="vg-label">Téléphone</label>
              <input type="tel" class="form-control vg-input" name="telephone"
                value="<?= htmlspecialchars($utilisateur['telephone'] ?? '') ?>">
            </div>

            <div class="mb-3">
              <label class="vg-label">Adresse postale</label>
              <input type="text" class="form-control vg-input" name="adresse_postale"
                value="<?= htmlspecialchars($utilisateur['adresse_postale'] ?? '') ?>">
            </div>

            <div class="row">
              <div class="col-md-4 mb-3">
                <label class="vg-label">Code postal</label>
                <input type="text" class="form-control vg-input" name="code_postal"
                  value="<?= htmlspecialchars($utilisateur['code_postal'] ?? '') ?>">
              </div>
              <div class="col-md-8 mb-3">
                <label class="vg-label">Ville</label>
                <input type="text" class="form-control vg-input" name="ville"
                  value="<?= htmlspecialchars($utilisateur['ville'] ?? '') ?>">
              </div>
            </div>

            <div class="text-center">
              <button type="submit" class="btn btn-vg-primary">Enregistrer les modifications</button>
            </div>
          </form>

          <hr class="my-4">

          <h2 class="mb-3">Changer mon adresse email</h2>
          <form method="post" action="/compte.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generer_token_csrf()) ?>">
            <input type="hidden" name="action" value="maj_email">

            <div class="mb-3">
              <label class="vg-label">Nouvelle adresse email*</label>
              <input type="email" class="form-control vg-input" name="nouvel_email" required
                value="<?= htmlspecialchars($utilisateur['email']) ?>">
            </div>
            <div class="mb-3">
              <label class="vg-label">Mot de passe actuel (confirmation)*</label>
              <div class="input-group">
                <input type="password" class="form-control vg-input" name="mot_de_passe_actuel" required>
                <button class="btn btn-outline-secondary toggle-password" type="button">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
            </div>
            <div class="text-center">
              <button type="submit" class="btn btn-vg-secondary">Changer mon email</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Onglet historique des commandes -->
    <div class="tab-pane fade" id="ongletCommandes">
      <?php if (empty($commandes)): ?>
        <p class="text-center vg-text-secondary">Vous n'avez pas encore passé de commande.</p>
      <?php endif; ?>

      <?php foreach ($commandes as $commande): ?>
        <div class="vg-texte-presentation mb-3">
          <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
              <h3 class="mb-1"><?= htmlspecialchars($commande['titre_menu']) ?></h3>
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

          <?php if ($commande['statut_actuel'] === 'en_attente'): ?>
            <div class="mt-3 d-flex gap-2 flex-wrap">
              <a href="/commande-modifier.php?id=<?= (int) $commande['id_commande'] ?>" class="btn btn-vg-secondary">Modifier</a>
              <form method="post" action="/compte.php" onsubmit="return confirm('Confirmez-vous l\'annulation de cette commande ?');" class="d-inline">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generer_token_csrf()) ?>">
                <input type="hidden" name="action" value="annuler_commande">
                <input type="hidden" name="id_commande" value="<?= (int) $commande['id_commande'] ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger">Annuler la commande</button>
              </form>
            </div>
          <?php elseif (in_array($commande['statut_actuel'], ['accepte', 'en_preparation', 'en_cours_de_livraison', 'livre', 'en_attente_retour_materiel'], true)): ?>
            <button type="button" class="btn btn-vg-secondary mt-3" data-bs-toggle="collapse" data-bs-target="#suivi<?= (int) $commande['id_commande'] ?>">
              Voir le suivi de la commande
            </button>
            <div class="collapse mt-2" id="suivi<?= (int) $commande['id_commande'] ?>">
              <ul class="list-unstyled">
                <?php foreach (Commande::getHistoriqueStatuts((int) $commande['id_commande']) as $etape): ?>
                  <li class="vg-text-secondary">
                    <?= $labels_statut[$etape['statut']] ?? $etape['statut'] ?> —
                    <?= (new DateTime($etape['date_modification']))->format('d/m/Y à H:i') ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php elseif ($commande['statut_actuel'] === 'terminee' && !$commande['a_un_avis']): ?>
            <button type="button" class="btn btn-vg-primary mt-3" data-bs-toggle="modal" data-bs-target="#modalAvis<?= (int) $commande['id_commande'] ?>">
              Laisser un avis
            </button>

            <!-- Modal pour laisser un avis -->
            <div class="modal fade" id="modalAvis<?= (int) $commande['id_commande'] ?>" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header">
                    <h2 class="modal-title">Votre avis sur "<?= htmlspecialchars($commande['titre_menu']) ?>"</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                  </div>
                  <form method="post" action="/compte.php">
                    <div class="modal-body">
                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generer_token_csrf()) ?>">
                      <input type="hidden" name="action" value="laisser_avis">
                      <input type="hidden" name="id_commande" value="<?= (int) $commande['id_commande'] ?>">

                      <label class="vg-label mb-2 d-block">Note</label>
                      <div class="d-flex gap-2 mb-3">
                        <?php for ($n = 1; $n <= 5; $n++): ?>
                          <label class="btn btn-vg-filtre">
                            <input type="radio" name="note" value="<?= $n ?>" class="d-none" required> <?= $n ?> ★
                          </label>
                        <?php endfor; ?>
                      </div>

                      <label class="vg-label">Commentaire</label>
                      <textarea class="form-control vg-input" name="commentaire" rows="3" style="height:auto;" required></textarea>
                    </div>
                    <div class="modal-footer">
                      <button type="submit" class="btn btn-vg-primary">Envoyer mon avis</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<script src="/js/compte.js"></script>

<?php
require_once __DIR__ . '/../views/partials/footer.php';
