-- =========================================
-- Translation Management System Migration
-- Creates translations table for multilingual support
-- =========================================

-- Create translations table
CREATE TABLE IF NOT EXISTS `translations` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `language_code` VARCHAR(5) NOT NULL,
    `key_name` VARCHAR(255) NOT NULL,
    `translation_text` TEXT NOT NULL,
    `group_name` VARCHAR(100) DEFAULT 'default',
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_translation` (`language_code`, `key_name`, `group_name`),
    INDEX `idx_language_code` (`language_code`),
    INDEX `idx_key_name` (`key_name`),
    INDEX `idx_group_name` (`group_name`),
    INDEX `idx_is_active` (`is_active`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create languages table for language management
CREATE TABLE IF NOT EXISTS `languages` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(5) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `native_name` VARCHAR(100) NOT NULL,
    `flag_icon` VARCHAR(50) DEFAULT NULL,
    `direction` ENUM('ltr', 'rtl') DEFAULT 'ltr',
    `is_active` BOOLEAN DEFAULT TRUE,
    `is_default` BOOLEAN DEFAULT FALSE,
    `sort_order` INT UNSIGNED DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_code` (`code`),
    INDEX `idx_is_active` (`is_active`),
    INDEX `idx_is_default` (`is_default`),
    INDEX `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default languages
INSERT INTO `languages` (`code`, `name`, `native_name`, `flag_icon`, `direction`, `is_active`, `is_default`, `sort_order`) VALUES
('en', 'English', 'English', 'gb', 'ltr', TRUE, FALSE, 1),
('sk', 'Slovak', 'Slovenčina', 'sk', 'ltr', TRUE, TRUE, 2),
('cs', 'Czech', 'Čeština', 'cz', 'ltr', TRUE, FALSE, 3),
('de', 'German', 'Deutsch', 'de', 'ltr', TRUE, FALSE, 4),
('es', 'Spanish', 'Español', 'es', 'ltr', TRUE, FALSE, 5),
('fr', 'French', 'Français', 'fr', 'ltr', TRUE, FALSE, 6),
('it', 'Italian', 'Italiano', 'it', 'ltr', TRUE, FALSE, 7),
('ru', 'Russian', 'Русский', 'ru', 'ltr', TRUE, FALSE, 8),
('pl', 'Polish', 'Polski', 'pl', 'ltr', TRUE, FALSE, 9),
('hu', 'Hungarian', 'Magyar', 'hu', 'ltr', TRUE, FALSE, 10);

-- Create translation cache table for performance
CREATE TABLE IF NOT EXISTS `translation_cache` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `language_code` VARCHAR(5) NOT NULL,
    `cache_key` VARCHAR(255) NOT NULL,
    `cache_data` LONGTEXT NOT NULL,
    `expires_at` TIMESTAMP NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_cache_key` (`language_code`, `cache_key`),
    INDEX `idx_expires_at` (`expires_at`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert some default translations
INSERT INTO `translations` (`language_code`, `key_name`, `translation_text`, `group_name`) VALUES
-- English translations
('en', 'app_title', 'Kidney Stories', 'app'),
('en', 'app_subtitle', 'A Multilingual Web Application', 'app'),
('en', 'welcome', 'Welcome!', 'common'),
('en', 'language', 'Language', 'common'),
('en', 'change_language', 'Change Language', 'common'),
('en', 'home', 'Home', 'navigation'),
('en', 'about', 'About', 'navigation'),
('en', 'contact', 'Contact', 'navigation'),
('en', 'login', 'Login', 'auth'),
('en', 'register', 'Register', 'auth'),
('en', 'logout', 'Logout', 'auth'),

-- Slovak translations
('sk', 'app_title', 'Obličkové príbehy', 'app'),
('sk', 'app_subtitle', 'Viacjazyčná webová aplikácia', 'app'),
('sk', 'welcome', 'Vitajte!', 'common'),
('sk', 'language', 'Jazyk', 'common'),
('sk', 'change_language', 'Zmeniť jazyk', 'common'),
('sk', 'home', 'Domov', 'navigation'),
('sk', 'about', 'O nás', 'navigation'),
('sk', 'contact', 'Kontakt', 'navigation'),
('sk', 'login', 'Prihlásiť', 'auth'),
('sk', 'register', 'Registrácia', 'auth'),
('sk', 'logout', 'Odhlásiť', 'auth'),

-- Czech translations
('cs', 'app_title', 'Ledvinové příběhy', 'app'),
('cs', 'app_subtitle', 'Vícejazyčná webová aplikace', 'app'),
('cs', 'welcome', 'Vítejte!', 'common'),
('cs', 'language', 'Jazyk', 'common'),
('cs', 'change_language', 'Změnit jazyk', 'common'),
('cs', 'home', 'Domů', 'navigation'),
('cs', 'about', 'O nás', 'navigation'),
('cs', 'contact', 'Kontakt', 'navigation'),
('cs', 'login', 'Přihlásit', 'auth'),
('cs', 'register', 'Registrace', 'auth'),
('cs', 'logout', 'Odhlásit', 'auth'),

-- German translations
('de', 'app_title', 'Nierengeschichten', 'app'),
('de', 'app_subtitle', 'Eine mehrsprachige Webanwendung', 'app'),
('de', 'welcome', 'Willkommen!', 'common'),
('de', 'language', 'Sprache', 'common'),
('de', 'change_language', 'Sprache ändern', 'common'),
('de', 'home', 'Startseite', 'navigation'),
('de', 'about', 'Über uns', 'navigation'),
('de', 'contact', 'Kontakt', 'navigation'),
('de', 'login', 'Anmelden', 'auth'),
('de', 'register', 'Registrieren', 'auth'),
('de', 'logout', 'Abmelden', 'auth');
