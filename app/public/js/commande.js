/**
 * commande.js
 * Gère les calculs dynamiques de prix et l'estimation des frais de port.
 */

document.addEventListener("DOMContentLoaded", function () {
  // Récupération des éléments du DOM
  const champNbPersonnes = document.getElementById("nb_personnes");
  const valeurSousTotal = document.getElementById("valeurSousTotal");
  const valeurFrais = document.getElementById("valeurFrais");
  const valeurDistance = document.getElementById("valeurDistance");
  const ligneTotal = document.getElementById("ligneTotal");

  const inputsAdresse = [
    document.getElementById("adresse_livraison"),
    document.getElementById("code_postal"),
    document.getElementById("ville_livraison"),
  ];

  let fraisActuels =
    parseFloat((valeurFrais.textContent || "0").replace(",", ".")) || 0;

  function formatMontant(nombre) {
    return nombre.toFixed(2).replace(".", ",") + " €";
  }

  /**
   * Recalcule le prix du menu + frais de livraison
   */
  function recalculerTout() {
    const prixUnitaire = parseFloat(champNbPersonnes.dataset.prix);
    const minimum = parseInt(champNbPersonnes.dataset.min, 10);
    let nb = parseInt(champNbPersonnes.value, 10) || minimum;

    // Sécurité : on ne descend pas sous le minimum
    if (nb < minimum) {
      nb = minimum;
      champNbPersonnes.value = minimum;
    }

    // Règle métier : -10% si on commande pour minimum + 5 personnes
    const reduction = nb >= minimum + 5 ? 0.9 : 1;
    const sousTotal = prixUnitaire * nb * reduction;

    valeurSousTotal.textContent = formatMontant(sousTotal);
    ligneTotal.textContent = formatMontant(sousTotal + fraisActuels);
  }

  /**
   * Appelle l'API PHP de géolocalisation
   */
  function estimerLivraison() {
    const adresse = inputsAdresse[0].value.trim();
    const cp = inputsAdresse[1].value.trim();
    const ville = inputsAdresse[2].value.trim();

    if (ville === "") return;

    const params = new URLSearchParams({ adresse, code_postal: cp, ville });
    valeurFrais.textContent = "Calcul...";

    fetch("/api/estimer-livraison.php?" + params.toString())
      .then((res) => {
        if (!res.ok) throw new Error("Erreur serveur HTTP " + res.status);
        return res.json();
      })
      .then((data) => {
        if (!data.success) return;

        fraisActuels = data.frais;
        valeurFrais.textContent = formatMontant(data.frais);

        // SÉCURITÉ : On vérifie si l'élément existe avant d'écrire dedans
        if (valeurDistance) {
          if (data.distance_km !== null) {
            valeurDistance.innerHTML = `<br><small class="text-muted">Distance : <strong>${data.distance_km} km</strong> <span class="badge bg-light text-dark border ms-1" style="font-size: 0.6rem;">Source : ${data.source}</span></small>`;
          } else {
            valeurDistance.textContent = "";
          }
        }

        recalculerTout();
      })
      .catch((err) => {
        console.error("Erreur Fetch détaillé :", err);
        valeurFrais.textContent = "Service indisponible";
      });
  }

  // Écouteurs d'événements
  champNbPersonnes.addEventListener("input", recalculerTout);

  inputsAdresse.forEach((input) => {
    if (input) input.addEventListener("blur", estimerLivraison);
  });

  // Sélection stylisée du mode de paiement
  document.querySelectorAll('input[name="mode_paiement"]').forEach((radio) => {
    radio.addEventListener("change", function () {
      // On retire la classe active de TOUTES les options de paiement
      document.querySelectorAll(".vg-payment-option").forEach((option) => {
        option.classList.remove("active");
      });

      // On l'ajoute uniquement à celle qui contient le bouton radio coché
      if (this.checked) {
        this.closest(".vg-payment-option").classList.add("active");
      }
    });
  });
});
