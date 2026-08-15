<?php
require_once __DIR__ . '/../../src/Models/Horaire.php';

$horairesGroupes = [];
try {
  $tousLesHoraires = Horaire::getAll();
  foreach ($tousLesHoraires as $h) {
    $horairesGroupes[$h['jour_semaine']][] = $h;
  }
} catch (Exception $e) {
  error_log("Erreur horaires footer : " . $e->getMessage());
}

if (!function_exists('formaterHeure')) {
  function formaterHeure(?string $heure): string
  {
    return $heure ? (new DateTime($heure))->format('H\hi') : '';
  }
}
?>

<footer class="vg-footer mt-5">
  <div class="container-fluid px-4 px-lg-5">
    <div class="row g-4">

      <!-- COLONNE 1 : HORAIRES (GAUCHE) -->
      <div class="col-lg-4 col-md-6">
        <h3 class="vg-footer-title text-center text-lg-start">Nos horaires</h3>
        <div class="vg-footer-horaires mx-auto mx-lg-0">
          <?php if (!empty($horairesGroupes)): ?>
            <?php foreach ($horairesGroupes as $jour => $services): ?>
              <div class="vg-footer-horaire-row">
                <div class="vg-horaire-jour"><?= ucfirst($jour) ?> :</div>
                <?php foreach ($services as $index => $s): ?>
                  <div class="vg-horaire-service">
                    <?php if ($s['ouvert']): ?>
                      <?= formaterHeure($s['heure_ouverture']) ?>-<?= formaterHeure($s['heure_fermeture']) ?>
                    <?php else: ?>
                      <span class="vg-ferme-message"><?= htmlspecialchars($s['commentaire_fermeture'] ?? 'Fermé') ?></span>
                    <?php endif; ?>
                  </div>
                  <?php if ($index === 0): ?><div class="vg-horaire-separateur">|</div><?php endif; ?>
                <?php endforeach; ?>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- COLONNE 2 : IDENTITÉ (CENTRE) -->
      <div class="col-lg-4 col-md-6 text-center vg-footer-brand">
        <a href="/index.php" class="text-decoration-none">
          <img src="/images/logo.png" alt="Logo Vite & Gourmand">
          <h3 class="font-italianno" style="font-size: 3rem; color: var(--vg-vert-fonce); margin: 0;">Vite & Gourmand</h3>
        </a>
        <p class="text-muted small mt-2">Traiteur d'exception à Bordeaux</p>
        <div class="mt-3">
          <a href="#" class="me-3 text-dark" aria-label="Facebook"><i class="bi bi-facebook fs-4"></i></a>
          <a href="#" class="text-dark" aria-label="Instagram"><i class="bi bi-instagram fs-4"></i></a>
        </div>
      </div>

      <!-- COLONNE 3 : CONTACT & LÉGAL (DROITE) -->
      <div class="col-lg-4 col-md-12 text-center text-lg-end vg-footer-contact">
        <h3 class="vg-footer-title">Nous contacter</h3>
        <p class="vg-text-secondary fw-bold">7, rue de la ville, 33000 Bordeaux</p>
        <p class="vg-text-secondary mb-4">Tél : 05 04 03 02 01</p>

        <!-- Suppression de la classe border-top ici -->
        <div class="mt-4 pt-2">
          <a href="/mentions-legales.php" class="small text-muted me-2">Mentions légales</a>
          <span class="text-muted">|</span>
          <a href="/cgv.php" class="small text-muted ms-2">CGV</a>
          <p class="text-muted mt-2 mb-0" style="font-size: 0.7rem;">&copy; <?= date('Y') ?> Vite & Gourmand - Tous droits réservés</p>
        </div>
      </div>

    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/js/ui-utils.js"></script>

<?php
$currentFile = basename($_SERVER['PHP_SELF']);
if ($currentFile === 'inscription.php') echo '<script src="/js/inscription.js"></script>';
if ($currentFile === 'compte.php') echo '<script src="/js/compte.js"></script>';
?>
</body>

</html>