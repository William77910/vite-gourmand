// Gestion des filtres de la page "Nos menus" (sans rechargement de page)

document.addEventListener("DOMContentLoaded", function () {
  const conteneurMenus = document.getElementById("conteneur-menus");
  const inputPrixMax = document.getElementById("filtrePrixMax");
  const affichagePrixMax = document.getElementById("filtrePrixMaxValeur");
  const boutonValider = document.getElementById("btnValiderFiltre");
  const boutonAnnuler = document.getElementById("btnAnnulerFiltre");

  if (!conteneurMenus) {
    return; // on n'est pas sur la page menus.php
  }

  // Selection simple (un seul choix actif par groupe theme/regime/personnes)
  document.querySelectorAll("[data-groupe]").forEach(function (groupe) {
    groupe.querySelectorAll(".btn-vg-filtre").forEach(function (bouton) {
      bouton.addEventListener("click", function () {
        const dejaActif = bouton.classList.contains("active");
        groupe.querySelectorAll(".btn-vg-filtre").forEach(function (b) {
          b.classList.remove("active");
        });
        // Reclique sur le meme bouton => on desactive (filtre optionnel)
        if (!dejaActif) {
          bouton.classList.add("active");
        }
      });
    });
  });

  // Mise a jour de l'affichage du curseur de prix
  if (inputPrixMax) {
    inputPrixMax.addEventListener("input", function () {
      affichagePrixMax.textContent = inputPrixMax.value;
    });
  }

  // Reinitialisation des filtres
  boutonAnnuler.addEventListener("click", function () {
    document.querySelectorAll(".btn-vg-filtre.active").forEach(function (b) {
      b.classList.remove("active");
    });
    inputPrixMax.value = 50;
    affichagePrixMax.textContent = 50;
  });

  // Application des filtres (appel AJAX)
  boutonValider.addEventListener("click", function () {
    const params = new URLSearchParams();

    const themeActif = document.querySelector(
      '[data-groupe="theme"] .btn-vg-filtre.active',
    );
    const regimeActif = document.querySelector(
      '[data-groupe="regime"] .btn-vg-filtre.active',
    );
    const personnesActif = document.querySelector(
      '[data-groupe="personnes"] .btn-vg-filtre.active',
    );

    if (themeActif) params.set("theme", themeActif.dataset.valeur);
    if (regimeActif) params.set("regime", regimeActif.dataset.valeur);
    if (personnesActif)
      params.set("personnes_min", personnesActif.dataset.valeur);
    if (inputPrixMax && inputPrixMax.value < 50)
      params.set("prix_max", inputPrixMax.value);

    conteneurMenus.innerHTML =
      '<p class="text-center vg-text-secondary">Chargement...</p>';

    // Appel AJAX pour récupérer les menus filtrés
    fetch("/api/menus-filtrer.php?" + params.toString())
      .then(function (reponse) {
        return reponse.json();
      })
      .then(function (data) {
        if (!data.success) {
          conteneurMenus.innerHTML =
            '<p class="text-center vg-text-secondary">Une erreur est survenue.</p>';
          return;
        }
        afficherMenus(data.menus);
      })
      .catch(function () {
        conteneurMenus.innerHTML =
          '<p class="text-center vg-text-secondary">Impossible de charger les menus.</p>';
      });
  });

  function afficherMenus(menus) {
    if (menus.length === 0) {
      conteneurMenus.innerHTML =
        '<p class="text-center vg-text-secondary">Aucun menu ne correspond à votre recherche.</p>';
      return;
    }

    conteneurMenus.innerHTML = menus
      .map(function (menu) {
        const image = menu.image_principale || "/images/menus/placeholder.jpg";
        const prix = parseFloat(menu.prix_personne)
          .toFixed(2)
          .replace(".", ",");
        return (
          '<div class="col-md-4">' +
          '<div class="vg-menu-card">' +
          '<img src="' +
          image +
          '" alt="' +
          menu.titre +
          '">' +
          '<div class="vg-menu-card-body">' +
          '<div class="vg-menu-card-titre">' +
          menu.titre +
          "</div>" +
          '<p class="mb-1">' +
          prix +
          " € / personne</p>" +
          '<p class="vg-text-secondary flex-grow-1">' +
          menu.description +
          "</p>" +
          '<a href="/menu-detail.php?id=' +
          menu.id_menu +
          '" class="btn btn-vg-primary mt-2">Voir le menu</a>' +
          "</div>" +
          "</div>" +
          "</div>"
        );
      })
      .join("");
  }
});

// Fonction pour trier un tableau par date
const trierParDate = (tableau, cleDate) => {
  // Vérifie si le tableau existe et n'est pas vide
  if (!Array.isArray(tableau) || tableau.length === 0) {
    return [];
  }

  // Trie le tableau par la date spécifiée dans la clé 'cleDate'
  return tableau.sort((a, b) => new Date(a[cleDate]) - new Date(b[cleDate]));
};
