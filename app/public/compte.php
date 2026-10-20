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
require_once __DIR__ . '/../src/Helpers/mot_de_passe.php';

$idUtilisateur = $_SESSION['id_utilisateur'];
$messages = ['succes' => [], 'erreurs' => []];

// Par défaut, l'onglet actif est "infos"
$tab_actif = 'infos';

// --- 2. TRAITEMENT DES FORMULAIRES ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!verifier_token_csrf($_POST['csrf_token'] ?? null)) {
    $messages['erreurs'][] = "Votre session a expiré, veuillez réessayer.";
  } else {
    $action = $_POST['action'] ?? '';

    // Mise à jour Infos Profil
    if ($action === 'maj_infos') {
      $data = [
        'nom' => trim($_POST['nom'] ?? ''),
        'prenom' => trim($_POST['prenom'] ?? ''),
        'telephone' => trim($_POST['telephone'] ?? ''),
        'adresse_postale' => trim($_POST['adresse_postale'] ?? ''),
        'code_postal' => trim($_POST['code_postal'] ?? ''),
        'ville' => trim($_POST['ville'] ?? '')
      ];
      if ($data['nom'] && $data['prenom']) {
        Utilisateur::update($idUtilisateur, $data);
        $_SESSION['prenom'] = $data['prenom'];
        $messages['succes'][] = "Vos informations ont été mises à jour.";
      }
    }

    // Annulation commande (CORRIGÉ)
    if ($action === 'annuler_commande') {
      $tab_actif = 'commandes'; // On force l'onglet commandes
      $idCmd = (int)$_POST['id_commande'];

      if (Commande::annulerParClient($idCmd, $idUtilisateur)) {
        require_once __DIR__ . '/../src/Helpers/stats_helper.php';
        if (function_exists('mettre_a_jour_commande_mongo')) {
          mettre_a_jour_commande_mongo($idCmd, ['statut' => 'annulee']);
        }
        $messages['succes'][] = "La commande a été annulée avec succès.";
      } else {
        $messages['erreurs'][] = "Impossible d'annuler cette commande.";
      }
    }

    // Traitement du dépôt d'avis
    if ($action === 'laisser_avis') {
      $tab_actif = 'commandes';
      $idCmd = (int)$_POST['id_commande'];
      $note = (int)$_POST['note'];
      $comm = trim($_POST['commentaire'] ?? '');
      if (Avis::create($idCmd, $idUtilisateur, $note, $comm)) {
        $messages['succes'][] = "Merci pour votre avis ! Il sera visible après validation.";
      } else {
        $messages['erreurs'][] = "Impossible d'enregistrer l'avis.";
      }
    }

    // Changement de mot de passe
    if ($action === 'maj_mdp') {
      $actuel = $_POST['mdp_actuel'] ?? '';
      $nouveau = $_POST['nouveau_mdp'] ?? '';
      $confirmation = $_POST['confirm_mdp'] ?? '';
      $utilisateur = Utilisateur::findById($idUtilisateur); // Besoin pour password_verify
      if (!password_verify($actuel, $utilisateur['mot_de_passe'])) {
        $messages['erreurs'][] = "Le mot de passe actuel est incorrect.";
      } elseif (!mot_de_passe_valide($nouveau)) {
        $messages['erreurs'][] = "Le nouveau mot de passe ne respecte pas les critères.";
      } elseif ($nouveau !== $confirmation) {
        $messages['erreurs'][] = "Les nouveaux mots de passe ne correspondent pas.";
      } else {
        Utilisateur::updatePassword($idUtilisateur, $nouveau);
        $messages['succes'][] = "Votre mot de passe a été modifié avec succès.";
      }
    }

    // Suppression / Anonymisation RGPD
    if ($action === 'supprimer_compte') {
      $mdp = $_POST['mdp_confirm'] ?? '';
      $utilisateur = Utilisateur::findById($idUtilisateur);
      if (!password_verify($mdp, $utilisateur['mot_de_passe'])) {
        $messages['erreurs'][] = "Mot de passe de confirmation incorrect.";
      } else {
        $commandesTemp = Commande::getByUtilisateur($idUtilisateur);
        $enCours = false;
        foreach ($commandesTemp as $c) {
          if (!in_array($c['statut_actuel'], ['terminee', 'annulee'])) {
            $enCours = true;
            break;
          }
        }
        if ($enCours) {
          $messages['erreurs'][] = "Impossible de supprimer le compte : une commande est en cours.";
        } else {
          Utilisateur::anonymiserCompte($idUtilisateur);
          session_destroy();
          header('Location: /index.php?info=compte_supprime');
          exit;
        }
      }
    }
  }
}

