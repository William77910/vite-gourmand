
USE vite_gourmand;


-- --------------------------------------------------------
-- 1. UTILISATEURS
-- --------------------------------------------------------
INSERT INTO `utilisateur` (`id_utilisateur`, `nom`, `prenom`, `email`, `mot_de_passe`, `telephone`, `adresse_postale`, `code_postal`, `ville`, `role`, `actif`, `date_creation`) VALUES
(5, 'Gourmand', 'José', 'josegourmand@mail.com', '$2y$10$gazAvdU3J4GDzlEX36P7ROEpHWxIXP.HBjI9Q8kkjXedB/bFA4N02', '0605040302', '1 rue de la ville', '33000', 'Bordeaux', 'administrateur', 1, '2026-08-05 09:44:23'),
(10, 'Gourmand', 'Julie', 'julie@vitegourmand.com', '$2y$10$GTO4fVktk22bWtEKdOxoi.63XmhqP/H4p65UeKwToZRMHJQFmqDJ2', '0605040302', '1, rue de la ville', '33000', 'Bordeaux', 'employe', 1, '2026-08-19 13:17:27'),
(11, 'Doe', 'John', 'johndoe@test.com', '$2y$10$mecRkCUsJ8suARiTVuX2ZezVZdnBlEwFcTI5xZWS8x/ymLCDZQRMi', '0602030405', '5, rue du centre', '33000', 'Bordeaux', 'utilisateur', 1, '2026-08-19 13:24:38'),
(12, 'Martin', 'Lisa', 'lisamartin@test.com', '$2y$10$T0NEPI1gRi.9qDN0yk1FuekucbkzEBtjVy/QLVtPhqVM/pFK.9w.6', '0605040302', '3, rue de la ville', '33500', 'Libourne', 'utilisateur', 1, '2026-08-21 10:44:50'),
(13, 'Durant', 'Roger', 'rogerdurant@test.com', '$2y$10$0QRJaKaBW.TcfmIHxsiUp.XBghw3OGleFU0RsDLw7xqLsQseJibJi', '0604030201', '10, rue du château', '16400', 'La Couronne', 'utilisateur', 1, '2026-08-21 12:00:16');

-- --------------------------------------------------------
-- 2. ALLERGENES
-- --------------------------------------------------------
INSERT INTO `allergene` (`id_allergene`, `nom`) VALUES
(6, 'Arachide'),(9, 'Céleri'),(5, 'Crustacés'),(3, 'Fruits à coque'),(12, 'Fruits rouges'),
(1, 'Gluten'),(2, 'Lactose'),(17, 'Lupin'),(11, 'Mollusques'),(15, 'Moutarde'),
(4, 'Oeuf'),(14, 'Poisson'),(16, 'Sésame'),(13, 'Soja'),(18, 'Sulfites'),
(10, 'Traces possible de fruits à coque');

-- --------------------------------------------------------
-- 3. IMAGES
-- --------------------------------------------------------
INSERT INTO `image` (`id_image`, `url_image`, `date_creation`) VALUES
(1, '/images/menus/plat-mediterraneen.jpg', '2026-08-11 09:36:44'),
(2, '/images/menus/entree-mediterraneen.jpg', '2026-08-11 09:36:44'),
(3, '/images/menus/dessert-mediterraneen.jpg', '2026-08-11 09:36:44'),
(4, '/images/menus/entree-vegetarien.jpg', '2026-08-11 09:36:44'),
(5, '/images/menus/plat-vegetarien.jpg', '2026-08-11 09:36:44'),
(6, '/images/menus/dessert-vegetarien.jpg', '2026-08-11 09:36:44'),
(7, '/images/menus/entree-gourmet.jpg', '2026-08-11 09:36:44'),
(8, '/images/menus/plat-gourmet.jpg', '2026-08-11 09:36:44'),
(9, '/images/menus/dessert-gourmet.jpg', '2026-08-11 09:36:44'),
(37, '/images/menus/34c6a737ac2bb7a0e58a78fba232f59cdfee23f305b08bb4fa8fa3213e51f506.png', NOW()),
(38, '/images/menus/992e79a356e7cb79570fc69c37dfb6d29d65feeae9fad42f5edfe4786ba41b85.jpg', NOW()),
(39, '/images/menus/3d4028a43e45232adbe301b3a7cde7299f6bf3b38b4f3ba90ae3e15d29c54965.jpg', NOW()),
(40, '/images/menus/7ced7295501de977fd0f67a7c11f998a7a698d3ee8b685b4748451d1c72bc62f.jpg', NOW()),
(41, '/images/menus/46ccb450f3475cdd9bbc90861e7a7da35cf020940282e76d03f35b841e8b9ecf.jpg', NOW()),
(42, '/images/menus/e3db5b1aaf2e057e2b7016393b84e566d1c2678dd4dfc0315704aa1649b93c71.jpg', NOW()),
(43, '/images/menus/c8524931430fc037534da4b208e1517494f80541d3c8b4489fffeee4e14dbcf8.jpg', NOW()),
(44, '/images/menus/4d9aa8dede8dd5f3393389bfb0157c9f47506b6b5001f77140e77dc4026b5429.png', NOW()),
(45, '/images/menus/bdc72355163b2ace1db1b09636cc34f2144a0e477492326ab397c97871ee6d93.jpg', NOW());

