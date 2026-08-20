/**
 * commande.js
 * Gère les calculs dynamiques de prix (avec remise) et l'estimation des frais de port.
 */

document.addEventListener("DOMContentLoaded", function () {
  // 1. Récupération des éléments du DOM
  const champNbPersonnes = document.getElementById("nb_personnes");
  const valeurSousTotal = document.getElementById("valeurSousTotal");
  const valeurFrais = document.getElementById("valeurFrais");
  const valeurDistance = document.getElementById("valeurDistance");
  const ligneTotal = document.getElementById("ligneTotal");
  const ligneRemise = document.getElementById("ligneRemise");
  const valeurRemise = document.getElementById("valeurRemise");

  const inputsAdresse = [
    document.getElementById("adresse_livraison"),
    document.getElementById("code_postal"),
    document.getElementById("ville_livraison"),
  ];

  // On initialise les frais avec ce qui est déjà écrit (souvent 15,00 ou 5,00)
  let fraisActuels =
    parseFloat((valeurFrais.textContent || "0").replace(",", ".")) || 0;

  /**
   * Formate un nombre en format monétaire FR
   */
  function formatMontant(nombre) {
    return nombre.toFixed(2).replace(".", ",") + " €";
  }

  /**
   * Recalcule le prix du menu, applique la remise si nécessaire et ajoute les frais
   */
  function recalculerTout() {
    if (!champNbPersonnes) return;

    const prixUnitaire = parseFloat(champNbPersonnes.dataset.prix);
    const minimum = parseInt(champNbPersonnes.dataset.min, 10);

    let nb = parseInt(champNbPersonnes.value, 10) || minimum;

    // Sécurité : on ne descend pas sous le minimum du menu
    if (nb < minimum) {
      nb = minimum;
      champNbPersonnes.value = minimum;
    }

    // Calcul de la remise (-10% si convives >= minimum + 5)
    const aUneReduction = nb >= minimum + 5;
    const prixBrut = prixUnitaire * nb;
    const montantRemise = aUneReduction ? prixBrut * 0.1 : 0;
    const sousTotal = prixBrut - montantRemise;

    // Affichage/Masquage de la ligne de remise (Bootstrap d-none)
    if (ligneRemise && valeurRemise) {
      if (aUneReduction) {
        ligneRemise.classList.remove("d-none");
        valeurRemise.textContent = "-" + formatMontant(montantRemise);
      } else {
        ligneRemise.classList.add("d-none");
      }
    }

    // Mise à jour des affichages
    valeurSousTotal.textContent = formatMontant(sousTotal);
    ligneTotal.textContent = formatMontant(sousTotal + fraisActuels);
  }

  /**
   * Appelle l'API PHP pour estimer les frais de port
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

        // Affichage de la distance si l'élément existe
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

  // --- ÉCOUTEURS D'ÉVÉNEMENTS ---

  // Changement du nombre de personnes
  if (champNbPersonnes) {
    champNbPersonnes.addEventListener("input", recalculerTout);
  }

  // Sortie des champs d'adresse (permet de calculer les frais dès que l'adresse est finie)
  inputsAdresse.forEach((input) => {
    if (input) input.addEventListener("blur", estimerLivraison);
  });

  // Sélection du mode de paiement (UI)
  document.querySelectorAll('input[name="mode_paiement"]').forEach((radio) => {
    radio.addEventListener("change", function () {
      document.querySelectorAll(".vg-payment-option").forEach((option) => {
        option.classList.remove("active");
      });
      if (this.checked) {
        this.closest(".vg-payment-option").classList.add("active");
      }
    });
  });

  // Calcul initial au chargement
  recalculerTout();
});
