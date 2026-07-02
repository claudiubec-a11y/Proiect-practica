-- ============================================================================
-- UtilajePro — database/schema.sql
-- ----------------------------------------------------------------------------
-- Schema completă pentru marketplace-ul de utilaje agricole și de construcții.
-- Motor InnoDB (suport FOREIGN KEY + tranzacții), charset utf8mb4.
--
-- Ordinea de creare respectă dependențele FK:
--   users -> categories -> machinery_types -> listings -> listing_images
--        -> favorites -> conversations -> messages -> rentals -> admin_logs
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `utilajepro`
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `utilajepro`;

-- ----------------------------------------------------------------------------
-- Tabelul: users
-- Utilizatori și administratori. Parola este stocată cu password_hash().
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `first_name`     VARCHAR(80)  NOT NULL,
    `last_name`      VARCHAR(80)  NOT NULL,
    `email`          VARCHAR(190) NOT NULL,
    `password`       VARCHAR(255) NOT NULL COMMENT 'hash generat cu password_hash()',
    `role`           ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    `phone`          VARCHAR(20)  NULL,
    `city`           VARCHAR(100) NULL,
    `county`         VARCHAR(100) NULL,
    `profile_image`  VARCHAR(255) NULL,
    `status`         ENUM('active', 'blocked') NOT NULL DEFAULT 'active',
    `remember_token` VARCHAR(100) NULL COMMENT 'folosit pentru resetare parolă',
    `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_users_email` (`email`),
    KEY `idx_users_role` (`role`),
    KEY `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabelul: categories
-- Cele două categorii principale: Agricole / Construcții.
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
    `id`   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(50)  NOT NULL COMMENT 'ex: agricole, constructii',
    `name` VARCHAR(100) NOT NULL,
    UNIQUE KEY `uq_categories_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabelul: machinery_types
-- Cele 20 de tipuri de utilaje, grupate pe categorie.
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `machinery_types`;
CREATE TABLE `machinery_types` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT UNSIGNED NOT NULL,
    `slug`        VARCHAR(60)  NOT NULL,
    `name`        VARCHAR(100) NOT NULL,
    UNIQUE KEY `uq_machinery_types_slug` (`slug`),
    KEY `idx_machinery_types_category` (`category_id`),
    CONSTRAINT `fk_machinery_types_category`
        FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabelul: listings (anunțuri)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `listings`;
