-- ============================================================
-- Vite & Gourmand - Refonte gestion des images et des plats
-- A executer EN UNE SEULE FOIS (tout selectionner avant "Executer")
-- ============================================================

USE vite_gourmand;

-- ------------------------------------------------------------
-- 1. Table centrale des images : une image n'existe qu'une fois
--    en base, quel que soit le nombre de plats/menus qui l'utilisent
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS image (
  id_image      INT AUTO_INCREMENT PRIMARY KEY,
  url_image     VARCHAR(255) NOT NULL,
  date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_url_image (url_image)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 2. Migration des photos de plats existantes vers la table image
-- ------------------------------------------------------------
INSERT IGNORE INTO image (url_image)
SELECT DISTINCT photo FROM plat WHERE photo IS NOT NULL AND photo <> '';

ALTER TABLE plat ADD COLUMN id_image INT DEFAULT NULL AFTER type_plat;

UPDATE plat p
JOIN image i ON i.url_image = p.photo
SET p.id_image = i.id_image
WHERE p.photo IS NOT NULL AND p.photo <> '';

ALTER TABLE plat
  ADD CONSTRAINT fk_plat_image FOREIGN KEY (id_image)
  REFERENCES image(id_image) ON DELETE SET NULL;

ALTER TABLE plat DROP COLUMN photo;

-- ------------------------------------------------------------
-- 3. Refonte de image_menu : ne contient plus que les photos
--    "d'ambiance" du menu. Les photos des plats sont deduites
--    automatiquement via menu_plat -> plat -> image (plus de
--    duplication, donc plus de desynchronisation possible).
-- ------------------------------------------------------------
DROP TABLE IF EXISTS image_menu;

CREATE TABLE image_menu (
  id_image_menu   INT AUTO_INCREMENT PRIMARY KEY,
  id_menu         INT NOT NULL,
  id_image        INT NOT NULL,
  ordre_affichage INT NOT NULL DEFAULT 0,
  FOREIGN KEY (id_menu) REFERENCES menu(id_menu) ON DELETE CASCADE,
  FOREIGN KEY (id_image) REFERENCES image(id_image) ON DELETE CASCADE
) ENGINE=InnoDB;