-- --------------------------------------------------------
-- 4. PLATS
-- --------------------------------------------------------
INSERT INTO `plat` (`id_plat`, `nom`, `description`, `type_plat`, `id_image`) VALUES
(1, 'Brochettes de poulet et couscous parfumé', 'Un plat principal chaleureux et parfumé. Les brochettes de poulet sont préalablement marinées dans un mélange de jus de citron, d\'huile d\'olive, d\'ail et d\'épices méditerranéennes, puis grillées pour une texture tendre et juteuse. Elles sont servies sur un lit de couscous léger, subtilement aromatisé aux herbes fraîches et aux épices douces.', 'plat', 1),
(2, 'Salade Grecque aux Légumes Frais', 'Une entrée classique, fraîche et croquante, qui met à l\'honneur les saveurs ensoleillées de la Méditerranée. Cette salade associe des tomates bien mûres, des concombres croquants, des poivrons verts et des oignons rouges finement émincés, le tout agrémenté d\'olives Kalamata et de généreux blocs de fromage feta.', 'entree', 2),
(3, 'Baklava au Miel et aux Noix', 'Pour finir sur une note douce et gourmande, ce dessert traditionnel est composé de multiples couches de pâte filo croustillante, badigeonnées de beurre et garnies d\'un mélange généreux de noix et de cannelle concassées. Après cuisson, le baklava est arrosé d\'un sirop chaud au miel parfumé qui lui donne tout son moelleux.', 'dessert', 3),
(4, 'Velouté de Potimarron aux Graines Torréfiées', 'Entrée fraîche et colorée à base de légumes de saison.', 'entree', 4),
(5, 'Risotto Crémeux aux Champignons Sauvages et Parmesan Veggie', 'Un plat principal élégant et fondant. Le riz Carnaroli est lentement cuisiné avec un bouillon de légumes et une sélection de champignons sauvages, fini avec une alternative végétale au parmesan pour une onctuosité parfaite.', 'plat', 5),
(6, 'Tartelette Fine aux Pommes et Frangipane Amande', 'Pour clore ce repas, une tartelette croustillante alliant la douceur des pommes caramélisées à la richesse d\'une frangipane onctueuse à l\'amande.', 'dessert', 6),
(7, 'Noix de Saint-Jacques Snackées, Crème de Chou-Fleur Truffée', 'Une entrée raffinée où la douceur des Saint-Jacques poêlées rencontre l\'onctuosité d\'une mousseline de chou-fleur, rehaussée par la puissance de la truffe noire et une tuile de parmesan croustillante.', 'entree', 7),
(8, 'Filet de Bœuf Rossini Revisité', 'Notre interprétation du classique : un cœur de filet de bœuf français, surmonté d\'une tranche de foie gras de canard poêlée « minute ». Le tout repose sur un rösti de pomme de terre croustillant, nappé d\'une sauce corsée au Madère et truffe noire.', 'plat', 8),
(9, 'Sphère Chocolat Noir & Cœur Framboise Intense', 'Un dessert spectacle : une fine sphère de chocolat noir Valrhona abrite un biscuit Sacher moelleux et un crémeux à la framboise. Au moment du service, un coulis chaud de framboise est versé pour faire fondre la coque et révéler le cœur fondant.', 'dessert', 9),
(38, 'Grillade', '', 'plat', 45),
(39, 'Café gournand', 'Café gourmand avec 3 mini pâtisseries et café fait maison', 'dessert', 43);

