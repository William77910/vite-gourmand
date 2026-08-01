-- ============================================================
-- Vite & Gourmand - Correction des images de menus
-- A executer apres 02_insertion_donnees.sql
-- Remplace les chemins d'image factices par les vraies photos,
-- et ajoute une image pour les menus 2 et 3 (qui n'en avaient pas)
-- ============================================================

USE vite_gourmand;

DELETE FROM image_menu;

INSERT INTO image_menu (id_menu, url_image, ordre_affichage) VALUES
(1, '/images/menus/mediterraneen.jpg', 1),
(2, '/images/menus/vegetarien.jpg', 1),
(3, '/images/menus/gourmet.jpg', 1);
