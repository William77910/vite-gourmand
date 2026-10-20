document.addEventListener("DOMContentLoaded", function () {
  const mdp = document.getElementById("mot_de_passe");
  const list = document.getElementById("listeCriteresMdp");

  // BLOCAGE DE SÉCURITÉ : On s'arrête si on n'est pas sur la bonne page
  if (!mdp || !list) {
    console.log("Validation inscription ignorée sur cette page.");
    return;
  }

  mdp.addEventListener("input", function () {
    const val = mdp.value;
    // Vérification des critères de sécurité du mot de passe
    const regles = {
      longueur: val.length >= 10,
      majuscule: /[A-Z]/.test(val),
      minuscule: /[a-z]/.test(val),
      chiffre: /[0-9]/.test(val),
      special: /[^A-Za-z0-9]/.test(val),
    };
    // Mise à jour de l'affichage des critères
    Object.keys(regles).forEach((k) => {
      const el = list.querySelector(`[data-critere="${k}"]`);
      if (el) {
        el.className = regles[k] ? "text-success" : "text-danger";
        const icon = el.querySelector("i");
        if (icon)
          icon.className = regles[k] ? "bi bi-check-circle" : "bi bi-x-circle";
      }
    });
  });

  const email = document.getElementById("email");
  const err = document.getElementById("email-error");
  if (email && err) {
    email.addEventListener("input", () => {
      const isOk = email.checkValidity();
      err.style.display = isOk ? "none" : "block";
      email.classList.toggle("is-invalid", !isOk);
    });
  }
});
