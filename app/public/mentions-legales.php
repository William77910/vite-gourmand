<?php
$current_page = 'CGV';
$page_title = 'Conditions Générales de Vente';
require_once __DIR__ . '/../views/partials/header.php';
?>

<div class="container my-5">
  <!-- SECTION 1 : HISTOIRE -->
  <section class="row align-items-center mb-5">
    <div class="col-12 col-lg-9 mb-4 mb-lg-0">
      <h1 class="display-4 font-italianno mb-4 vg-about-title">Mentions légales</h1>
      <div class="vg-lead-text">
        <ul>
          1. <Strong>Éditeur du site</Strong><br />
          Le présent site, accessible à l’URL [Votre URL de site] (le "Site"), est édité par :<br />
          <li>Nom de l'entreprise : Vite et Gourmand</li>
          <li>Forme juridique : [Ex: SAS, SARL, Entrepreneur individuel / Auto-entrepreneur]</li>
          <li>Capital social : [Montant du capital en euros, si applicable]</li>
          <li>Adresse du siège social : 7, rue de la ville, 33000 Bordeaux</li>
          <li>Numéro SIRET : [Votre numéro SIRET à 14 chiffres]</li>
          <li>Numéro de TVA intracommunautaire : [Votre numéro de TVA, si assujetti]</li>
          <li>RCS / RM : [Ville d'immatriculation] RCS / RM [Numéro]</li>
          <li>Téléphone : [Votre numéro de téléphone]</li>
          <li>Email : [Votre adresse email de contact]</li>
        </ul>
        <ul class="mt-3">
          2. <Strong>Directeur de la publication</Strong><br />
          <li>[Nom et Prénom du dirigeant ou du responsable].</li>
        </ul>

        <ul class="mt-3">
          3. <Strong>Hébergeur du site</Strong><br />
          Le Site est hébergé par :<br />
          <li>Nom de l'hébergeur : [Nom de l'hébergeur]</li>
          <li>Adresse : [Adresse complète de l'hébergeur]</li>
          <li>Téléphone : [Numéro de téléphone de l'hébergeur]</li>
        </ul>
        <ul class="mt-3">
          4. <Strong>Activité de traiteur et réglementation sanitaire</Strong><br />
          <li>Activité réglementée : Vente de denrées alimentaires périssables préparées pour la livraison à domicile.</li>
          <li>Respect de la chaîne du froid et des normes d'hygiène : L'entreprise respecte scrupuleusement la réglementation en vigueur relative à l’hygiène
            des denrées alimentaires (notamment le Paquet Hygiène et l'arrêté du 21 décembre 2009 relatif aux températures de conservation</li>
        </ul>
        <ul class="mt-3">
          5. <Strong>Propriété intellectuelle</Strong><br />
          L'ensemble de ce site relève de la législation française et internationale sur le droit d'auteur et la propriété intellectuelle. Tous les droits de
          reproduction sont réservés, y compris pour les documents téléchargeables et les représentations iconographiques et photographiques (notamment les photos
          de vos plats et buffets).
        </ul>
        <ul class="mt-3">
          6. <Strong>Données personnelles (RGPD) et Cookies</Strong><br />
          Le traitement de vos données personnelles est régi par notre politique de confidentialité, accessible depuis la section [Lien vers votre politique de
          confidentialité], conformément au Règlement Général sur la Protection des Données (RGPD 2016/679). Vous disposez d’un droit d’accès, de rectification et
          d’opposition de vos données en contactant l'entreprise par email à [Votre email].
        </ul>

      </div>
    </div>

  </section>

  <hr class="my-5 opacity-10">




</div> <!-- FIN DU CONTAINER PRINCIPAL -->

<?php
// Le footer est appelé APRÈS la fermeture du container pour pouvoir prendre toute la largeur
require_once __DIR__ . '/../views/partials/footer.php';
?>