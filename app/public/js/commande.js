// Gestion de la page de commande (calcul du total, estimation des frais de livraison, etc.)

// On attend que le DOM soit charge avant d'ajouter les ecouteurs d'evenements
document.addEventListener("DOMContentLoaded", function () {
  const champNbPersonnes = document.getElementById("nb_personnes");
  const valeurSousTotal = document.getElementById("valeurSousTotal");
  const valeurFrais = document.getElementById("valeurFrais");
  const valeurDistance = document.getElementById("valeurDistance");
  const ligneTotal = document.getElementById("ligneTotal");
  const champAdresse = document.getElementById("adresse_livraison");
  const champCodePostal = document.getElementById("code_postal");
  const champVille = document.getElementById("ville_livraison");

  let fraisActuels =
    parseFloat((valeurFrais.textContent || "0").replace(",", ".")) || 0;

  // Formate un nombre en montant monetaire (ex: 12.5 => "12,50 €")
  function formatMontant(nombre) {
    return nombre.toFixed(2).replace(".", ",") + " €";
  }

  // Recalcule le sous-total et le total de la commande en fonction du nombre de personnes et des frais de livraison
  function recalculerSousTotal() {
    const prixUnitaire = parseFloat(champNbPersonnes.dataset.prix);
    const minimum = parseInt(champNbPersonnes.dataset.min, 10);
    let nb = parseInt(champNbPersonnes.value, 10) || minimum;
    if (nb < minimum) {
      nb = minimum;
      champNbPersonnes.value = minimum;
    }

    // Application d'une reduction de 10% si le nombre de personnes est superieur ou egal a minimum + 5
    const reduction = nb >= minimum + 5 ? 0.9 : 1;
    const sousTotal = prixUnitaire * nb * reduction;

    valeurSousTotal.textContent = formatMontant(sousTotal);
    ligneTotal.textContent = formatMontant(sousTotal + fraisActuels);
  }

  // Estime les frais de livraison en fonction de l'adresse saisie par l'utilisateur
  function estimerLivraison() {
    const ville = champVille.value.trim();
    if (ville === "") {
      return;
    }

    const params = new URLSearchParams({
      adresse: champAdresse.value,
      code_postal: champCodePostal.value,
      ville: ville,
    });

    valeurFrais.textContent = "Calcul...";

    // On appelle l'API pour estimer les frais de livraison
    fetch("/api/estimer-livraison.php?" + params.toString())
      .then(function (reponse) {
        return reponse.json();
      })
      .then(function (data) {
        if (!data.success) {
          return;
        }
        fraisActuels = data.frais;
        valeurFrais.textContent = formatMontant(data.frais);
        valeurDistance.textContent =
          data.distance_km !== null
            ? "(" + data.distance_km.toString().replace(".", ",") + " km)"
            : "";
        recalculerSousTotal();
      })
      .catch(function () {
        // En cas d'echec (service indisponible), on garde le
        // dernier montant affiche plutot que de bloquer l'utilisateur
      });
  }

  // On ajoute les ecouteurs d'evenements pour recalculer le total et estimer les frais de livraison
  champNbPersonnes.addEventListener("input", recalculerSousTotal);
  champNbPersonnes.addEventListener("change", recalculerSousTotal);
  champVille.addEventListener("blur", estimerLivraison);
  champCodePostal.addEventListener("blur", estimerLivraison);
  champAdresse.addEventListener("blur", estimerLivraison);

  // Selection du mode de paiement (boutons stylises autour de radios cachees)
  document
    .querySelectorAll('input[name="mode_paiement"]')
    .forEach(function (radio) {
      radio.addEventListener("change", function () {
        document
          .querySelectorAll('input[name="mode_paiement"]')
          .forEach(function (r) {
            r.closest("label").classList.remove("active");
          });
        if (radio.checked) {
          radio.closest("label").classList.add("active");
        }
      });
    });
});
