-- ============================================================
-- Migration : ajout du point relais dans la tarification de livraison
-- À exécuter UNE SEULE FOIS sur une base déjà en place (locale ET
-- production Hostinger), via : mysql -u user -p base < ce_fichier.sql
--
-- Prérequis : api/migration_frais_livraison.sql doit déjà avoir été
-- exécutée (table frais_livraison + colonne commandes.frais_livraison).
-- ============================================================

SET NAMES utf8mb4;

-- ── 1. Renomme frais_livraison.prix -> prix_domicile (si pas déjà fait) ──
SET @colonne_prix_existe = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'frais_livraison'
      AND COLUMN_NAME = 'prix'
);

SET @sql = IF(
    @colonne_prix_existe > 0,
    'ALTER TABLE `frais_livraison` CHANGE COLUMN `prix` `prix_domicile` INT UNSIGNED NOT NULL DEFAULT 0',
    'SELECT "Colonne prix deja renommee ou absente, rien a faire."'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ── 2. Ajoute la colonne prix_point_relais (si absente) ──────────────────
SET @colonne_relais_existe = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'frais_livraison'
      AND COLUMN_NAME = 'prix_point_relais'
);

SET @sql = IF(
    @colonne_relais_existe = 0,
    'ALTER TABLE `frais_livraison` ADD COLUMN `prix_point_relais` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `prix_domicile`',
    'SELECT "Colonne prix_point_relais deja presente, rien a faire."'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ── 3. Ajoute commandes.type_livraison (si absente) ──────────────────────
SET @colonne_type_existe = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'commandes'
      AND COLUMN_NAME = 'type_livraison'
);

SET @sql = IF(
    @colonne_type_existe = 0,
    "ALTER TABLE `commandes` ADD COLUMN `type_livraison` ENUM('domicile','point_relais') NOT NULL DEFAULT 'domicile' AFTER `frais_livraison`",
    'SELECT "Colonne type_livraison deja presente, rien a faire."'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
