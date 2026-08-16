<?php
session_start();

// Sécurité : Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
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

// --- GESTION DES ACTIONS POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!verifier_token_csrf($_POST['csrf_token'] ?? null)) {
    $messages['erreurs'][] = "Votre session a expiré, veuillez réessayer.";
  } else {
    $action = $_POST['action'] ?? '';

    // Mise à jour des informations personnelles
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

    // Annulation de commande par le client
    if ($action === 'annuler_commande') {
      $idCommande = (int) ($_POST['id_commande'] ?? 0);
      if (Commande::annulerParClient($idCommande, $idUtilisateur)) {
        // --- SYNCHRONISATION MONGODB ---
        require_once __DIR__ . '/../src/Helpers/stats_helper.php';
        mettre_a_jour_statut_mongo($idCommande, 'annulee');
        // -------------------------------
        $messages['succes'][] = "Votre commande a bien été annulée.";
      } else {
        $messages['erreurs'][] = "Cette commande ne peut plus être annulée.";
      }
    }

    // Laisser un avis
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

$current_page = 'compte';
$page_title = 'Mon compte';
require_once __DIR__ . '/../views/partials/header.php';
?>

<div class="container my-5">
  <h1 class="text-center mb-4">Mon compte</h1>

  <?php foreach ($messages['succes'] as $m): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($m) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  <?php endforeach; ?>
  <?php foreach ($messages['erreurs'] as $m): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($m) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  <?php endforeach; ?>

  <!-- ONGLETS -->
  <ul class="nav nav-tabs mb-4" id="compteTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="infos-tab" data-bs-toggle="tab" data-bs-target="#pane-infos" type="button" role="tab" aria-controls="pane-infos" aria-selected="true">Mes informations</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="commandes-tab" data-bs-toggle="tab" data-bs-target="#pane-commandes" type="button" role="tab" aria-controls="pane-commandes" aria-selected="false">Mes commandes</button>
    </li>
  </ul>

  <div class="tab-content" id="compteTabsContent">

    <!-- CONTENU 1 : INFORMATIONS -->
    <div class="tab-pane fade show active" id="pane-infos" role="tabpanel" aria-labelledby="infos-tab">
      <div class="row justify-content-center">
        <div class="col-lg-6">
          <form method="post" action="/compte.php" class="vg-texte-presentation p-4 border rounded shadow-sm bg-white">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generer_token_csrf()) ?>">
            <input type="hidden" name="action" value="maj_infos">

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="vg-label">Nom*</label>
                <input type="text" class="form-control vg-input" name="nom" required value="<?= htmlspecialchars($utilisateur['nom']) ?>">
              </div>
              <div class="col-md-6 mb-3">
                <label class="vg-label">Prénom*</label>
                <input type="text" class="form-control vg-input" name="prenom" required value="<?= htmlspecialchars($utilisateur['prenom']) ?>">
              </div>
            </div>
            <div class="mb-3">
              <label class="vg-label">Téléphone</label>
              <input type="tel" class="form-control vg-input" name="telephone" value="<?= htmlspecialchars($utilisateur['telephone'] ?? '') ?>">
            </div>
            <div class="mb-3">
              <label class="vg-label">Adresse postale</label>
              <input type="text" class="form-control vg-input" name="adresse_postale" value="<?= htmlspecialchars($utilisateur['adresse_postale'] ?? '') ?>">
            </div>
            <div class="row">
              <div class="col-md-4 mb-3">
                <label class="vg-label">Code postal</label>
                <input type="text" class="form-control vg-input" name="code_postal" value="<?= htmlspecialchars($utilisateur['code_postal'] ?? '') ?>">
              </div>
              <div class="col-md-8 mb-3">
                <label class="vg-label">Ville</label>
                <input type="text" class="form-control vg-input" name="ville" value="<?= htmlspecialchars($utilisateur['ville'] ?? '') ?>">
              </div>
            </div>
            <button type="submit" class="btn btn-vg-primary w-100">Enregistrer les modifications</button>
          </form>

          <hr class="my-5">

          <h2 class="h4 mb-3 text-center">Sécurité</h2>
          <form method="post" action="/compte.php" class="vg-texte-presentation p-4 border rounded shadow-sm bg-white">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generer_token_csrf()) ?>">
            <input type="hidden" name="action" value="maj_email">

            <div class="mb-3">
              <label class="vg-label">Nouvelle adresse email*</label>
              <input type="email" class="form-control vg-input" name="nouvel_email" required value="<?= htmlspecialchars($utilisateur['email']) ?>">
            </div>
            <div class="mb-3">
              <label class="vg-label">Mot de passe actuel (pour confirmer)*</label>
              <div class="input-group">
                <input type="password" class="form-control vg-input input-password" name="mot_de_passe_actuel" required>
                <button class="btn btn-outline-secondary toggle-password" type="button"><i class="bi bi-eye"></i></button>
              </div>
            </div>
            <button type="submit" class="btn btn-vg-secondary w-100">Mettre à jour l'email</button>
          </form>
        </div>
      </div>
    </div>

    <!-- CONTENU 2 : COMMANDES -->
    <div class="tab-pane fade" id="pane-commandes" role="tabpanel" aria-labelledby="commandes-tab">
      <div class="row justify-content-center">
        <div class="col-lg-9">
          <?php if (empty($commandes)): ?>
            <p class="text-center text-muted py-5">Vous n'avez pas encore passé de commande.</p>
          <?php else: ?>
            <?php foreach ($commandes as $c): ?>
              <?php
              // Logique de couleur de badge
              $badgeColor = 'bg-secondary';
              $statut = $c['statut_actuel'];
              if ($statut === 'en_attente') $badgeColor = 'bg-warning text-dark';
              elseif (in_array($statut, ['accepte', 'en_preparation', 'en_cours_de_livraison'])) $badgeColor = 'bg-info text-dark';
              elseif ($statut === 'annulee') $badgeColor = 'bg-danger';
              elseif ($statut === 'terminee' || $statut === 'livre') $badgeColor = 'bg-success';
              ?>
              <div class="card mb-3 shadow-sm border-0">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
                  <div>
                    <h5 class="mb-1"><?= htmlspecialchars($c['titre_menu']) ?></h5>
                    <p class="small text-muted mb-1">
                      Le <?= date('d/m/Y', strtotime($c['date_prestation'])) ?> à <?= substr($c['heure_prestation'], 0, 5) ?> (<?= (int)$c['nb_personnes'] ?> pers.)
                    </p>
                    <strong class="text-dark"><?= number_format($c['prix_total'], 2, ',', ' ') ?> €</strong>
                  </div>

                  <div class="text-end" style="min-width: 160px;">
                    <!-- LE BADGE DE STATUT -->
                    <span class="badge <?= $badgeColor ?> d-block p-2 mb-2">
                      <?= $labels_statut[$statut] ?? $statut ?>
                    </span>

                    <!-- ACTIONS -->
                    <div class="d-flex flex-column gap-1">
                      <?php if ($statut === 'en_attente'): ?>
                        <a href="/commande-modifier.php?id=<?= (int)$c['id_commande'] ?>" class="btn btn-sm btn-outline-secondary">Modifier</a>
                        <form method="post" action="/compte.php" onsubmit="return confirm('Annuler cette commande ?');">
                          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generer_token_csrf()) ?>">
                          <input type="hidden" name="action" value="annuler_commande">
                          <input type="hidden" name="id_commande" value="<?= (int) $c['id_commande'] ?>">
                          <button type="submit" class="btn btn-sm btn-outline-danger w-100">Annuler</button>
                        </form>
                      <?php elseif ($statut !== 'annulee'): ?>
                        <button class="btn btn-sm btn-link text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#suivi<?= $c['id_commande'] ?>">
                          <i class="bi bi-info-circle"></i> Suivre
                        </button>
                      <?php endif; ?>

                      <?php if ($statut === 'terminee' && !$c['a_un_avis']): ?>
                        <button type="button" class="btn btn-sm btn-vg-primary" data-bs-toggle="modal" data-bs-target="#modalAvis<?= $c['id_commande'] ?>">Donner mon avis</button>
                      <?php endif; ?>
                    </div>
                  </div>

                  <!-- Zone de suivi dépliable -->
                  <div class="collapse w-100 mt-3" id="suivi<?= $c['id_commande'] ?>">
                    <div class="p-3 bg-light rounded small">
                      <ul class="list-unstyled mb-0">
                        <?php foreach (Commande::getHistoriqueStatuts((int) $c['id_commande']) as $etape): ?>
                          <li class="border-bottom py-1">
                            <i class="bi bi-check2"></i> <strong><?= $labels_statut[$etape['statut']] ?></strong>
                            <span class="text-muted float-end"><?= date('d/m/Y H:i', strtotime($etape['date_modification'])) ?></span>
                          </li>
                        <?php endforeach; ?>
                      </ul>
                    </div>
                  </div>

                </div>
              </div>

              <!-- MODALE AVIS (Uniquement si terminée) -->
              <?php if ($statut === 'terminee' && !$c['a_un_avis']): ?>
                <div class="modal fade" id="modalAvis<?= $c['id_commande'] ?>" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h2 class="h5 modal-title">Votre avis sur "<?= htmlspecialchars($c['titre_menu']) ?>"</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <form method="post">
                        <div class="modal-body text-start">
                          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generer_token_csrf()) ?>">
                          <input type="hidden" name="action" value="laisser_avis">
                          <input type="hidden" name="id_commande" value="<?= (int) $c['id_commande'] ?>">

                          <label class="vg-label mb-2 d-block">Votre note</label>
                          <select name="note" class="form-select mb-3">
                            <option value="5">5 ★ (Excellent)</option>
                            <option value="4">4 ★ (Très bon)</option>
                            <option value="3" selected>3 ★ (Correct)</option>
                            <option value="2">2 ★ (Décevant)</option>
                            <option value="1">1 ★ (Mauvais)</option>
                          </select>

                          <label class="vg-label mb-2">Votre commentaire</label>
                          <textarea class="form-control" name="commentaire" rows="4" required placeholder="Comment était votre repas ?"></textarea>
                        </div>
                        <div class="modal-footer">
                          <button type="submit" class="btn btn-vg-primary w-100">Publier mon avis</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              <?php endif; ?>

            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>