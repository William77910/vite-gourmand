<?php
require_once __DIR__ . '/../../src/Models/Horaire.php';
$tousLesHoraires = Horaire::getAll();

$horairesGroupes = [];
foreach ($tousLesHoraires as $h) {
  $horairesGroupes[$h['jour_semaine']][] = $h;
}

function formaterHeure(?string $heure): string
{
  return $heure ? (new DateTime($heure))->format('H\hi') : '';
}
?>

<style>
  .vg-horaires-container {
    max-width: 450px;
    margin: 0 auto;
  }

  .vg-horaire-row {
    display: grid;
    /* Colonne 1 (Jour) : 100px */
    /* Colonne 2 (Midi) : 130px */
    /* Colonne 3 (Séparateur) : 20px */
    /* Colonne 4 (Soir) : 130px */
    grid-template-columns: 100px 130px 20px 130px;
    align-items: center;
    padding: 4px 0;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    font-size: 0.9rem;
  }

  .vg-horaire-jour {
    font-weight: bold;
    color: var(--vg-vert-fonce);
    text-align: left;
  }

  .vg-horaire-service {
    text-align: center;
    color: #666;
  }

  .vg-horaire-separateur {
    text-align: center;
    color: #ccc;
    font-weight: 300;
  }

  .vg-ferme-message {
    font-style: italic;
    font-size: 0.85rem;
    color: #999;
  }
</style>

<footer class="vg-footer py-4 mt-5">
  <div class="container">
    <div class="row justify-content-center text-center">

      <!-- SECTION HORAIRES ALIGNÉS -->
      <div class="col-md-7 mb-4">
        <h3 class="mb-3">Nos horaires</h3>
        <div class="vg-horaires-container">
          <?php foreach ($horairesGroupes as $jour => $services): ?>
            <div class="vg-horaire-row">
              <div class="vg-horaire-jour"><?= ucfirst($jour) ?> :</div>

              <?php foreach ($services as $index => $s): ?>
                <div class="vg-horaire-service">
                  <?php if ($s['ouvert']): ?>
                    <?= formaterHeure($s['heure_ouverture']) ?> - <?= formaterHeure($s['heure_fermeture']) ?>
                  <?php else: ?>
                    <span class="vg-ferme-message">
                      <?= $s['commentaire_fermeture'] ? htmlspecialchars($s['commentaire_fermeture']) : 'Fermé' ?>
                    </span>
                  <?php endif; ?>
                </div>

                <?php if ($index === 0): // Après le premier service (midi), on met la barre 
                ?>
                  <div class="vg-horaire-separateur">|</div>
                <?php endif; ?>
              <?php endforeach; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- RESTE DU FOOTER -->
      <div class="col-md-5 mb-3 d-flex flex-column justify-content-center">
        <h3>Vite et Gourmand</h3>
        <p class="vg-text-secondary mb-3">
          7, rue de la ville, 33000 Bordeaux<br>
          tél : 05 04 03 02 01
        </p>
        <div class="mb-2 fs-5">
          <a href="#" class="me-3 text-dark"><i class="bi bi-facebook"></i></a>
          <a href="#" class="text-dark"><i class="bi bi-instagram"></i></a>
        </div>
        <p class="vg-text-secondary small mb-0">
          <a href="/mentions-legales.php" class="text-decoration-none">Mentions légales</a> |
          <a href="/cgv.php" class="text-decoration-none">CGV</a><br>
          &copy; <?= date('Y') ?> Vite & Gourmand
        </p>
      </div>

    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>