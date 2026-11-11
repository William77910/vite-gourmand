-- Création de la base de données
CREATE DATABASE IF NOT EXISTS vite_gourmand CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE vite_gourmand;

-- --------------------------------------------------------

-- --------------------------------------------------------
-- Structure de la table `utilisateur`
-- --------------------------------------------------------
CREATE TABLE `utilisateur` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------
-- Structure de la table `allergene`
-- --------------------------------------------------------
CREATE TABLE `allergene` (
  `id_allergene` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  PRIMARY KEY (`id_allergene`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- --------------------------------------------------------
-- Structure de la table `image`
-- --------------------------------------------------------
CREATE TABLE `image` (
  `id_image` int NOT NULL AUTO_INCREMENT,
  `url_image` varchar(255) NOT NULL,
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_image`),
  UNIQUE KEY `uq_url_image` (`url_image`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------
-- Structure de la table `plat`
-- --------------------------------------------------------
CREATE TABLE `plat` (
  `id_plat` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(150) NOT NULL,
  `description` text,
  `type_plat` enum('entree','plat','dessert') NOT NULL,
  `id_image` int DEFAULT NULL,
  PRIMARY KEY (`id_plat`),
  KEY `fk_plat_image` (`id_image`),
  CONSTRAINT `fk_plat_image` FOREIGN KEY (`id_image`) REFERENCES `image` (`id_image`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------
-- Structure de la table `menu`
-- --------------------------------------------------------
CREATE TABLE `menu` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------
-- Structure de la table `image_menu`
-- --------------------------------------------------------
CREATE TABLE `image_menu` (
  `id_image_menu` int NOT NULL AUTO_INCREMENT,
  `id_menu` int NOT NULL,
  `id_image` int NOT NULL,
  `ordre_affichage` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_image_menu`),
  KEY `id_menu` (`id_menu`),
  KEY `id_image` (`id_image`),
  CONSTRAINT `image_menu_ibfk_1` FOREIGN KEY (`id_menu`) REFERENCES `menu` (`id_menu`) ON DELETE CASCADE,
  CONSTRAINT `image_menu_ibfk_2` FOREIGN KEY (`id_image`) REFERENCES `image` (`id_image`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------
-- Structure de la table `menu_plat`
-- --------------------------------------------------------
CREATE TABLE `menu_plat` (
  `id_menu` int NOT NULL,
  `id_plat` int NOT NULL,
  PRIMARY KEY (`id_menu`,`id_plat`),
  KEY `fk_menuplat_plat` (`id_plat`),
  CONSTRAINT `fk_menuplat_menu` FOREIGN KEY (`id_menu`) REFERENCES `menu` (`id_menu`) ON DELETE CASCADE,
  CONSTRAINT `fk_menuplat_plat` FOREIGN KEY (`id_plat`) REFERENCES `plat` (`id_plat`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------
-- Structure de la table `plat_allergene`
-- --------------------------------------------------------
CREATE TABLE `plat_allergene` (
  `id_plat` int NOT NULL,
  `id_allergene` int NOT NULL,
  PRIMARY KEY (`id_plat`,`id_allergene`),
  KEY `fk_platallergene_allergene` (`id_allergene`),
  CONSTRAINT `fk_platallergene_allergene` FOREIGN KEY (`id_allergene`) REFERENCES `allergene` (`id_allergene`) ON DELETE CASCADE,
  CONSTRAINT `fk_platallergene_plat` FOREIGN KEY (`id_plat`) REFERENCES `plat` (`id_plat`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- --------------------------------------------------------
-- Structure de la table `commande`
-- --------------------------------------------------------
CREATE TABLE `commande` (
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
  KEY `fk_commande_menu` (`id_menu`),
  CONSTRAINT `fk_commande_menu` FOREIGN KEY (`id_menu`) REFERENCES `menu` (`id_menu`),
  CONSTRAINT `fk_commande_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Structure de la table `avis`
-- --------------------------------------------------------
CREATE TABLE `avis` (
  `id_avis` int NOT NULL AUTO_INCREMENT,
  `id_commande` int NOT NULL,
  `note` tinyint NOT NULL,
  `commentaire` text,
  `valide` tinyint(1) NOT NULL DEFAULT '0',
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_avis`),
  UNIQUE KEY `id_commande` (`id_commande`),
  CONSTRAINT `fk_avis_commande` FOREIGN KEY (`id_commande`) REFERENCES `commande` (`id_commande`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Structure de la table `historique_statut`
-- --------------------------------------------------------
CREATE TABLE `historique_statut` (
  `id_historique` int NOT NULL AUTO_INCREMENT,
  `id_commande` int NOT NULL,
  `id_employe` int DEFAULT NULL,
  `statut` enum('en_attente','accepte','en_preparation','en_cours_de_livraison','livre','en_attente_retour_materiel','terminee','annulee') NOT NULL,
  `motif` text,
  `mode_contact` enum('appel','mail') DEFAULT NULL,
  `date_modification` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_historique`),
  KEY `fk_historique_employe` (`id_employe`),
  KEY `idx_commande_statut_join` (`id_commande`,`date_modification`),
  CONSTRAINT `fk_historique_commande` FOREIGN KEY (`id_commande`) REFERENCES `commande` (`id_commande`) ON DELETE CASCADE,
  CONSTRAINT `fk_historique_employe` FOREIGN KEY (`id_employe`) REFERENCES `utilisateur` (`id_utilisateur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------
-- Structure de la table `horaire`
-- --------------------------------------------------------
CREATE TABLE `horaire` (
  `id_horaire` int NOT NULL AUTO_INCREMENT,
  `jour_semaine` enum('lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche') NOT NULL,
  `service` enum('midi','soir') NOT NULL,
  `heure_ouverture` time DEFAULT NULL,
  `heure_fermeture` time DEFAULT NULL,
  `ouvert` tinyint(1) NOT NULL DEFAULT '1',
  `commentaire_fermeture` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_horaire`),
  UNIQUE KEY `uq_jour_service` (`jour_semaine`,`service`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;



-- --------------------------------------------------------
-- Structure de la table `message_contact`
-- --------------------------------------------------------
CREATE TABLE `message_contact` (
  `id_contact` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `email` varchar(150) NOT NULL,
  `traite` tinyint(1) NOT NULL DEFAULT '0',
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_contact`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


