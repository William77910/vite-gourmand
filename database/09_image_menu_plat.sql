-- ============================================================
-- Vite & Gourmand - Lien image_menu <-> plat
-- Permet de remplacer une photo (entree/plat/dessert) au lieu d'en
-- ajouter une nouvelle a chaque modification.
-- ============================================================

USE vite_gourmand;

ALTER TABLE image_menu ADD COLUMN id_plat INT DEFAULT NULL AFTER id_menu;

ALTER TABLE image_menu
  ADD CONSTRAINT fk_image_menu_plat FOREIGN KEY (id_plat)
  REFERENCES plat(id_plat) ON DELETE SET NULL;
