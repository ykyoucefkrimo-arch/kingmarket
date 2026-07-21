-- ============================================================
-- Migration : ajout des frais de livraison par wilaya
-- À exécuter UNE SEULE FOIS sur une base déjà en place (locale ET
-- production Hostinger), via phpMyAdmin ou : mysql -u user -p base < ce_fichier.sql
-- ============================================================

SET NAMES utf8mb4;

-- Nouvelle table de reference des frais par wilaya (prix en DZD)
CREATE TABLE IF NOT EXISTS `frais_livraison` (
    `wilaya`            VARCHAR(100) NOT NULL,
    `prix`              INT UNSIGNED NOT NULL DEFAULT 0,
    `date_modification` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`wilaya`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ajoute la colonne uniquement si elle n'existe pas deja (evite une erreur
-- si la migration est relancee par erreur).
SET @colonne_existe = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'commandes'
      AND COLUMN_NAME = 'frais_livraison'
);

SET @sql = IF(
    @colonne_existe = 0,
    'ALTER TABLE `commandes` ADD COLUMN `frais_livraison` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `quantite`',
    'SELECT "Colonne frais_livraison deja presente, rien a faire."'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