-- --------------------------------------------------------
-- 5. MENUS
-- --------------------------------------------------------
INSERT INTO `menu` (`id_menu`, `titre`, `description`, `theme`, `regime`, `nb_personnes_min`, `prix_personne`, `stock_disponible`) VALUES
(1, 'Menu Méditerranéen', 'Saveurs ensoleillées', 'classique', 'classique', 4, 25.00, 7),
(2, 'Menu Végétarien', 'Un menu 100% végétal', 'classique', 'vegetarien', 4, 20.00, 8),
(3, 'Menu Gourmet', 'Expérience gastronomique', 'evenement', 'classique', 6, 30.00, 9),
(14, 'Menu Barbecue', 'Grillade en famille', 'classique', 'classique', 4, 15.00, 5);

-- --------------------------------------------------------
-- 6. LIAISONS
-- --------------------------------------------------------
INSERT INTO `image_menu` (`id_menu`, `id_image`, `ordre_affichage`) VALUES (3, 39, 0), (14, 40, 0), (1, 41, 0), (2, 42, 0);
INSERT INTO `menu_plat` (`id_menu`, `id_plat`) VALUES (1,1),(1,2),(1,3),(2,4),(2,5),(2,6),(14,2),(14,38),(14,39);
INSERT INTO `plat_allergene` (`id_plat`, `id_allergene`) VALUES
(1, 1),
(3, 1),
(8, 1),
(2, 2),
(3, 2),
(7, 2),
(3, 3),
(5, 3),
(8, 6),
(4, 9),
(5, 9),
(4, 10),
(7, 11),
(9, 12),
(39, 12);
-- --------------------------------------------------------
-- 7. COMMANDES
-- --------------------------------------------------------
INSERT INTO `commande` (`id_commande`, `id_utilisateur`, `id_menu`, `nb_personnes`, `date_prestation`, `heure_prestation`, `adresse_livraison`, `ville_livraison`, `code_postal_livraison`, `frais_livraison`, `reduction_appliquee`, `prix_total`, `materiel_prete`, `commentaires`, `mode_paiement`, `date_creation`) VALUES
(1, 11, 14, 9, '2026-06-06', '12:00:00', '5, rue du centre', 'Bordeaux', '33000', 5.00, 1, 126.50, 0, NULL, 'carte', '2026-06-01 11:20:42'),
(2, 13, 14, 10, '2026-06-20', '12:30:00', '10, rue du château', 'La Couronne', '16400', 64.55, 1, 199.55, 0, NULL, 'paypal', '2026-06-09 11:26:00'),
(3, 12, 3, 6, '2026-07-11', '21:30:00', '3, rue de la ville', 'Libourne', '33500', 21.22, 0, 201.22, 0, NULL, 'virement', '2026-07-06 11:31:59'),
(4, 11, 1, 4, '2026-07-18', '12:30:00', '5, rue du centre', 'Bordeaux', '33000', 5.00, 0, 105.00, 0, NULL, 'virement', '2026-07-07 11:35:34'),
(5, 11, 2, 5, '2026-07-25', '12:30:00', '5, rue du centre', 'Bordeaux', '33000', 5.00, 0, 105.00, 0, NULL, 'carte', '2026-07-21 11:38:47'),
(6, 13, 2, 7, '2026-08-07', '13:00:00', '10, rue du château', 'La Couronne', '16400', 64.55, 0, 204.55, 0, NULL, 'paypal', '2026-08-01 11:42:35'),
(7, 12, 14, 10, '2026-09-01', '21:30:00', '3, rue de la ville', 'Libourne', '33500', 21.22, 1, 156.22, 0, NULL, 'carte', '2026-08-28 11:46:48'),
(8, 12, 1, 4, '2026-09-03', '13:00:00', '3, rue de la ville', 'Libourne', '33500', 21.22, 0, 121.22, 0, NULL, 'carte', '2026-08-31 11:47:58'),
(9, 13, 3, 6, '2026-09-12', '21:30:00', '10, rue du château', 'La Couronne', '16400', 64.55, 0, 244.55, 0, NULL, 'paypal', '2026-09-01 11:50:36'),
(10, 11, 14, 4, '2026-09-15', '13:54:00', '5, rue du centre', 'Bordeaux', '33000', 5.00, 0, 65.00, 0, NULL, 'carte', '2026-09-01 11:54:53'),
(11, 12, 14, 4, '2026-09-15', '13:00:00', '3, rue de la ville', 'Libourne', '33500', 21.22, 0, 81.22, 0, NULL, 'carte', '2026-09-01 11:56:31'),
(12, 13, 1, 4, '2026-09-17', '13:01:00', '10, rue du château', 'La Couronne', '16400', 64.55, 0, 164.55, 0, NULL, 'carte', '2026-09-01 12:55:59');
-- --------------------------------------------------------
-- 8. AVIS
-- --------------------------------------------------------
INSERT INTO `avis` (`id_avis`, `id_commande`, `note`, `commentaire`, `valide`, `date_creation`) VALUES
(1, 1, 5, 'Très content de la prestation et du menu', 1, '2026-09-03 14:05:17'),
(2, 2, 4, 'Menu très agréable et bien présenté', 1, '2026-09-03 14:07:19'),
(3, 6, 4, 'Enchanté par ce menu avec des produits frais', 1, '2026-09-03 14:08:23'),
(4, 4, 4, 'Tout le monde s''est régalé !', 0, '2026-09-03 14:32:59');