// --- 3. CHARGEMENT FINAL DES DONNÉES ---
$utilisateur = Utilisateur::findById($idUtilisateur);
$commandes = Commande::getByUtilisateur($idUtilisateur);

$labels_statut = [
  'en_attente'                => 'En attente',
  'accepte'                   => 'Acceptée',
  'en_preparation'            => 'En préparation',
  'en_cours_de_livraison'     => 'En cours de livraison',
  'livre'                     => 'Livrée',
  'en_attente_retour_materiel' => 'Attente matériel',
  'terminee'                  => 'Terminée',
  'annulee'                   => 'Annulée',
];

$page_title = 'Mon compte';
require_once __DIR__ . '/../views/partials/header.php';
?>

<section class="container my-5">
  <h1 class="text-center mb-4 vg-moncompte-title">Mon compte</h1>

  <?php foreach ($messages['succes'] as $m): ?><div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($m) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endforeach; ?>
  <?php foreach ($messages['erreurs'] as $m): ?><div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($m) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endforeach; ?>

  <!-- ONGLETS -->
  <ul class="nav nav-tabs mb-4" id="accountTabs" role="tablist">
    <li class="nav-item">
      <button class="nav-link <?= $tab_actif === 'infos' ? 'active' : '' ?> vg-link-accessible" id="btn-infos" data-bs-toggle="tab" data-bs-target="#pane-infos" type="button" role="tab">Mes informations</button>
    </li>
    <li class="nav-item">
      <button class="nav-link <?= $tab_actif === 'commandes' ? 'active' : '' ?> vg-link-accessible" id="btn-commandes" data-bs-toggle="tab" data-bs-target="#pane-commandes" type="button" role="tab">Mes commandes</button>
    </li>
  </ul>

  <div class="tab-content">
    <!-- ONGLET 1 : INFORMATIONS -->
    <div class="tab-pane fade <?= $tab_actif === 'infos' ? 'show active' : '' ?>" id="pane-infos" role="tabpanel">
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <form method="post" action="/compte.php" class="vg-texte-presentation p-4 border rounded shadow-sm bg-white mb-5">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generer_token_csrf()) ?>">
            <input type="hidden" name="action" value="maj_infos">
            <div class="row g-3">
              <div class="col-md-6">
                <label for="nom" class="vg-label">Nom*</label>
                <input type="text" id="nom" name="nom" class="form-control vg-input" value="<?= htmlspecialchars($utilisateur['nom']) ?>" required>
              </div>
              <div class="col-md-6">
                <label for="prenom" class="vg-label">Prénom*</label>
                <input type="text" id="prenom" name="prenom" class="form-control vg-input" value="<?= htmlspecialchars($utilisateur['prenom']) ?>" required>
              </div>
              <div class="col-12">
                <label for="telephone" class="vg-label">Téléphone</label>
                <input type="tel" id="telephone" name="telephone" class="form-control vg-input" value="<?= htmlspecialchars($utilisateur['telephone'] ?? '') ?>">
              </div>
              <div class="col-12">
                <label for="adresse_postale" class="vg-label">Adresse postale</label>
                <input type="text" id="adresse_postale" name="adresse_postale" class="form-control vg-input" value="<?= htmlspecialchars($utilisateur['adresse_postale'] ?? '') ?>">
              </div>
              <div class="col-md-4">
                <label for="code_postal" class="vg-label">Code postal</label>
                <input type="text" id="code_postal" name="code_postal" class="form-control vg-input" value="<?= htmlspecialchars($utilisateur['code_postal'] ?? '') ?>">
              </div>
              <div class="col-md-8">
                <label for="ville" class="vg-label">Ville</label>
                <input type="text" id="ville" name="ville" class="form-control vg-input" value="<?= htmlspecialchars($utilisateur['ville'] ?? '') ?>">
              </div>
            </div>
            <button type="submit" class="btn btn-vg-primary w-100 mt-4">Enregistrer les modifications</button>
          </form>

          <!-- Formulaire MDP -->
          <form method="post" action="/compte.php" class="vg-texte-presentation p-4 border rounded shadow-sm bg-white mb-5">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generer_token_csrf()) ?>">
            <input type="hidden" name="action" value="maj_mdp">
            <h2 class="h5 mb-3 text-center">Changer le mot de passe</h2>
            <div class="mb-3">
              <label for="mdp_actuel" class="vg-label">Mot de passe actuel</label>
              <input type="password" id="mdp_actuel" name="mdp_actuel" class="form-control vg-input" required>
            </div>
            <div class="mb-3">
              <label for="nouveau_mdp" class="vg-label">Nouveau mot de passe</label>
              <div class="input-group">
                <input type="password" id="nouveau_mdp" name="nouveau_mdp" class="form-control vg-input input-password" required>
                <button class="btn btn-outline-secondary toggle-password" type="button"><i class="bi bi-eye"></i></button>
              </div>
            </div>
            <ul class="list-unstyled small mb-3" id="listeCriteresMdp">
              <li data-critere="longueur" class="text-danger"><i class="bi bi-x-circle"></i> 10 caractères minimum</li>
              <li data-critere="majuscule" class="text-danger"><i class="bi bi-x-circle"></i> 1 majuscule</li>
              <li data-critere="chiffre" class="text-danger"><i class="bi bi-x-circle"></i> 1 chiffre</li>
              <li data-critere="special" class="text-danger"><i class="bi bi-x-circle"></i> 1 caractère spécial</li>
            </ul>
            <div class="mb-3">
              <label for="confirm_mdp" class="vg-label">Confirmation</label>
              <input type="password" id="confirm_mdp" name="confirm_mdp" class="form-control vg-input" required>
            </div>
            <button type="submit" class="btn btn-vg-secondary w-100">Modifier le mot de passe</button>
          </form>

          <div class="p-4 border border-danger rounded bg-white mb-5">
            <h2 class="h5 text-danger">Zone de danger</h2>
            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalSupprimerCompte">Supprimer mon compte</button>
          </div>
        </div>
      </div>
    </div>

    <!-- ONGLET 2 : COMMANDES -->
    <div class="tab-pane fade <?= $tab_actif === 'commandes' ? 'show active' : '' ?>" id="pane-commandes" role="tabpanel">
      <div class="row justify-content-center">
        <div class="col-lg-10">
          <?php if (empty($commandes)): ?>
            <div class="text-center py-5">
              <i class="bi bi-cart-x text-muted" style="font-size: 3rem;"></i>
              <p class="text-muted mt-3">Vous n'avez pas encore passé de commande.</p>
              <a href="/menus.php" class="btn btn-vg-primary">Découvrir nos menus</a>
            </div>
          <?php else: ?>
            <?php foreach ($commandes as $c): ?>
              <?php
              $badgeColor = 'bg-secondary';
              $s = $c['statut_actuel'];
              if ($s === 'en_attente') $badgeColor = 'bg-warning text-dark';
              elseif (in_array($s, ['accepte', 'en_preparation', 'en_cours_de_livraison'])) $badgeColor = 'bg-info text-dark';
              elseif ($s === 'annulee') $badgeColor = 'bg-danger';
              elseif ($s === 'terminee' || $s === 'livre') $badgeColor = 'bg-success';
              ?>
              <div class="card mb-3 shadow-sm border-0">
                <div class="card-body d-flex justify-content-between align-items-center">
                  <div>
                    <h5 class="mb-1"><?= htmlspecialchars($c['titre_menu']) ?></h5>
                    <p class="small text-muted mb-0">
                      Le <?= date('d/m/Y', strtotime($c['date_prestation'])) ?> (<?= (int)$c['nb_personnes'] ?> pers.)
                    </p>
                    <strong class="text-dark"><?= number_format($c['prix_total'], 2, ',', ' ') ?> €</strong>
                  </div>
                  <div class="text-end" style="min-width: 180px;">
                    <span class="badge <?= $badgeColor ?> d-block p-2 mb-2"><?= $labels_statut[$s] ?? $s ?></span>
                    <?php if ($s === 'en_attente'): ?>
                      <div class="d-flex gap-2">
                        <a href="/commande-modifier.php?id=<?= $c['id_commande'] ?>" class="btn btn-sm btn-outline-primary flex-grow-1">Modifier</a>
                        <form method="post" onsubmit="return confirm('Annuler cette commande ?');">
                          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generer_token_csrf()) ?>">
                          <input type="hidden" name="id_commande" value="<?= $c['id_commande'] ?>">
                          <input type="hidden" name="action" value="annuler_commande">
                          <button type="submit" class="btn btn-sm btn-outline-danger" title="Annuler"><i class="bi bi-trash"></i></button>
                        </form>
                      </div>
                    <?php elseif ($s === 'terminee' && (int)$c['a_un_avis'] === 0): ?>
                      <button type="button" class="btn btn-sm btn-vg-primary w-100" data-bs-toggle="modal" data-bs-target="#modalAvis<?= $c['id_commande'] ?>">Laisser un avis</button>
                    <?php endif; ?>
                  </div>
                </div>
              </div>

              <!-- MODALE AVIS -->
              <?php if ($s === 'terminee' && (int)$c['a_un_avis'] === 0): ?>
                <div class="modal fade" id="modalAvis<?= $c['id_commande'] ?>" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                      <form method="post">
                        <div class="modal-header">
                          <h5 class="modal-title">Votre avis sur "<?= htmlspecialchars($c['titre_menu']) ?>"</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-start">
                          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generer_token_csrf()) ?>">
                          <input type="hidden" name="action" value="laisser_avis">
                          <input type="hidden" name="id_commande" value="<?= $c['id_commande'] ?>">

                          <label class="vg-label mb-2">Note :</label>
                          <select name="note" class="form-select mb-3">
                            <option value="5">5 ★ (Excellent)</option>
                            <option value="4">4 ★ (Très bon)</option>
                            <option value="3" selected>3 ★ (Correct)</option>
                            <option value="2">2 ★ (Décevant)</option>
                            <option value="1">1 ★ (Mauvais)</option>
                          </select>
                          <label class="vg-label mb-2">Commentaire :</label>
                          <textarea name="commentaire" class="form-control" rows="4" required placeholder="Partagez votre expérience..."></textarea>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                          <button type="submit" class="btn btn-vg-primary">Envoyer l'avis</button>
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
</section>

