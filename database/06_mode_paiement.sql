-- ============================================================
-- Vite & Gourmand - Ajout du mode de paiement
-- ============================================================

USE vite_gourmand;

ALTER TABLE commande
  ADD COLUMN mode_paiement ENUM('carte', 'paypal', 'virement') DEFAULT NULL AFTER commentaires;
