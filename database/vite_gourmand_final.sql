-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : mysql
-- Généré le : ven. 21 août 2026 à 12:31
-- Version du serveur : 8.0.45
-- Version de PHP : 8.3.32

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `vite_gourmand`
--
CREATE DATABASE IF NOT EXISTS `vite_gourmand` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `vite_gourmand`;

-- --------------------------------------------------------

--
-- Structure de la table `allergene`
--

DROP TABLE IF EXISTS `allergene`;
CREATE TABLE IF NOT EXISTS `allergene` (
  `id_allergene` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  PRIMARY KEY (`id_allergene`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `allergene`
--

INSERT INTO `allergene` (`id_allergene`, `nom`) VALUES
(6, 'Arachide'),
(9, 'Céleri'),
(5, 'Crustacés'),
(3, 'Fruits à coque'),
(12, 'Fruits rouges'),
(1, 'Gluten'),
(2, 'Lactose'),
(17, 'Lupin'),
(11, 'Mollusques'),
(15, 'Moutarde'),
(4, 'Oeuf'),
(14, 'Poisson'),
(16, 'Sésame'),
(13, 'Soja'),
(18, 'Sulfites'),
(10, 'Traces possible de fruits à coque');

-- --------------------------------------------------------

--
-- Structure de la table `avis`
--

DROP TABLE IF EXISTS `avis`;
CREATE TABLE IF NOT EXISTS `avis` (
  `id_avis` int NOT NULL AUTO_INCREMENT,
  `id_commande` int NOT NULL,
  `note` tinyint NOT NULL,
  `commentaire` text,
  `valide` tinyint(1) NOT NULL DEFAULT '0',
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_avis`),
  UNIQUE KEY `id_commande` (`id_commande`)
) ;

--
-- Déchargement des données de la table `avis`
--

INSERT INTO `avis` (`id_avis`, `id_commande`, `note`, `commentaire`, `valide`, `date_creation`) VALUES
(1, 1, 5, 'Tout le monde s\'ait régalé. Super présentation', 1, '2026-08-21 10:38:08'),
(2, 2, 4, 'Tout était super.\r\nMerci à toute l\'équipe!', 1, '2026-08-21 11:15:05'),
(3, 3, 5, 'Livraison ponctuelle, menu excellent et bien présenté', 1, '2026-08-21 12:06:24');

-- --------------------------------------------------------

--
-- Structure de la table `commande`
--

DROP TABLE IF EXISTS `commande`;
CREATE TABLE IF NOT EXISTS `commande` (
  `id_commande` int NOT NULL AUTO_INCREMENT,
  `id_utilisateur` int NOT NULL,
  `id_menu` int NOT NULL,
  `nb_personnes` int NOT NULL,
  `date_prestation` date NOT NULL,
  `heure_prestation` time NOT NULL,
  `adresse_livraison` varchar(255) NOT NULL,
  `ville_livraison` varchar(100) NOT NULL,
  `code_postal_livraison` varchar(10) DEFAULT NULL,
  `frais_livraison` decimal(6,2) NOT NULL DEFAULT '0.00',
  `reduction_appliquee` tinyint(1) NOT NULL DEFAULT '0',
  `prix_total` decimal(8,2) NOT NULL,
  `materiel_prete` tinyint(1) NOT NULL DEFAULT '0',
  `commentaires` text,
  `mode_paiement` enum('carte','paypal','virement') DEFAULT NULL,
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_commande`),
  KEY `fk_commande_utilisateur` (`id_utilisateur`),
  KEY `fk_commande_menu` (`id_menu`)
) ;

--
-- Déchargement des données de la table `commande`
--

INSERT INTO `commande` (`id_commande`, `id_utilisateur`, `id_menu`, `nb_personnes`, `date_prestation`, `heure_prestation`, `adresse_livraison`, `ville_livraison`, `code_postal_livraison`, `frais_livraison`, `reduction_appliquee`, `prix_total`, `materiel_prete`, `commentaires`, `mode_paiement`, `date_creation`) VALUES
(1, 11, 1, 4, '2026-08-24', '12:30:00', '5, rue du centre', 'Bordeaux', '33000', 5.00, 0, 105.00, 0, 'Sonner à l\'interphone du portail', 'carte', '2026-08-21 09:41:17'),
(2, 12, 14, 4, '2026-08-24', '13:30:00', '3, rue de la ville', 'Libourne', '33500', 21.22, 0, 81.22, 0, 'Dernière maison au fond de la court', 'paypal', '2026-08-21 10:47:23'),
(3, 13, 3, 6, '2026-08-24', '21:00:00', '10, rue du château', 'La Couronne', '16400', 64.55, 0, 244.55, 0, NULL, 'virement', '2026-08-21 12:02:18');

-- --------------------------------------------------------

--
-- Structure de la table `historique_statut`
--

DROP TABLE IF EXISTS `historique_statut`;
CREATE TABLE IF NOT EXISTS `historique_statut` (
  `id_historique` int NOT NULL AUTO_INCREMENT,
  `id_commande` int NOT NULL,
  `id_employe` int DEFAULT NULL,
  `statut` enum('en_attente','accepte','en_preparation','en_cours_de_livraison','livre','en_attente_retour_materiel','terminee','annulee') NOT NULL,
  `motif` text,
  `mode_contact` enum('appel','mail') DEFAULT NULL,
  `date_modification` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_historique`),
  KEY `fk_historique_employe` (`id_employe`),
  KEY `idx_commande_statut_join` (`id_commande`,`date_modification`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `historique_statut`
--

INSERT INTO `historique_statut` (`id_historique`, `id_commande`, `id_employe`, `statut`, `motif`, `mode_contact`, `date_modification`) VALUES
(1, 1, NULL, 'en_attente', NULL, NULL, '2026-08-21 09:41:17'),
(2, 1, 5, 'accepte', NULL, NULL, '2026-08-21 09:48:17'),
(3, 1, 5, 'en_preparation', NULL, NULL, '2026-08-21 09:48:27'),
(4, 1, 5, 'en_cours_de_livraison', NULL, NULL, '2026-08-21 09:48:32'),
(5, 1, 5, 'livre', NULL, NULL, '2026-08-21 09:48:40'),
(6, 1, 5, 'terminee', NULL, NULL, '2026-08-21 09:48:49'),
(7, 2, NULL, 'en_attente', NULL, NULL, '2026-08-21 10:47:23'),
(8, 2, 5, 'accepte', NULL, NULL, '2026-08-21 11:10:58'),
(9, 2, 5, 'en_preparation', NULL, NULL, '2026-08-21 11:12:39'),
(10, 2, 5, 'en_cours_de_livraison', NULL, NULL, '2026-08-21 11:12:42'),
(11, 2, 5, 'livre', NULL, NULL, '2026-08-21 11:12:50'),
(12, 2, 5, 'terminee', NULL, NULL, '2026-08-21 11:12:56'),
(13, 3, NULL, 'en_attente', NULL, NULL, '2026-08-21 12:02:18'),
(14, 3, 5, 'accepte', NULL, NULL, '2026-08-21 12:04:09'),
(15, 3, 5, 'en_preparation', NULL, NULL, '2026-08-21 12:04:12'),
(16, 3, 5, 'en_cours_de_livraison', NULL, NULL, '2026-08-21 12:04:16'),
(17, 3, 5, 'livre', NULL, NULL, '2026-08-21 12:04:20'),
(18, 3, 5, 'terminee', NULL, NULL, '2026-08-21 12:04:25');

-- --------------------------------------------------------

--
-- Structure de la table `horaire`
--

DROP TABLE IF EXISTS `horaire`;
CREATE TABLE IF NOT EXISTS `horaire` (
  `id_horaire` int NOT NULL AUTO_INCREMENT,
  `jour_semaine` enum('lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche') NOT NULL,
  `service` enum('midi','soir') NOT NULL,
  `heure_ouverture` time DEFAULT NULL,
  `heure_fermeture` time DEFAULT NULL,
  `ouvert` tinyint(1) NOT NULL DEFAULT '1',
  `commentaire_fermeture` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_horaire`),
  UNIQUE KEY `uq_jour_service` (`jour_semaine`,`service`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `horaire`
--

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

--
-- Structure de la table `image`
--

DROP TABLE IF EXISTS `image`;
CREATE TABLE IF NOT EXISTS `image` (
  `id_image` int NOT NULL AUTO_INCREMENT,
  `url_image` varchar(255) NOT NULL,
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_image`),
  UNIQUE KEY `uq_url_image` (`url_image`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `image`
--

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
(37, '/images/menus/34c6a737ac2bb7a0e58a78fba232f59cdfee23f305b08bb4fa8fa3213e51f506.png', '2026-08-12 13:37:50'),
(38, '/images/menus/992e79a356e7cb79570fc69c37dfb6d29d65feeae9fad42f5edfe4786ba41b85.jpg', '2026-08-12 14:00:24'),
(39, '/images/menus/3d4028a43e45232adbe301b3a7cde7299f6bf3b38b4f3ba90ae3e15d29c54965.jpg', '2026-08-12 14:19:52'),
(40, '/images/menus/7ced7295501de977fd0f67a7c11f998a7a698d3ee8b685b4748451d1c72bc62f.jpg', '2026-08-12 14:21:42'),
(41, '/images/menus/46ccb450f3475cdd9bbc90861e7a7da35cf020940282e76d03f35b841e8b9ecf.jpg', '2026-08-12 14:24:35'),
(42, '/images/menus/e3db5b1aaf2e057e2b7016393b84e566d1c2678dd4dfc0315704aa1649b93c71.jpg', '2026-08-12 14:27:06'),
(43, '/images/menus/c8524931430fc037534da4b208e1517494f80541d3c8b4489fffeee4e14dbcf8.jpg', '2026-08-13 09:51:16'),
(44, '/images/menus/4d9aa8dede8dd5f3393389bfb0157c9f47506b6b5001f77140e77dc4026b5429.png', '2026-08-13 09:51:40'),
(45, '/images/menus/bdc72355163b2ace1db1b09636cc34f2144a0e477492326ab397c97871ee6d93.jpg', '2026-08-13 09:51:55');

-- --------------------------------------------------------

--
-- Structure de la table `image_menu`
--

DROP TABLE IF EXISTS `image_menu`;
CREATE TABLE IF NOT EXISTS `image_menu` (
  `id_image_menu` int NOT NULL AUTO_INCREMENT,
  `id_menu` int NOT NULL,
  `id_image` int NOT NULL,
  `ordre_affichage` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_image_menu`),
  KEY `id_menu` (`id_menu`),
  KEY `id_image` (`id_image`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `image_menu`
--

INSERT INTO `image_menu` (`id_image_menu`, `id_menu`, `id_image`, `ordre_affichage`) VALUES
(4, 3, 39, 0),
(5, 14, 40, 0),
(6, 1, 41, 0),
(7, 2, 42, 0);

-- --------------------------------------------------------

--
-- Structure de la table `menu`
--

DROP TABLE IF EXISTS `menu`;
CREATE TABLE IF NOT EXISTS `menu` (
  `id_menu` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(150) NOT NULL,
  `description` text,
  `theme` enum('noel','paques','classique','evenement') NOT NULL,
  `regime` enum('classique','vegetarien','vegan') NOT NULL DEFAULT 'classique',
  `nb_personnes_min` int NOT NULL,
  `prix_personne` decimal(8,2) NOT NULL,
  `conditions_menu` text,
  `stock_disponible` int NOT NULL DEFAULT '0',
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `est_actif` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_menu`),
  KEY `idx_menu_theme` (`theme`),
  KEY `idx_menu_regime` (`regime`),
  KEY `idx_menu_prix` (`prix_personne`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `menu`
--

INSERT INTO `menu` (`id_menu`, `titre`, `description`, `theme`, `regime`, `nb_personnes_min`, `prix_personne`, `conditions_menu`, `stock_disponible`, `date_creation`, `est_actif`) VALUES
(1, 'Menu Méditerranéen', 'Saveurs ensoleillées de la Méditerranée', 'classique', 'classique', 4, 25.00, 'Commande 48h à  l\'avance minimum. Conservation au réfrigérateur recommandée.', 9, '2026-07-26 11:00:48', 1),
(2, 'Menu Végétarien', 'Un menu 100% végétal et gourmand', 'classique', 'vegetarien', 4, 20.00, 'Commande 48h à l\'avance minimum.', 8, '2026-07-26 11:00:48', 1),
(3, 'Menu Gourmet', 'Une expérience gastronomique raffinée', 'evenement', 'classique', 6, 30.00, 'Commande 7 jours à l\'avance minimum. Prêt de matériel possible.', 4, '2026-07-26 11:00:48', 1),
(14, 'Menu \"Barbecue\"', 'Une grillade en famille ou entre amis', 'classique', 'classique', 4, 15.00, 'Commande 3 jours à l\'avance minimum. Prêt de matériel possible.', 9, '2026-08-09 16:21:03', 1);

-- --------------------------------------------------------

--
-- Structure de la table `menu_plat`
--

DROP TABLE IF EXISTS `menu_plat`;
CREATE TABLE IF NOT EXISTS `menu_plat` (
  `id_menu` int NOT NULL,
  `id_plat` int NOT NULL,
  PRIMARY KEY (`id_menu`,`id_plat`),
  KEY `fk_menuplat_plat` (`id_plat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `menu_plat`
--

INSERT INTO `menu_plat` (`id_menu`, `id_plat`) VALUES
(1, 1),
(1, 2),
(14, 2),
(1, 3),
(2, 4),
(2, 5),
(2, 6),
(3, 7),
(3, 8),
(3, 9),
(14, 38),
(14, 39);

-- --------------------------------------------------------

--
-- Structure de la table `message_contact`
--

DROP TABLE IF EXISTS `message_contact`;
CREATE TABLE IF NOT EXISTS `message_contact` (
  `id_contact` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `email` varchar(150) NOT NULL,
  `traite` tinyint(1) NOT NULL DEFAULT '0',
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_contact`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `plat`
--

DROP TABLE IF EXISTS `plat`;
CREATE TABLE IF NOT EXISTS `plat` (
  `id_plat` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(150) NOT NULL,
  `description` text,
  `type_plat` enum('entree','plat','dessert') NOT NULL,
  `id_image` int DEFAULT NULL,
  PRIMARY KEY (`id_plat`),
  KEY `fk_plat_image` (`id_image`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `plat`
--

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

--
-- Structure de la table `plat_allergene`
--

DROP TABLE IF EXISTS `plat_allergene`;
CREATE TABLE IF NOT EXISTS `plat_allergene` (
  `id_plat` int NOT NULL,
  `id_allergene` int NOT NULL,
  PRIMARY KEY (`id_plat`,`id_allergene`),
  KEY `fk_platallergene_allergene` (`id_allergene`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `plat_allergene`
--

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

--
-- Structure de la table `utilisateur`
--

DROP TABLE IF EXISTS `utilisateur`;
CREATE TABLE IF NOT EXISTS `utilisateur` (
  `id_utilisateur` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `adresse_postale` varchar(255) DEFAULT NULL,
  `code_postal` varchar(10) DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `role` enum('utilisateur','employe','administrateur') NOT NULL DEFAULT 'utilisateur',
  `actif` tinyint(1) NOT NULL DEFAULT '1',
  `token_reset_mdp` varchar(255) DEFAULT NULL,
  `token_reset_expire` datetime DEFAULT NULL,
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_utilisateur`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id_utilisateur`, `nom`, `prenom`, `email`, `mot_de_passe`, `telephone`, `adresse_postale`, `code_postal`, `ville`, `role`, `actif`, `token_reset_mdp`, `token_reset_expire`, `date_creation`) VALUES
(5, 'Gourmand', 'José', 'josegourmand@mail.com', '$2y$10$zDSZUIWkfSjpnARVjOLAveNczruGz.DbdctZwsdjx3Prhe7927rhC', '0605040302', '1 rue de la ville', '33000', 'Bordeaux', 'administrateur', 1, NULL, NULL, '2026-08-05 09:44:23'),
(10, 'Gourmand', 'Julie', 'julie@vitegourmand.com', '$2y$10$GTO4fVktk22bWtEKdOxoi.63XmhqP/H4p65UeKwToZRMHJQFmqDJ2', '0605040302', '1, rue de la ville', '33000', 'Bordeaux', 'employe', 1, NULL, NULL, '2026-08-19 13:17:27'),
(11, 'Doe', 'John', 'johndoe@test.com', '$2y$10$mecRkCUsJ8suARiTVuX2ZezVZdnBlEwFcTI5xZWS8x/ymLCDZQRMi', '0602030405', '5, rue du centre', '33000', 'Bordeaux', 'utilisateur', 1, NULL, NULL, '2026-08-19 13:24:38'),
(12, 'Martin', 'Lisa', 'lisamartin@test.com', '$2y$10$T0NEPI1gRi.9qDN0yk1FuekucbkzEBtjVy/QLVtPhqVM/pFK.9w.6', '0605040302', '3, rue de la ville', '33500', 'Libourne', 'utilisateur', 1, NULL, NULL, '2026-08-21 10:44:50'),
(13, 'Durant', 'Roger', 'rogerdurant@test.com', '$2y$10$0QRJaKaBW.TcfmIHxsiUp.XBghw3OGleFU0RsDLw7xqLsQseJibJi', '0604030201', '10, rue du château', '16400', 'La Couronne', 'utilisateur', 1, NULL, NULL, '2026-08-21 12:00:16');

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `avis`
--
ALTER TABLE `avis`
  ADD CONSTRAINT `fk_avis_commande` FOREIGN KEY (`id_commande`) REFERENCES `commande` (`id_commande`) ON DELETE CASCADE;

--
-- Contraintes pour la table `commande`
--
ALTER TABLE `commande`
  ADD CONSTRAINT `fk_commande_menu` FOREIGN KEY (`id_menu`) REFERENCES `menu` (`id_menu`),
  ADD CONSTRAINT `fk_commande_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`);

--
-- Contraintes pour la table `historique_statut`
--
ALTER TABLE `historique_statut`
  ADD CONSTRAINT `fk_historique_commande` FOREIGN KEY (`id_commande`) REFERENCES `commande` (`id_commande`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_historique_employe` FOREIGN KEY (`id_employe`) REFERENCES `utilisateur` (`id_utilisateur`);

--
-- Contraintes pour la table `image_menu`
--
ALTER TABLE `image_menu`
  ADD CONSTRAINT `image_menu_ibfk_1` FOREIGN KEY (`id_menu`) REFERENCES `menu` (`id_menu`) ON DELETE CASCADE,
  ADD CONSTRAINT `image_menu_ibfk_2` FOREIGN KEY (`id_image`) REFERENCES `image` (`id_image`) ON DELETE CASCADE;

--
-- Contraintes pour la table `menu_plat`
--
ALTER TABLE `menu_plat`
  ADD CONSTRAINT `fk_menuplat_menu` FOREIGN KEY (`id_menu`) REFERENCES `menu` (`id_menu`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_menuplat_plat` FOREIGN KEY (`id_plat`) REFERENCES `plat` (`id_plat`) ON DELETE CASCADE;

--
-- Contraintes pour la table `plat`
--
ALTER TABLE `plat`
  ADD CONSTRAINT `fk_plat_image` FOREIGN KEY (`id_image`) REFERENCES `image` (`id_image`) ON DELETE SET NULL;

--
-- Contraintes pour la table `plat_allergene`
--
ALTER TABLE `plat_allergene`
  ADD CONSTRAINT `fk_platallergene_allergene` FOREIGN KEY (`id_allergene`) REFERENCES `allergene` (`id_allergene`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_platallergene_plat` FOREIGN KEY (`id_plat`) REFERENCES `plat` (`id_plat`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
