<?php
require_once __DIR__ . '/../src/Helpers/auth.php';
exiger_role(['employe', 'administrateur']);
require_once __DIR__ . '/../src/Models/Contact.php';
require_once __DIR__ . '/../src/Helpers/csrf.php';

$messages_notif = ['succes' => [], 'erreurs' => []];

// GESTION DES ACTIONS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!verifier_token_csrf($_POST['csrf_token'] ?? null)) {
    $messages_notif['erreurs'][] = "Session expirée.";
  } else {
    $id = (int)($_POST['id_contact'] ?? 0);
    if ($_POST['action'] === 'traiter') {
      Contact::marquerCommeTraite($id);
      $messages_notif['succes'][] = "Message marqué comme lu.";
    } elseif ($_POST['action'] === 'supprimer') {
      Contact::delete($id);
      $messages_notif['succes'][] = "Message supprimé.";
    }
  }
}

$demandes = Contact::getAll();
$current_page = 'employe';
$page_title = 'Demandes de contact';
require_once __DIR__ . '/../views/partials/header.php';
?>

<div class="container my-5">
  <h1 class="mb-4">Messages des clients</h1>

  <?php foreach ($messages_notif['succes'] as $m): ?><div class="alert alert-success"><?= $m ?></div><?php endforeach; ?>

  <div class="table-responsive bg-white p-3 border rounded shadow-sm">
    <table class="table table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th>Date</th>
          <th>Email</th>
          <th>Sujet</th>
          <th>Statut</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($demandes as $d): ?>
          <tr class="<?= $d['traite'] ? 'text-muted' : 'fw-bold' ?>">
            <td><?= date('d/m/Y H:i', strtotime($d['date_creation'])) ?></td>
            <td><?= htmlspecialchars($d['email']) ?></td>
            <td><?= htmlspecialchars($d['titre']) ?></td>
            <td>
              <span class="badge <?= $d['traite'] ? 'bg-light text-dark' : 'bg-warning text-dark' ?>">
                <?= $d['traite'] ? 'Traité' : 'Nouveau' ?>
              </span>
            </td>
            <td class="text-end">
              <div class="d-flex justify-content-end gap-2">
                <!-- Bouton Lire (Modale) -->
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#msgModal<?= $d['id_contact'] ?>">
                  <i class="bi bi-eye"></i>
                </button>

                <?php if (!$d['traite']): ?>
                  <form method="post" class="d-inline">
                    <input type="hidden" name="csrf_token" value="<?= generer_token_csrf() ?>">
                    <input type="hidden" name="id_contact" value="<?= $d['id_contact'] ?>">
                    <input type="hidden" name="action" value="traiter">
                    <button type="submit" class="btn btn-sm btn-outline-success"><i class="bi bi-check-lg"></i></button>
                  </form>
                <?php endif; ?>

                <form method="post" class="d-inline" onsubmit="return confirm('Supprimer ce message ?');">
                  <input type="hidden" name="csrf_token" value="<?= generer_token_csrf() ?>">
                  <input type="hidden" name="id_contact" value="<?= $d['id_contact'] ?>">
                  <input type="hidden" name="action" value="supprimer">
                  <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
              </div>

              <!-- MODALE POUR LIRE LE MESSAGE -->
              <div class="modal fade text-start" id="msgModal<?= $d['id_contact'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h2 class="h5 modal-title"><?= htmlspecialchars($d['titre']) ?></h2>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                      <p class="small text-muted mb-3">De : <?= htmlspecialchars($d['email']) ?> le <?= date('d/m/Y à H:i', strtotime($d['date_creation'])) ?></p>
                      <div class="p-3 bg-light rounded border">
                        <?= nl2br(htmlspecialchars($d['description'])) ?>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                      <a href="mailto:<?= $d['email'] ?>" class="btn btn-vg-primary">Répondre par mail</a>
                    </div>
                  </div>
                </div>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>