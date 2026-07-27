-- ============================================================
-- Vite & Gourmand - Jeu de données de test
-- À exécuter après 01_creation_tables.sql
-- ============================================================

USE vite_gourmand;

-- ------------------------------------------------------------
-- Horaires (source : mockup fourni)
-- Mardi à samedi : 12h-14h et 18h-23h / Dimanche : 12h-14h / Lundi fermé
-- ------------------------------------------------------------
INSERT INTO horaire (jour_semaine, service, heure_ouverture, heure_fermeture, ouvert) VALUES
('lundi',    'midi', NULL,      NULL,      0),
('lundi',    'soir', NULL,      NULL,      0),
('mardi',    'midi', '12:00:00','14:00:00',1),
('mardi',    'soir', '18:00:00','23:00:00',1),
('mercredi', 'midi', '12:00:00','14:00:00',1),
('mercredi', 'soir', '18:00:00','23:00:00',1),
('jeudi',    'midi', '12:00:00','14:00:00',1),
('jeudi',    'soir', '18:00:00','23:00:00',1),
('vendredi', 'midi', '12:00:00','14:00:00',1),
('vendredi', 'soir', '18:00:00','23:00:00',1),
('samedi',   'midi', '12:00:00','14:00:00',1),
('samedi',   'soir', '18:00:00','23:00:00',1),
('dimanche', 'midi', '12:00:00','14:00:00',1),
('dimanche', 'soir', NULL,      NULL,      0);

-- ------------------------------------------------------------
-- Utilisateurs
-- ATTENTION : les mots de passe ci-dessous sont des hash
-- d'exemple (bcrypt). En conditions réelles, ils sont générés
-- par password_hash() côté PHP au moment de l'inscription.
-- Mot de passe en clair pour les tests : "MotDePasse!2026"
-- ------------------------------------------------------------
INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, telephone, adresse_postale, code_postal, ville, role, actif) VALUES
('Dupont', 'Julie',  'admin@vitegourmand.com',    '$2y$10$exempledehashbcryptaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', '0504030201', '7 rue de la ville', '33000', 'Bordeaux', 'administrateur', 1),
('Martin', 'Sophie', 'employe1@vitegourmand.com', '$2y$10$exempledehashbcryptbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb', '0601020304', '12 rue des Fleurs',  '33000', 'Bordeaux', 'employe',        1),
('Roussel','Clara',  'clara.r@example.com',       '$2y$10$exempledehashbcryptccccccccccccccccccccccccccccccccccccc', '0611223344', '3 rue Sainte-Catherine','33000','Bordeaux', 'utilisateur',    1),
('Lefevre','Marc',   'marc.l@example.com',        '$2y$10$exempledehashbcryptdddddddddddddddddddddddddddddddddddd', '0622334455', '8 avenue Thiers',   '33200', 'Bordeaux', 'utilisateur',    1);

-- ------------------------------------------------------------
-- Allergènes
-- ------------------------------------------------------------
INSERT INTO allergene (nom) VALUES
('Gluten'), ('Lactose'), ('Fruits à coque'), ('Oeuf'), ('Crustacés'), ('Arachide');

-- ------------------------------------------------------------
-- Plats
-- ------------------------------------------------------------
INSERT INTO plat (nom, description, type_plat) VALUES
('Brochettes de poulet et couscous parfumé', 'Poulet mariné aux épices méditerranéennes, servi sur couscous léger', 'plat'),
('Taboulé libanais', 'Semoule fine, herbes fraîches, tomates et citron', 'entree'),
('Salade de fruits de saison', 'Fruits frais coupés, sirop léger à la menthe', 'dessert');

-- ------------------------------------------------------------
-- Association plats / allergènes
-- ------------------------------------------------------------
INSERT INTO plat_allergene (id_plat, id_allergene) VALUES
(1, 1), -- Brochettes / Gluten (semoule de blé)
(2, 1); -- Taboulé / Gluten

-- ------------------------------------------------------------
-- Menus
-- ------------------------------------------------------------
INSERT INTO menu (titre, description, theme, regime, nb_personnes_min, prix_personne, conditions_menu, stock_disponible) VALUES
('Menu Méditerranéen', 'Saveurs ensoleillées de la Méditerranée', 'classique', 'classique', 4, 25.00, 'Commande 48h à l''avance minimum. Conservation au réfrigérateur recommandée.', 10),
('Menu Végétarien',    'Un menu 100% végétal et gourmand',        'classique', 'vegetarien', 4, 20.00, 'Commande 48h à l''avance minimum.', 8),
('Menu Gourmet',       'Une expérience gastronomique raffinée',   'evenement', 'classique', 6, 30.00, 'Commande 7 jours à l''avance minimum. Prêt de matériel possible.', 5);

INSERT INTO menu_plat (id_menu, id_plat) VALUES
(1, 1), (1, 2), (1, 3);

INSERT INTO image_menu (id_menu, url_image, ordre_affichage) VALUES
(1, '/images/menus/mediterraneen-1.jpg', 1),
(1, '/images/menus/mediterraneen-2.jpg', 2);

-- ------------------------------------------------------------
-- Commande d'exemple (utilisateur Clara, menu Méditerranéen)
-- ------------------------------------------------------------
INSERT INTO commande (id_utilisateur, id_menu, nb_personnes, date_prestation, heure_prestation, adresse_livraison, ville_livraison, code_postal_livraison, frais_livraison, 
reduction_appliquee, prix_total, materiel_prete, commentaires) VALUES
(3, 1, 4, '2026-08-15', '19:00:00', '3 rue Sainte-Catherine', 'Bordeaux', '33000', 0.00, 0, 100.00, 0, 'Merci de sonner à l''interphone.');

INSERT INTO historique_statut (id_commande, id_employe, statut, date_modification) VALUES
(1, NULL, 'en_attente', '2026-07-20 10:15:00'),
(1, 2,    'accepte',    '2026-07-20 14:30:00');

-- ------------------------------------------------------------
-- Avis d'exemple
-- ------------------------------------------------------------
-- (à insérer une fois la commande passée en "terminee",
-- laissé volontairement absent ici : commande encore en cours)
