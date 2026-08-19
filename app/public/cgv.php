<?php
$current_page = 'CGV';
$page_title = 'Conditions Générales de Vente';
require_once __DIR__ . '/../views/partials/header.php';
?>

<div class="container my-5">
  <!-- SECTION 1 : HISTOIRE -->
  <section class="row align-items-center mb-5">
    <div class="col-12 col-lg-9 mb-4 mb-lg-0">
      <h1 class="display-4 font-italianno mb-4 vg-about-title">Conditions générales de vente</h1>
      <div class="vg-lead-text">
        <p>
          <Strong>Article 1</Strong> – Objet et Champ d'application<br /> Les présentes CGV s'appliquent à toutes les ventes de produits alimentaires et prestations de services de
          traiteur conclues par l'entreprise "Vite et Gourmand" auprès de clients consommateurs ou professionnels, via le site internet "Vite et Gourmand.com"
          ou par commande directe. Le fait de passer commande implique l'adhésion entière et sans réserve du client aux présentes CGV.
        </p>
        <p class="mt-3">
          <Strong>Article 2</Strong> – Commandes<br /> Toute commande est réputée ferme et définitive après validation du paiement ou signature du devis et réception
          d'un accusé de réception par le traiteur. Les modifications ou annulations de commande sont acceptées uniquement avant le passage du statut de la dite
          commande en "acceptée". Passé ce délai, la commande sera due en totalité.<br />
          Rappel légal : Conformément à l'article L.221-28 du Code de la consommation, le droit de
          rétractation ne s'applique pas aux contrats de fourniture de denrées alimentaires périssables (repas et produits frais).
        </p>
        <p class="mt-3">
          <Strong>Article 3</Strong>– Prix<br /> Les prix sont indiqués en Euros TTC (toutes taxes comprises), incluant la TVA applicable au taux en vigueur pour la
          restauration. Les frais de livraison sont facturés en sus, le montant exact est précisé au moment de la validation du panier ou sur le devis.
        </p>
        <p class="mt-3">
          <Strong>Article 4</Strong> – Modalités de Paiement<br /> Le règlement s'effectue en ligne par carte bancaire (via un système sécurisé) ou par tout autre
          moyen accepté et indiqué lors de la commande. La commande ne sera préparée et livrée qu'après encaissement total du montant dû.
        </p>
        <p class="mt-3">
          <Strong>Article 5</Strong> – Livraison à Domicile<br /> Les livraisons sont effectuées à l'adresse indiquée par le client lors de la commande, dans la zone
          géographique desservie par "Vite et Gourmand" et selon le créneau horaire choisi. En cas d'absence du client au moment de la livraison convenue et
          après tentative de contact infructueuse, les produits ne pourront être ni remboursés ni renvoyés immédiatement. Le livreur se réserve le droit de déposer
          le colis en lieu sûr ou de le ramener, les frais d'une nouvelle livraison étant à la charge du client. Il appartient au client de vérifier l'état apparent
          des produits lors de la livraison et d'émettre des réserves immédiates en cas d'anomalie manifeste.
        </p>
        <p class="mt-3">
          <Strong>Article 6</Strong> – Responsabilité et Conservation<br /> "Vite et Gourmand" garantit la fraîcheur des produits et le respect de la chaîne du
          foid jusqu'à la livraison. Dès la remise en main propre des produits au client (ou à son domicile), la responsabilité de la conservation, du respect des
          dates limites de consommation (DLC) et des consignes de réchauffage (le cas échéant) incombe entièrement au client.
        </p>
        <p class="mt-3">
          <Strong>Article 7</Strong> – Données Personnelles<br /> Les informations collectées font l'objet d'un traitement informatique destiné à la gestion des
          commandes. Conformément au RGPD, le client dispose d'un droit d'accès, de rectification et de suppression de ses données personnelles.
        </p>
        <p class="mt-3">
          <Strong>Article 8</Strong> – Litiges et Droit Applicable<br /> Les présentes CGV sont soumises à la loi française. En cas de litige, une solution amiable sera
          recherchée en priorité. À défaut, les tribunaux français compétents seront saisis.
        </p>

      </div>
    </div>

  </section>

  <hr class="my-5 opacity-10">




</div> <!-- FIN DU CONTAINER PRINCIPAL -->

<?php
// Le footer est appelé APRÈS la fermeture du container pour pouvoir prendre toute la largeur
require_once __DIR__ . '/../views/partials/footer.php';
?>