CREATE TABLE `listings` (
    `id`                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`              INT UNSIGNED NOT NULL COMMENT 'proprietarul anunțului',
    `machinery_type_id`    INT UNSIGNED NOT NULL,
    `title`                VARCHAR(180) NOT NULL,
    `description`          TEXT NULL,
    `manufacturer`         VARCHAR(100) NULL,
    `model`                VARCHAR(100) NULL,
    `sale_price`           DECIMAL(12,2) NULL COMMENT 'preț de vânzare (EUR)',
    `rental_price_per_day` DECIMAL(10,2) NULL COMMENT 'tarif de închiriere pe zi (EUR)',
    `manufacturing_year`   SMALLINT UNSIGNED NOT NULL,
    `operating_hours`      INT UNSIGNED NULL,
    `engine_power`         INT UNSIGNED NULL COMMENT 'putere motor (CP)',
    `condition`            ENUM('new', 'used') NOT NULL DEFAULT 'used',
    `offer_type`           ENUM('sale', 'rental') NOT NULL,
    `status`               ENUM('available', 'rented', 'reserved', 'sold') NOT NULL DEFAULT 'available',
    `approval_status`      ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    `city`                 VARCHAR(100) NOT NULL,
    `county`               VARCHAR(100) NOT NULL,
    `created_at`           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_listings_user` (`user_id`),
    KEY `idx_listings_machinery_type` (`machinery_type_id`),
    KEY `idx_listings_offer_type` (`offer_type`),
    KEY `idx_listings_status` (`status`),
    KEY `idx_listings_approval_status` (`approval_status`),
    KEY `idx_listings_county_city` (`county`, `city`),
    KEY `idx_listings_year` (`manufacturing_year`),
    KEY `idx_listings_sale_price` (`sale_price`),
    KEY `idx_listings_rental_price` (`rental_price_per_day`),
    FULLTEXT KEY `ft_listings_title_description` (`title`, `description`),
    CONSTRAINT `fk_listings_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_listings_machinery_type`
        FOREIGN KEY (`machinery_type_id`) REFERENCES `machinery_types` (`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabelul: listing_images
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `listing_images`;
CREATE TABLE `listing_images` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `listing_id` INT UNSIGNED NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
    `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_listing_images_listing` (`listing_id`),
    CONSTRAINT `fk_listing_images_listing`
        FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabelul: favorites
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `favorites`;
CREATE TABLE `favorites` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`    INT UNSIGNED NOT NULL,
    `listing_id` INT UNSIGNED NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_favorites_user_listing` (`user_id`, `listing_id`),
    KEY `idx_favorites_listing` (`listing_id`),
    CONSTRAINT `fk_favorites_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_favorites_listing`
        FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabelul: conversations
-- `listing_id` este opțional: leagă discuția de anunțul care a generat-o
-- (folosit de frontend în mesaje.html pentru a afișa "Despre: <anunț>").
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `conversations`;
CREATE TABLE `conversations` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `buyer_id`   INT UNSIGNED NOT NULL,
    `seller_id`  INT UNSIGNED NOT NULL,
    `listing_id` INT UNSIGNED NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_conversations_participants_listing` (`buyer_id`, `seller_id`, `listing_id`),
    KEY `idx_conversations_seller` (`seller_id`),
    KEY `idx_conversations_listing` (`listing_id`),
    CONSTRAINT `fk_conversations_buyer`
        FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_conversations_seller`
        FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_conversations_listing`
        FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabelul: messages
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `conversation_id` INT UNSIGNED NOT NULL,
    `sender_id`       INT UNSIGNED NOT NULL,
    `message`         TEXT NOT NULL,
    `is_read`         TINYINT(1) NOT NULL DEFAULT 0,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_messages_conversation` (`conversation_id`),
    KEY `idx_messages_sender` (`sender_id`),
    KEY `idx_messages_is_read` (`is_read`),
    CONSTRAINT `fk_messages_conversation`
        FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_messages_sender`
        FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabelul: rentals (închirieri)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `rentals`;
CREATE TABLE `rentals` (
    `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `listing_id`     INT UNSIGNED NOT NULL,
    `renter_id`      INT UNSIGNED NOT NULL,
    `start_date`     DATE NOT NULL,
    `end_date`       DATE NOT NULL,
    `total_price`    DECIMAL(12,2) NOT NULL COMMENT 'numar_zile x pret_pe_zi',
    `rental_status`  ENUM('pending', 'confirmed', 'cancelled', 'completed') NOT NULL DEFAULT 'pending',
    `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_rentals_listing` (`listing_id`),
    KEY `idx_rentals_renter` (`renter_id`),
    KEY `idx_rentals_dates` (`start_date`, `end_date`),
    KEY `idx_rentals_status` (`rental_status`),
    CONSTRAINT `fk_rentals_listing`
        FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_rentals_renter`
        FOREIGN KEY (`renter_id`) REFERENCES `users` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `chk_rentals_dates` CHECK (`end_date` >= `start_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabelul: admin_logs
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `admin_logs`;
CREATE TABLE `admin_logs` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `admin_id`    INT UNSIGNED NOT NULL,
    `action`      VARCHAR(100) NOT NULL COMMENT 'ex: approve_listing, block_user',
    `target_type` VARCHAR(50)  NOT NULL COMMENT 'ex: listing, user',
    `target_id`   INT UNSIGNED NOT NULL,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_admin_logs_admin` (`admin_id`),
    KEY `idx_admin_logs_target` (`target_type`, `target_id`),
    CONSTRAINT `fk_admin_logs_admin`
        FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