-- --------------------------------------------------------
-- 9. HISTORIQUE DES STATUTS
-- --------------------------------------------------------
INSERT INTO `historique_statut` (`id_historique`, `id_commande`, `id_employe`, `statut`, `motif`, `mode_contact`, `date_modification`) VALUES
(1, 1, NULL, 'en_attente', NULL, NULL, '2026-09-01 11:20:42'),
(2, 2, NULL, 'en_attente', NULL, NULL, '2026-09-01 11:26:00'),
(3, 3, NULL, 'en_attente', NULL, NULL, '2026-09-01 11:31:59'),
(4, 4, NULL, 'en_attente', NULL, NULL, '2026-09-01 11:35:34'),
(5, 5, NULL, 'en_attente', NULL, NULL, '2026-09-01 11:38:47'),
(6, 6, NULL, 'en_attente', NULL, NULL, '2026-09-01 11:42:35'),
(7, 7, NULL, 'en_attente', NULL, NULL, '2026-09-01 11:46:48'),
(8, 8, NULL, 'en_attente', NULL, NULL, '2026-09-01 11:47:58'),
(9, 9, NULL, 'en_attente', NULL, NULL, '2026-09-01 11:50:36'),
(10, 10, NULL, 'en_attente', NULL, NULL, '2026-09-01 11:54:53'),
(11, 11, NULL, 'en_attente', NULL, NULL, '2026-09-01 11:56:31'),
(12, 12, NULL, 'en_attente', NULL, NULL, '2026-09-01 12:55:59'),
(13, 1, 5, 'accepte', NULL, NULL, '2026-09-01 13:34:48'),
(14, 2, 5, 'accepte', NULL, NULL, '2026-09-01 13:35:06'),
(15, 3, 5, 'accepte', NULL, NULL, '2026-09-01 13:36:53'),
(16, 4, 5, 'accepte', NULL, NULL, '2026-09-01 14:06:43'),
(17, 1, 5, 'en_preparation', NULL, NULL, '2026-09-01 14:10:30'),
(18, 6, 5, 'accepte', NULL, NULL, '2026-09-01 14:14:19'),
(19, 3, 5, 'annulee', 'Client injoignable', 'appel', '2026-09-01 14:16:30'),
(20, 7, 5, 'accepte', NULL, NULL, '2026-09-01 14:18:07'),
(21, 2, 5, 'en_preparation', NULL, NULL, '2026-09-01 14:18:20'),
(22, 8, 5, 'accepte', NULL, NULL, '2026-09-01 14:20:12'),
(23, 9, 5, 'accepte', NULL, NULL, '2026-09-01 14:21:20'),
(24, 7, 5, 'en_preparation', NULL, NULL, '2026-09-01 17:31:43'),
(25, 1, 5, 'en_cours_de_livraison', NULL, NULL, '2026-09-01 17:38:00'),
(26, 1, 5, 'livre', NULL, NULL, '2026-09-01 17:38:12'),
(27, 1, 5, 'terminee', NULL, NULL, '2026-09-01 17:38:23'),
(28, 2, 5, 'en_cours_de_livraison', NULL, NULL, '2026-09-01 17:38:55'),
(29, 2, 5, 'livre', NULL, NULL, '2026-09-01 17:39:00'),
(30, 2, 5, 'terminee', NULL, NULL, '2026-09-01 17:39:08'),
(31, 4, 5, 'en_preparation', NULL, NULL, '2026-09-01 17:39:37'),
(32, 4, 5, 'en_cours_de_livraison', NULL, NULL, '2026-09-01 17:39:47'),
(33, 4, 5, 'livre', NULL, NULL, '2026-09-01 17:39:53'),
(34, 4, 5, 'terminee', NULL, NULL, '2026-09-01 17:40:01'),
(35, 5, 5, 'accepte', NULL, NULL, '2026-09-01 17:40:15'),
(36, 5, 5, 'en_preparation', NULL, NULL, '2026-09-01 17:40:19'),
(37, 5, 5, 'en_cours_de_livraison', NULL, NULL, '2026-09-01 17:40:29'),
(38, 5, 5, 'livre', NULL, NULL, '2026-09-01 17:40:38'),
(39, 5, 5, 'terminee', NULL, NULL, '2026-09-01 17:40:47'),
(40, 6, 5, 'en_preparation', NULL, NULL, '2026-09-01 17:41:06'),
(41, 6, 5, 'en_cours_de_livraison', NULL, NULL, '2026-09-01 17:41:19'),
(42, 6, 5, 'livre', NULL, NULL, '2026-09-01 17:41:28'),
(43, 6, 5, 'terminee', NULL, NULL, '2026-09-01 17:41:35'),
(44, 7, 5, 'en_cours_de_livraison', NULL, NULL, '2026-09-01 17:43:44'),
(45, 8, 5, 'en_preparation', NULL, NULL, '2026-09-01 17:46:13');

