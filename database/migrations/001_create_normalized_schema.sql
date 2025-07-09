-- =========================================
-- Database Normalization Migration Script
-- Creates new normalized tables with proper relationships and UTF8MB4 charset
-- =========================================

-- Set database to use UTF8MB4 charset
ALTER DATABASE `renaltales` CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- =========================================
-- 1. Create normalized users table
-- =========================================
CREATE TABLE IF NOT EXISTS `users_new` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `email_verified` BOOLEAN DEFAULT FALSE,
    `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
    `status` ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    `last_login_at` TIMESTAMP NULL DEFAULT NULL,
    `failed_login_count` INT UNSIGNED DEFAULT 0,
    `locked_until` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_username` (`username`),
    UNIQUE KEY `uk_email` (`email`),
    INDEX `idx_email_verified` (`email_verified`),
    INDEX `idx_status` (`status`),
    INDEX `idx_last_login` (`last_login_at`),
    INDEX `idx_created_at` (`created_at`),
    INDEX `idx_locked_until` (`locked_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- 2. Create user profiles table (normalized from users JSON field)
-- =========================================
CREATE TABLE IF NOT EXISTS `user_profiles` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `first_name` VARCHAR(100) NULL,
    `last_name` VARCHAR(100) NULL,
    `display_name` VARCHAR(150) NULL,
    `avatar_url` VARCHAR(500) NULL,
    `bio` TEXT NULL,
    `timezone` VARCHAR(50) DEFAULT 'UTC',
    `language` VARCHAR(10) DEFAULT 'en',
    `date_format` VARCHAR(20) DEFAULT 'Y-m-d',
    `time_format` VARCHAR(20) DEFAULT 'H:i',
    `phone` VARCHAR(20) NULL,
    `date_of_birth` DATE NULL,
    `gender` ENUM('male', 'female', 'other', 'prefer_not_to_say') NULL,
    `country` VARCHAR(2) NULL,
    `city` VARCHAR(100) NULL,
    `privacy_settings` JSON NULL,
    `notification_settings` JSON NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_user_id` (`user_id`),
    INDEX `idx_display_name` (`display_name`),
    INDEX `idx_language` (`language`),
    INDEX `idx_country` (`country`),
    INDEX `idx_created_at` (`created_at`),
    CONSTRAINT `fk_user_profiles_user_id` FOREIGN KEY (`user_id`) REFERENCES `users_new`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- 3. Create password resets table (normalized)
-- =========================================
CREATE TABLE IF NOT EXISTS `password_resets_new` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `token_hash` VARCHAR(255) NOT NULL,
    `expires_at` TIMESTAMP NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `used_at` TIMESTAMP NULL DEFAULT NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(500) NULL,
    `is_used` BOOLEAN DEFAULT FALSE,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_token_hash` (`token_hash`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_expires_at` (`expires_at`),
    INDEX `idx_is_used` (`is_used`),
    INDEX `idx_created_at` (`created_at`),
    INDEX `idx_cleanup` (`expires_at`, `is_used`),
    CONSTRAINT `fk_password_resets_user_id` FOREIGN KEY (`user_id`) REFERENCES `users_new`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- 4. Create email verifications table (normalized)
-- =========================================
CREATE TABLE IF NOT EXISTS `email_verifications_new` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `token_hash` VARCHAR(255) NOT NULL,
    `expires_at` TIMESTAMP NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `verified_at` TIMESTAMP NULL DEFAULT NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(500) NULL,
    `is_verified` BOOLEAN DEFAULT FALSE,
    `verification_type` ENUM('registration', 'email_change', 'login_verification') DEFAULT 'registration',
    `old_email` VARCHAR(255) NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_token_hash` (`token_hash`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_email` (`email`),
    INDEX `idx_expires_at` (`expires_at`),
    INDEX `idx_is_verified` (`is_verified`),
    INDEX `idx_verification_type` (`verification_type`),
    INDEX `idx_created_at` (`created_at`),
    INDEX `idx_cleanup` (`expires_at`, `is_verified`),
    CONSTRAINT `fk_email_verifications_user_id` FOREIGN KEY (`user_id`) REFERENCES `users_new`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- 5. Create user sessions table (normalized)
-- =========================================
CREATE TABLE IF NOT EXISTS `user_sessions_new` (
    `id` VARCHAR(255) NOT NULL,
    `user_id` INT UNSIGNED NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(500) NULL,
    `payload` LONGTEXT NOT NULL,
    `last_activity` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `expires_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_last_activity` (`last_activity`),
    INDEX `idx_expires_at` (`expires_at`),
    INDEX `idx_cleanup` (`expires_at`, `last_activity`),
    CONSTRAINT `fk_user_sessions_user_id` FOREIGN KEY (`user_id`) REFERENCES `users_new`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- 6. Create audit logs table (normalized)
-- =========================================
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NULL,
    `event_type` VARCHAR(50) NOT NULL,
    `event_category` ENUM('auth', 'user', 'security', 'system', 'data') NOT NULL,
    `description` TEXT NOT NULL,
    `resource_type` VARCHAR(50) NULL,
    `resource_id` INT UNSIGNED NULL,
    `old_values` JSON NULL,
    `new_values` JSON NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` VARCHAR(500) NULL,
    `session_id` VARCHAR(255) NULL,
    `request_method` VARCHAR(10) NULL,
    `request_url` VARCHAR(2000) NULL,
    `severity` ENUM('info', 'warning', 'error', 'critical') DEFAULT 'info',
    `additional_data` JSON NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_event_type` (`event_type`),
    INDEX `idx_event_category` (`event_category`),
    INDEX `idx_resource` (`resource_type`, `resource_id`),
    INDEX `idx_severity` (`severity`),
    INDEX `idx_ip_address` (`ip_address`),
    INDEX `idx_created_at` (`created_at`),
    INDEX `idx_session_id` (`session_id`),
    INDEX `idx_user_events` (`user_id`, `event_type`, `created_at`),
    INDEX `idx_security_events` (`event_category`, `severity`, `created_at`),
    CONSTRAINT `fk_audit_logs_user_id` FOREIGN KEY (`user_id`) REFERENCES `users_new`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- 7. Create security events table (specialized audit)
-- =========================================
CREATE TABLE IF NOT EXISTS `security_events` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NULL,
    `event_type` ENUM('login_success', 'login_failure', 'logout', 'password_change', 'email_change', 'account_locked', 'account_unlocked', 'suspicious_activity') NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` VARCHAR(500) NULL,
    `country` VARCHAR(100) NULL,
    `city` VARCHAR(100) NULL,
    `risk_score` TINYINT UNSIGNED DEFAULT 0,
    `failure_reason` VARCHAR(255) NULL,
    `attempt_count` INT UNSIGNED DEFAULT 1,
    `blocked_until` TIMESTAMP NULL,
    `additional_data` JSON NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_event_type` (`event_type`),
    INDEX `idx_ip_address` (`ip_address`),
    INDEX `idx_risk_score` (`risk_score`),
    INDEX `idx_created_at` (`created_at`),
    INDEX `idx_blocked_until` (`blocked_until`),
    INDEX `idx_user_events` (`user_id`, `event_type`, `created_at`),
    INDEX `idx_security_analysis` (`event_type`, `risk_score`, `created_at`),
    CONSTRAINT `fk_security_events_user_id` FOREIGN KEY (`user_id`) REFERENCES `users_new`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- 8. Create system logs table
-- =========================================
CREATE TABLE IF NOT EXISTS `system_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `level` ENUM('debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency') NOT NULL,
    `message` TEXT NOT NULL,
    `context` JSON NULL,
    `channel` VARCHAR(50) DEFAULT 'system',
    `extra` JSON NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_level` (`level`),
    INDEX `idx_channel` (`channel`),
    INDEX `idx_created_at` (`created_at`),
    INDEX `idx_level_created` (`level`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- 9. Create language preferences table
-- =========================================
CREATE TABLE IF NOT EXISTS `language_preferences` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `language_code` VARCHAR(10) NOT NULL,
    `is_primary` BOOLEAN DEFAULT FALSE,
    `proficiency_level` ENUM('beginner', 'intermediate', 'advanced', 'native') DEFAULT 'intermediate',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_user_language` (`user_id`, `language_code`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_language_code` (`language_code`),
    INDEX `idx_is_primary` (`is_primary`),
    CONSTRAINT `fk_language_preferences_user_id` FOREIGN KEY (`user_id`) REFERENCES `users_new`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- 10. Create database version tracking
-- =========================================
CREATE TABLE IF NOT EXISTS `database_migrations` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `migration` VARCHAR(255) NOT NULL,
    `batch` INT UNSIGNED NOT NULL,
    `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_migration` (`migration`),
    INDEX `idx_batch` (`batch`),
    INDEX `idx_executed_at` (`executed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Record this migration
INSERT INTO `database_migrations` (`migration`, `batch`) VALUES ('001_create_normalized_schema', 1);
