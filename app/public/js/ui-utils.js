/**
 * ui-utils.js
 * Regroupe les fonctions utilitaires pour l'interface utilisateur (UI).
 */

document.addEventListener("DOMContentLoaded", function () {
  const toggleButtons = document.querySelectorAll(".toggle-password");

  // On ne fait rien si aucun bouton n'est trouvé
  if (toggleButtons.length === 0) return;

  toggleButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const inputGroup = this.closest(".input-group");
      if (!inputGroup) return; // Sécurité supplémentaire

      const input = inputGroup.querySelector("input");
      const icon = this.querySelector("i");

      if (input && icon) {
        if (input.type === "password") {
          input.type = "text";
          icon.classList.replace("bi-eye", "bi-eye-slash");
        } else {
          input.type = "password";
          icon.classList.replace("bi-eye-slash", "bi-eye");
        }
      }
    });
  });
});
