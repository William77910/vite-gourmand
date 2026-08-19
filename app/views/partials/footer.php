<?php
require_once __DIR__ . '/../../src/Models/Horaire.php';
$horairesGroupes = [];
try {
  $tousLesHoraires = Horaire::getAll();
  foreach ($tousLesHoraires as $h) {
    $horairesGroupes[$h['jour_semaine']][] = $h;
  }
} catch (Exception $e) {
  $horairesGroupes = [];
}

if (!function_exists('formaterHeure')) {
  function formaterHeure(?string $heure): string
  {
    return $heure ? (new DateTime($heure))->format('H\hi') : '';
  }
}
?>

</main>

<footer class="vg-footer mt-5">
  <div class="container-fluid px-4 px-lg-5">
    <div class="row g-4 align-items-center">

      <!-- 1. HORAIRES -->
      <div class="col-lg-4">
        <h3 class="vg-footer-title">Nos horaires</h3>
        <div class="mx-auto mx-lg-0" style="max-width: 350px;">
          <?php foreach ($horairesGroupes as $jour => $services): ?>
            <div class="vg-footer-horaire-row">
              <div class="fw-bold"><?= ucfirst($jour) ?> :</div>
              <?php foreach ($services as $index => $s): ?>
                <div class="text-center text-muted">
                  <?= $s['ouvert'] ? formaterHeure($s['heure_ouverture']) . '-' . formaterHeure($s['heure_fermeture']) : '<i>Fermé</i>' ?>
                </div>
                <?php if ($index === 0): ?><div>|</div><?php endif; ?>
              <?php endforeach; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- 2. LOGO CENTRAL -->
      <div class="col-lg-4 text-center">
        <a href="/index.php" class="text-decoration-none vg-footer-brand">
          <img src="/images/logo.png" alt="Logo">
          <h3 class="font-italianno" style="font-size: 2.5rem; color: var(--vg-vert-fonce); margin-bottom:0;">Vite & Gourmand</h3>
        </a>
        <p class="text-muted small">Votre traiteur à Bordeaux</p>
      </div>

      <!-- 3. CONTACT -->
      <div class="col-lg-4 text-center text-lg-end">
        <h3 class="vg-footer-title">Nous contacter</h3>
        <p class="mb-1">7, rue de la ville, 33000 Bordeaux</p>
        <p class="mb-4">Tél : 05 04 03 02 01</p>
        <div class="small">
          <a href="/mentions-legales.php" class="text-muted">Mentions légales</a> |
          <a href="/cgv.php" class="text-muted">CGV</a>
          <p class="mt-2 text-muted" style="font-size: 0.7rem;">&copy; <?= date('Y') ?> Vite & Gourmand</p>
        </div>
      </div>

    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/js/ui-utils.js"></script>

<?php
$currentFile = basename($_SERVER['PHP_SELF']);

// MODIFICATION ICI : On charge le script sur les DEUX pages
if ($currentFile === 'inscription.php' || $currentFile === 'compte.php'):
?>
  <script src="/js/inscription.js"></script>
<?php endif; ?>

</body>

</html>