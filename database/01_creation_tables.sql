-- ============================================================
-- Vite & Gourmand - Script de création de la base de données
-- SGBD cible : MySQL / MariaDB
-- ============================================================

CREATE DATABASE IF NOT EXISTS vite_gourmand
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE vite_gourmand;

-- ------------------------------------------------------------
-- Table : utilisateur
-- Regroupe visiteurs inscrits, employés et l'administrateur
-- Le mot de passe est stocké HASHÉ (password_hash() côté PHP,
-- jamais en clair) : mesure de sécurité obligatoire
-- ------------------------------------------------------------
CREATE TABLE utilisateur (
  id_utilisateur    INT AUTO_INCREMENT PRIMARY KEY,
  nom               VARCHAR(100) NOT NULL,
  prenom            VARCHAR(100) NOT NULL,
  email             VARCHAR(150) NOT NULL UNIQUE,
  mot_de_passe      VARCHAR(255) NOT NULL,
  telephone         VARCHAR(20),
  adresse_postale   VARCHAR(255),
  code_postal       VARCHAR(10),
  ville             VARCHAR(100),
  role              ENUM('utilisateur', 'employe', 'administrateur') NOT NULL DEFAULT 'utilisateur',
  actif             TINYINT(1) NOT NULL DEFAULT 1,
  token_reset_mdp    VARCHAR(255) DEFAULT NULL,
  token_reset_expire DATETIME DEFAULT NULL,
  date_creation     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table : menu
-- ------------------------------------------------------------
CREATE TABLE menu (
  id_menu           INT AUTO_INCREMENT PRIMARY KEY,
  titre             VARCHAR(150) NOT NULL,
  description       TEXT,
  theme             ENUM('noel', 'paques', 'classique', 'evenement') NOT NULL,
  regime            ENUM('classique', 'vegetarien', 'vegan') NOT NULL DEFAULT 'classique',
  nb_personnes_min  INT NOT NULL,
  prix_personne     DECIMAL(8,2) NOT NULL,
  conditions_menu   TEXT,
  stock_disponible  INT NOT NULL DEFAULT 0,
  date_creation     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table : image_menu (galerie d'images d'un menu)
-- ------------------------------------------------------------
CREATE TABLE image_menu (
  id_image        INT AUTO_INCREMENT PRIMARY KEY,
  id_menu         INT NOT NULL,
  url_image       VARCHAR(255) NOT NULL,
  ordre_affichage INT NOT NULL DEFAULT 0,
  CONSTRAINT fk_image_menu FOREIGN KEY (id_menu)
    REFERENCES menu(id_menu) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table : plat
-- ------------------------------------------------------------
CREATE TABLE plat (
  id_plat     INT AUTO_INCREMENT PRIMARY KEY,
  nom         VARCHAR(150) NOT NULL,
  description TEXT,
  type_plat   ENUM('entree', 'plat', 'dessert') NOT NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table : allergene
-- ------------------------------------------------------------
CREATE TABLE allergene (
  id_allergene INT AUTO_INCREMENT PRIMARY KEY,
  nom          VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table d'association : menu_plat (many-to-many)
-- ------------------------------------------------------------
CREATE TABLE menu_plat (
  id_menu INT NOT NULL,
  id_plat INT NOT NULL,
  PRIMARY KEY (id_menu, id_plat),
  CONSTRAINT fk_menuplat_menu FOREIGN KEY (id_menu)
    REFERENCES menu(id_menu) ON DELETE CASCADE,
  CONSTRAINT fk_menuplat_plat FOREIGN KEY (id_plat)
    REFERENCES plat(id_plat) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table d'association : plat_allergene (many-to-many)
-- ------------------------------------------------------------
CREATE TABLE plat_allergene (
  id_plat      INT NOT NULL,
  id_allergene INT NOT NULL,
  PRIMARY KEY (id_plat, id_allergene),
  CONSTRAINT fk_platallergene_plat FOREIGN KEY (id_plat)
    REFERENCES plat(id_plat) ON DELETE CASCADE,
  CONSTRAINT fk_platallergene_allergene FOREIGN KEY (id_allergene)
    REFERENCES allergene(id_allergene) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table : commande
-- ------------------------------------------------------------
CREATE TABLE commande (
  id_commande           INT AUTO_INCREMENT PRIMARY KEY,
  id_utilisateur        INT NOT NULL,
  id_menu               INT NOT NULL,
  nb_personnes          INT NOT NULL,
  date_prestation       DATE NOT NULL,
  heure_prestation      TIME NOT NULL,
  adresse_livraison     VARCHAR(255) NOT NULL,
  ville_livraison       VARCHAR(100) NOT NULL,
  code_postal_livraison VARCHAR(10),
  frais_livraison       DECIMAL(6,2) NOT NULL DEFAULT 0,
  reduction_appliquee   TINYINT(1) NOT NULL DEFAULT 0,
  prix_total            DECIMAL(8,2) NOT NULL,
  materiel_prete        TINYINT(1) NOT NULL DEFAULT 0,
  commentaires          TEXT,
  date_creation         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_commande_utilisateur FOREIGN KEY (id_utilisateur)
    REFERENCES utilisateur(id_utilisateur),
  CONSTRAINT fk_commande_menu FOREIGN KEY (id_menu)
    REFERENCES menu(id_menu),
  CONSTRAINT chk_nb_personnes CHECK (nb_personnes > 0)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table : historique_statut (suivi horodaté de chaque commande)
-- ------------------------------------------------------------
CREATE TABLE historique_statut (
  id_historique     INT AUTO_INCREMENT PRIMARY KEY,
  id_commande       INT NOT NULL,
  id_employe        INT DEFAULT NULL,
  statut            ENUM(
                      'en_attente', 'accepte', 'en_preparation',
                      'en_cours_de_livraison', 'livre',
                      'en_attente_retour_materiel', 'terminee', 'annulee'
                    ) NOT NULL,
  motif             TEXT DEFAULT NULL,
  mode_contact      ENUM('appel', 'mail') DEFAULT NULL,
  date_modification DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_historique_commande FOREIGN KEY (id_commande)
    REFERENCES commande(id_commande) ON DELETE CASCADE,
  CONSTRAINT fk_historique_employe FOREIGN KEY (id_employe)
    REFERENCES utilisateur(id_utilisateur)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table : avis
-- ------------------------------------------------------------
CREATE TABLE avis (
  id_avis       INT AUTO_INCREMENT PRIMARY KEY,
  id_commande   INT NOT NULL UNIQUE,
  note          TINYINT NOT NULL,
  commentaire   TEXT,
  valide        TINYINT(1) NOT NULL DEFAULT 0,
  date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_avis_commande FOREIGN KEY (id_commande)
    REFERENCES commande(id_commande) ON DELETE CASCADE,
  CONSTRAINT chk_note CHECK (note BETWEEN 1 AND 5)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table : horaire
-- ------------------------------------------------------------
CREATE TABLE horaire (
  id_horaire      INT AUTO_INCREMENT PRIMARY KEY,
  jour_semaine    ENUM('lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche') NOT NULL,
  service         ENUM('midi', 'soir') NOT NULL,
  heure_ouverture TIME,
  heure_fermeture TIME,
  ouvert          TINYINT(1) NOT NULL DEFAULT 1,
  UNIQUE KEY uq_jour_service (jour_semaine, service)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table : message_contact
-- ------------------------------------------------------------
CREATE TABLE message_contact (
  id_contact    INT AUTO_INCREMENT PRIMARY KEY,
  titre         VARCHAR(150) NOT NULL,
  description   TEXT NOT NULL,
  email         VARCHAR(150) NOT NULL,
  traite        TINYINT(1) NOT NULL DEFAULT 0,
  date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Index complémentaires (recherche/filtres fréquents)
-- ------------------------------------------------------------
CREATE INDEX idx_menu_theme  ON menu(theme);
CREATE INDEX idx_menu_regime ON menu(regime);
CREATE INDEX idx_menu_prix   ON menu(prix_personne);
CREATE INDEX idx_commande_statut_join ON historique_statut(id_commande, date_modification);