<!-- Modal Supprimer Compte -->
<div class="modal fade" id="modalSupprimerCompte" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header">
          <h5 class="modal-title text-danger">Supprimer définitivement le compte</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generer_token_csrf()) ?>">
          <input type="hidden" name="action" value="supprimer_compte">
          <p>Cette action est irréversible. Vos données personnelles seront anonymisées conformément au RGPD, mais vos factures seront conservées pour obligation comptable.</p>
          <p>Pour confirmer, saisissez votre mot de passe :</p>
          <input type="password" name="mdp_confirm" class="form-control vg-input" required>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-danger">Confirmer la suppression</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>

<!-- Script de gestion améliorée des onglets -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // 1. Gère l'activation automatique via l'URL (ex: #pane-commandes)
    const hash = window.location.hash;
    if (hash) {
      const targetTab = document.querySelector(`[data-bs-target="${hash}"]`);
      if (targetTab) {
        const tabTrigger = new bootstrap.Tab(targetTab);
        tabTrigger.show();
      }
    }

    // 2. Met à jour l'URL quand on clique sur un onglet (sans recharger)
    const tabs = document.querySelectorAll('button[data-bs-toggle="tab"]');
    tabs.forEach(tab => {
      tab.addEventListener('shown.bs.tab', (event) => {
        const target = event.target.getAttribute('data-bs-target');
        history.replaceState(null, null, target);
      });
    });
  });
</script>