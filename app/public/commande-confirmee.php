<?php
session_start();
// Securite : un utilisateur doit etre connecte pour voir cette page
if (!isset($_SESSION['id_utilisateur'])) {
  header('Location: /connexion.php');
  exit;
}
// Pour recuperer les infos de la commande
require_once __DIR__ . '/../src/Models/Commande.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$commande = $id > 0 ? Commande::getById($id) : null;

// Securite : un utilisateur ne doit voir que ses propres commandes
if (!$commande || $commande['id_utilisateur'] !== $_SESSION['id_utilisateur']) {
  header('Location: /index.php');
  exit;
}

$current_page = '';
$page_title = 'Commande confirmée';
require_once __DIR__ . '/../views/partials/header.php';
?>

<div class="container my-5 text-center">
  <i class="bi bi-check-circle-fill" style="font-size:48px; color: var(--vg-vert-fonce);"></i>
  <h1 class="mt-3">Merci, votre commande est enregistrée !</h1>
  <p class="vg-text-secondary">Un e-mail de confirmation vous sera envoyé prochainement.</p>

  <div class="row justify-content-center mt-4">
    <div class="col-lg-5">
      <div class="vg-texte-presentation text-start">
        <p class="mb-1"><strong>Menu :</strong> <?= htmlspecialchars($commande['titre_menu']) ?></p>
        <p class="mb-1"><strong>Nombre de personnes :</strong> <?= (int) $commande['nb_personnes'] ?></p>
        <p class="mb-1"><strong>Date :</strong> <?= htmlspecialchars($commande['date_prestation']) ?> à <?= htmlspecialchars(substr($commande['heure_prestation'], 0, 5)) ?></p>
        <p class="mb-1"><strong>Adresse :</strong> <?= htmlspecialchars($commande['adresse_livraison']) ?>, <?= htmlspecialchars($commande['ville_livraison']) ?></p>
        <p class="mb-0"><strong>Total payé :</strong> <?= number_format((float) $commande['prix_total'], 2, ',', ' ') ?> €</p>
      </div>
    </div>
  </div>

  <a href="/index.php" class="btn btn-vg-primary mt-4">Retour à l'accueil</a>
</div>

<?php
require_once __DIR__ . '/../views/partials/footer.php';
