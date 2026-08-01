-- ============================================================
-- Vite & Gourmand - Ajout des photos par plat
-- A executer apres 02_insertion_donnees.sql et 04_maj_images_menus.sql
-- ============================================================

USE vite_gourmand;

ALTER TABLE plat ADD COLUMN photo VARCHAR(255) DEFAULT NULL AFTER type_plat;

-- Le jeu de données de test (02_insertion_donnees.sql) ne définissait
-- que 3 plats, tous rattachés au Menu Méditerranéen. On complète ici
-- avec les entrées/plats/desserts des 3 menus, pour correspondre aux
-- photos fournies.

UPDATE plat SET photo = '/images/menus/entree-mediterraneen.jpg' WHERE nom = 'Taboulé libanais';
UPDATE plat SET photo = '/images/menus/plat-mediterraneen.jpg' WHERE nom = 'Brochettes de poulet et couscous parfumé';
UPDATE plat SET photo = '/images/menus/dessert-mediterraneen.jpg' WHERE nom = 'Salade de fruits de saison';

-- Plats du Menu Végétarien et du Menu Gourmet (à adapter aux noms/
-- descriptions réels que vous souhaitez donner à chaque plat)
INSERT INTO plat (nom, description, type_plat, photo) VALUES
('Entrée végétarienne de saison', 'Entrée fraîche et colorée à base de légumes de saison.', 'entree', '/images/menus/entree-vegetarien.jpg'),
('Bowl de légumes rôtis', 'Un plat 100% végétal, gourmand et coloré.', 'plat', '/images/menus/plat-vegetarien.jpg'),
('Dessert végétarien', 'Un dessert léger et gourmand pour terminer le repas.', 'dessert', '/images/menus/dessert-vegetarien.jpg'),
('Entrée gastronomique', 'Une entrée raffinée pour ouvrir le repas.', 'entree', '/images/menus/entree-gourmet.jpg'),
('Filet mignon et légumes de saison', 'Un plat gastronomique soigné, dressage haut de gamme.', 'plat', '/images/menus/plat-gourmet.jpg'),
('Dessert gastronomique', 'Un dessert raffiné en accord avec le menu gourmet.', 'dessert', '/images/menus/dessert-gourmet.jpg');

-- Association des nouveaux plats à leurs menus respectifs
-- (id_menu 2 = Menu Vegetarien, id_menu 3 = Menu Gourmet)
INSERT INTO menu_plat (id_menu, id_plat)
SELECT 2, id_plat FROM plat WHERE photo IN (
    '/images/menus/entree-vegetarien.jpg',
    '/images/menus/plat-vegetarien.jpg',
    '/images/menus/dessert-vegetarien.jpg'
);

INSERT INTO menu_plat (id_menu, id_plat)
SELECT 3, id_plat FROM plat WHERE photo IN (
    '/images/menus/entree-gourmet.jpg',
    '/images/menus/plat-gourmet.jpg',
    '/images/menus/dessert-gourmet.jpg'
);
