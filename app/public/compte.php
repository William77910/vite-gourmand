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

// --- 1. CHARGEMENT DES DONNÉES ---
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

// --- 2. TRAITEMENT DES FORMULAIRES ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!verifier_token_csrf($_POST['csrf_token'] ?? null)) {
    $messages['erreurs'][] = "Session expirée, réessayez.";
  } else {
    $action = $_POST['action'] ?? '';

    // MISE À JOUR INFOS (NOM, TEL, ADRESSE...)
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
        $messages['succes'][] = "Informations mises à jour.";
        $utilisateur = Utilisateur::findById($idUtilisateur);
      }
    }

    // ANNULATION COMMANDE
    if ($action === 'annuler_commande') {
      $idCmd = (int)$_POST['id_commande'];
      if (Commande::annulerParClient($idCmd, $idUtilisateur)) {
        require_once __DIR__ . '/../src/Helpers/stats_helper.php';
        mettre_a_jour_statut_mongo($idCmd, 'annulee');
        $messages['succes'][] = "Commande annulée.";
        $commandes = Commande::getByUtilisateur($idUtilisateur);
      }
    }

    // CHANGEMENT MOT DE PASSE
    if ($action === 'maj_mdp') {
      $actuel = $_POST['mdp_actuel'] ?? '';
      $nouveau = $_POST['nouveau_mdp'] ?? '';
      $confirmation = $_POST['confirm_mdp'] ?? '';
      if (!password_verify($actuel, $utilisateur['mot_de_passe'])) {
        $messages['erreurs'][] = "Le mot de passe actuel est incorrect.";
      } elseif (!mot_de_passe_valide($nouveau)) {
        $messages['erreurs'][] = "Le nouveau mot de passe ne respecte pas les critères.";
      } elseif ($nouveau !== $confirmation) {
        $messages['erreurs'][] = "Les nouveaux mots de passe ne correspondent pas.";
      } else {
        Utilisateur::updatePassword($idUtilisateur, $nouveau);
        $messages['succes'][] = "Mot de passe modifié avec succès.";
      }
    }

    // SUPPRESSION DE COMPTE (RGPD)
    if ($action === 'supprimer_compte') {
      $mdp = $_POST['mdp_confirm'] ?? '';
      if (!password_verify($mdp, $utilisateur['mot_de_passe'])) {
        $messages['erreurs'][] = "Mot de passe de confirmation incorrect.";
      } else {
        $enCours = false;
        foreach ($commandes as $c) {
          if (!in_array($c['statut_actuel'], ['terminee', 'annulee'])) {
            $enCours = true;
            break;
          }
        }
        if ($enCours) {
          $messages['erreurs'][] = "Désolé, vous avez une commande en cours.";
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

$page_title = 'Mon compte';
require_once __DIR__ . '/../views/partials/header.php';
?>

<div class="container my-5">
  <h1 class="text-center mb-4">Mon compte</h1>

  <?php foreach ($messages['succes'] as $m): ?><div class="alert alert-success alert-dismissible fade show"><?= $m ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endforeach; ?>
  <?php foreach ($messages['erreurs'] as $m): ?><div class="alert alert-danger alert-dismissible fade show"><?= $m ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endforeach; ?>

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
    <!-- CONTENU 1 : INFOS PERSONNELLES -->
    <div class="tab-pane fade show active" id="pane-infos" role="tabpanel">
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <!-- Formulaire Profil -->
          <form method="post" class="bg-white p-4 border rounded shadow-sm mb-5">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generer_token_csrf()) ?>">
            <input type="hidden" name="action" value="maj_infos">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="vg-label">Nom*</label>
                <input type="text" name="nom" class="form-control vg-input" value="<?= htmlspecialchars($utilisateur['nom']) ?>" required>
              </div>
              <div class="col-md-6">
                <label class="vg-label">Prénom*</label>
                <input type="text" name="prenom" class="form-control vg-input" value="<?= htmlspecialchars($utilisateur['prenom']) ?>" required>
              </div>
              <div class="col-12">
                <label class="vg-label">Téléphone</label>
                <input type="tel" name="telephone" class="form-control vg-input" value="<?= htmlspecialchars($utilisateur['telephone'] ?? '') ?>">
              </div>
              <div class="col-12">
                <label class="vg-label">Adresse postale</label>
                <input type="text" name="adresse_postale" class="form-control vg-input" value="<?= htmlspecialchars($utilisateur['adresse_postale'] ?? '') ?>">
              </div>
              <div class="col-md-4">
                <label class="vg-label">Code postal</label>
                <input type="text" name="code_postal" class="form-control vg-input" value="<?= htmlspecialchars($utilisateur['code_postal'] ?? '') ?>">
              </div>
              <div class="col-md-8">
                <label class="vg-label">Ville</label>
                <input type="text" name="ville" class="form-control vg-input" value="<?= htmlspecialchars($utilisateur['ville'] ?? '') ?>">
              </div>
            </div>
            <button type="submit" class="btn btn-vg-primary w-100 mt-4">Enregistrer les modifications</button>
          </form>

          <!-- Formulaire Mot de passe -->
          <form method="post" class="bg-white p-4 border rounded shadow-sm mb-5">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generer_token_csrf()) ?>">
            <input type="hidden" name="action" value="maj_mdp">
            <h2 class="h5 mb-3 text-center">Changer le mot de passe</h2>
            <div class="mb-3">
              <label class="vg-label">Mot de passe actuel</label>
              <input type="password" name="mdp_actuel" class="form-control vg-input" required>
            </div>
            <div class="mb-3">
              <label class="vg-label">Nouveau mot de passe</label>
              <div class="input-group">
                <input type="password" id="mot_de_passe" name="nouveau_mdp" class="form-control vg-input input-password" required>
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
              <label class="vg-label">Confirmer le nouveau mot de passe</label>
              <input type="password" name="confirm_mdp" class="form-control vg-input" required>
            </div>
            <button type="submit" class="btn btn-vg-secondary w-100">Mettre à jour le mot de passe</button>
          </form>

          <!-- Zone de danger -->
          <div class="p-4 border border-danger rounded bg-white mb-5">
            <h2 class="h5 text-danger">Zone de danger</h2>
            <p class="small text-muted">Effacer vos données personnelles et fermer votre compte.</p>
            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalSupprimerCompte">Supprimer mon compte</button>
          </div>
        </div>
      </div>
    </div>

    <!-- CONTENU 2 : HISTORIQUE DES COMMANDES -->
    <div class="tab-pane fade" id="pane-commandes" role="tabpanel">
      <div class="row justify-content-center">
        <div class="col-lg-10">
          <?php if (empty($commandes)): ?>
            <p class="text-center text-muted py-5">Vous n'avez pas encore passé de commande.</p>
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
              <div class="card mb-3 border-0 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
                  <div>
                    <h5 class="mb-1"><?= htmlspecialchars($c['titre_menu']) ?></h5>
                    <p class="small text-muted mb-0">
                      Le <?= date('d/m/Y', strtotime($c['date_prestation'])) ?> (<?= (int)$c['nb_personnes'] ?> pers.)
                    </p>
                    <strong class="text-dark"><?= number_format($c['prix_total'], 2, ',', ' ') ?> €</strong>
                  </div>
                  <div class="text-end" style="min-width: 160px;">
                    <span class="badge <?= $badgeColor ?> d-block p-2 mb-2"><?= $labels_statut[$s] ?></span>
                    <?php if ($s === 'en_attente'): ?>
                      <form method="post" onsubmit="return confirm('Annuler cette commande ?');">
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
  </div>
</div>

<!-- Modal Supprimer Compte -->
<div class="modal fade" id="modalSupprimerCompte" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header">
          <h5 class="modal-title text-danger">Supprimer définitivement le compte</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generer_token_csrf()) ?>">
          <input type="hidden" name="action" value="supprimer_compte">
          <p>Cette action est irréversible. Pour confirmer, saisissez votre mot de passe :</p>
          <input type="password" name="mdp_confirm" class="form-control vg-input" required>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-danger">Supprimer mon compte</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>

<!-- SCRIPT DE SECOURS ONGLETS -->
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