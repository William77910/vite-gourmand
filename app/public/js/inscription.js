// Inscription.js
document.addEventListener("DOMContentLoaded", function () {
  const champMdp = document.getElementById("mot_de_passe");
  const liste = document.getElementById("listeCriteresMdp");

  if (!champMdp || !liste) {
    return;
  }

  const regles = {
    longueur: function (mdp) {
      return mdp.length >= 10;
    },
    majuscule: function (mdp) {
      return /[A-Z]/.test(mdp);
    },
    minuscule: function (mdp) {
      return /[a-z]/.test(mdp);
    },
    chiffre: function (mdp) {
      return /[0-9]/.test(mdp);
    },
    special: function (mdp) {
      return /[^A-Za-z0-9]/.test(mdp);
    },
  };

  champMdp.addEventListener("input", function () {
    const valeur = champMdp.value;

    Object.keys(regles).forEach(function (critere) {
      const li = liste.querySelector('[data-critere="' + critere + '"]');
      const icone = li.querySelector("i");
      const respecte = regles[critere](valeur);

      if (respecte) {
        icone.classList.remove("bi-x-circle", "text-danger");
        icone.classList.add("bi-check-circle", "text-success");
      } else {
        icone.classList.remove("bi-check-circle", "text-success");
        icone.classList.add("bi-x-circle", "text-danger");
      }
    });
  });
});