-- --------------------------------------------------------
-- 10. Horaires
-- --------------------------------------------------------

INSERT INTO `horaire` (`id_horaire`, `jour_semaine`, `service`, `heure_ouverture`, `heure_fermeture`, `ouvert`, `commentaire_fermeture`) VALUES
(1, 'lundi', 'midi', '12:00:00', '14:00:00', 1, NULL),
(2, 'lundi', 'soir', '18:00:00', '23:00:00', 1, NULL),
(3, 'mardi', 'midi', '12:00:00', '14:00:00', 1, NULL),
(4, 'mardi', 'soir', '18:00:00', '23:00:00', 1, NULL),
(5, 'mercredi', 'midi', '12:00:00', '14:00:00', 1, NULL),
(6, 'mercredi', 'soir', '18:00:00', '23:00:00', 1, NULL),
(7, 'jeudi', 'midi', '12:00:00', '14:00:00', 1, NULL),
(8, 'jeudi', 'soir', '18:00:00', '23:00:00', 1, NULL),
(9, 'vendredi', 'midi', '12:00:00', '14:00:00', 1, NULL),
(10, 'vendredi', 'soir', '18:00:00', '23:00:00', 1, NULL),
(11, 'samedi', 'midi', '12:00:00', '14:00:00', 1, NULL),
(12, 'samedi', 'soir', '18:00:00', '23:00:00', 1, NULL),
(13, 'dimanche', 'midi', NULL, NULL, 0, 'Nous contacter'),
(14, 'dimanche', 'soir', NULL, NULL, 0, 'Nous contacter');

-- --------------------------------------------------------
-- 11. Messages de contact
-- --------------------------------------------------------

INSERT INTO `message_contact` (`id_contact`, `titre`, `description`, `email`, `traite`, `date_creation`) VALUES
(1, 'Devis pour une communion', 'Bonjour, je souhaiterais un devis pour un menu de communion.\r\nCet évènement se déroulerait un dimanche.\r\nBien cordialement\r\nJohn DOE', 'johndoe@test.com', 1, '2026-09-03 14:42:03'),
(2, 'Demande de devis', 'Bonjour, je souhaiterais savoir si vous faite aussi les réception d''entreprise.\r\nVous en remerciant par avance,\r\nCordialement,\r\nJean DUPONT', 'jeandupont@test.com', 0, '2026-09-03 15:31:29');
