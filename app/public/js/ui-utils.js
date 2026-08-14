/**
 * ui-utils.js
 * Regroupe les fonctions utilitaires pour l'interface utilisateur (UI).
 */

document.addEventListener("DOMContentLoaded", function () {
  /**
   * GESTION DE LA VISIBILITÉ DES MOTS DE PASSE
   * Cible tous les boutons avec la classe .toggle-password
   */
  const toggleButtons = document.querySelectorAll(".toggle-password");

  toggleButtons.forEach((button) => {
    button.addEventListener("click", function () {
      // On remonte au parent '.input-group' pour trouver l'input correspondant
      // Cela permet de gérer plusieurs champs sur la même page (ex: inscription)
      const inputGroup = this.closest(".input-group");
      const input = inputGroup.querySelector("input");
      const icon = this.querySelector("i");

      if (input.type === "password") {
        // On affiche le mot de passe
        input.type = "text";
        // On change l'icône
        icon.classList.remove("bi-eye");
        icon.classList.add("bi-eye-slash");
      } else {
        // On cache le mot de passe
        input.type = "password";
        // On remet l'icône d'origine
        icon.classList.remove("bi-eye-slash");
        icon.classList.add("bi-eye");
      }
    });
  });
});
