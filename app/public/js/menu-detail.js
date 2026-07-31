document.addEventListener("DOMContentLoaded", function () {
  const spanNb = document.getElementById("nbPersonnes");
  const spanPrixTotal = document.getElementById("prixTotal");
  const btnMoins = document.getElementById("btnMoins");
  const btnPlus = document.getElementById("btnPlus");
  const lienCommander = document.getElementById("lienCommander");

  if (!spanNb) {
    return;
  }

  const minimum = parseInt(spanNb.dataset.min, 10);
  const prixUnitaire = parseFloat(spanNb.dataset.prix);
  let nb = minimum;

  function recalculer() {
    // Reduction de 10% si nb personnes >= minimum + 5 (regle du cahier des charges)
    const reduction = nb >= minimum + 5 ? 0.9 : 1;
    const total = prixUnitaire * nb * reduction;

    spanNb.textContent = nb;
    spanPrixTotal.textContent = total.toFixed(2).replace(".", ",") + " €";

    if (lienCommander) {
      lienCommander.href =
        "/commande.php?menu=" + getMenuIdDepuisUrl() + "&personnes=" + nb;
    }
  }

  function getMenuIdDepuisUrl() {
    const params = new URLSearchParams(window.location.search);
    return params.get("id") || "";
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
});
