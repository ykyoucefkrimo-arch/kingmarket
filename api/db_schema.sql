-- ============================================================
-- Smart Ink Case — Schéma de base de données MySQL
-- À importer via phpMyAdmin (hPanel Hostinger > Bases de données)
-- ============================================================

SET NAMES utf8mb4;

-- ── Table des commandes ─────────────────────────────────────
CREATE TABLE IF NOT EXISTS `commandes` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nom`           VARCHAR(100)  NOT NULL,
    `prenom`        VARCHAR(100)  NOT NULL,
    `telephone`     VARCHAR(20)   NOT NULL,
    `wilaya`        VARCHAR(100)  NOT NULL,
    `commune`       VARCHAR(150)  NOT NULL,
    `quantite`      TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `statut`        ENUM('Nouvelle','Confirmée','En livraison','Livrée','Annulée')
                    NOT NULL DEFAULT 'Nouvelle',
    `date_creation` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `ip_client`     VARCHAR(45)   NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_statut` (`statut`),
    INDEX `idx_wilaya` (`wilaya`),
    INDEX `idx_date_creation` (`date_creation`),
    INDEX `idx_ip_client` (`ip_client`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Table des comptes admin ──────────────────────────────────
CREATE TABLE IF NOT EXISTS `admins` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username`      VARCHAR(60)  NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `date_creation` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Aucun compte admin n'est créé ici : utilisez admin/setup.php une seule
-- fois après la mise en ligne pour créer le premier compte (voir README.md).
