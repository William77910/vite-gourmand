document.addEventListener("DOMContentLoaded", function () {
  const spanNb = document.getElementById("nbPersonnes");
  const spanPrixTotal = document.getElementById("prixTotal");
  const btnMoins = document.getElementById("btnMoins");
  const btnPlus = document.getElementById("btnPlus");
  const lienCommander = document.getElementById("lienCommander");

  // Nouveaux éléments pour la remise
  const ligneRemise = document.getElementById("ligneRemiseMenu");
  const valeurRemise = document.getElementById("valeurRemiseMenu");

  if (!spanNb) return;

  const minimum = parseInt(spanNb.dataset.min, 10);
  const prixUnitaire = parseFloat(spanNb.dataset.prix);
  let nb = minimum;

  function formatMontant(nombre) {
    return nombre.toFixed(2).replace(".", ",") + " €";
  }

  function recalculer() {
    // Règle métier : -10% si nb personnes >= minimum + 5
    const aUneReduction = nb >= minimum + 5;
    const prixBrut = prixUnitaire * nb;
    const montantRemise = aUneReduction ? prixBrut * 0.1 : 0;
    const totalFinal = prixBrut - montantRemise;

    // Mise à jour de l'affichage de la remise
    if (ligneRemise && valeurRemise) {
      if (aUneReduction) {
        ligneRemise.classList.remove("d-none");
        valeurRemise.textContent = "-" + formatMontant(montantRemise);
      } else {
        ligneRemise.classList.add("d-none");
      }
    }

    // Mise à jour des textes
    spanNb.textContent = nb;
    spanPrixTotal.textContent = formatMontant(totalFinal);

    // Mise à jour du lien pour transmettre le bon nombre au panier
    if (lienCommander) {
      const url = new URL(lienCommander.href, window.location.origin);
      url.searchParams.set("personnes", nb);
      lienCommander.href = url.pathname + url.search;
    }
  }

  btnMoins.addEventListener("click", function () {
    if (nb > minimum) {
      nb--;
      recalculer();
    }
  });

  btnPlus.addEventListener("click", function () {
    nb++;
    recalculer();
  });

  // Initialisation au chargement
  recalculer();
});
