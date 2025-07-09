-- Renal Tales Database Setup Script
-- This script creates all necessary tables for user security features
-- Including users, password resets, and email verifications

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS `renaltales` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `renaltales`;

-- =========================================
-- Users table for authentication
-- =========================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `email_verified` BOOLEAN DEFAULT FALSE,
  `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login_at` TIMESTAMP NULL DEFAULT NULL,
  `status` ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
  `profile_data` JSON NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_email` (`email`),
  INDEX `idx_username` (`username`),
  INDEX `idx_status` (`status`),
  INDEX `idx_email_verified` (`email_verified`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- Password resets table
-- =========================================
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `token` VARCHAR(255) NOT NULL UNIQUE,
  `token_hash` VARCHAR(255) NOT NULL,
  `expires_at` TIMESTAMP NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `used_at` TIMESTAMP NULL DEFAULT NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `is_used` BOOLEAN DEFAULT FALSE,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_token` (`token`),
  INDEX `idx_token_hash` (`token_hash`),
  INDEX `idx_email` (`email`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_expires_at` (`expires_at`),
  INDEX `idx_is_used` (`is_used`),
  INDEX `idx_created_at` (`created_at`),
  INDEX `idx_expired_tokens` (`expires_at`, `is_used`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- Email verifications table
-- =========================================
CREATE TABLE IF NOT EXISTS `email_verifications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `token` VARCHAR(255) NOT NULL UNIQUE,
  `token_hash` VARCHAR(255) NOT NULL,
  `expires_at` TIMESTAMP NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `verified_at` TIMESTAMP NULL DEFAULT NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `is_verified` BOOLEAN DEFAULT FALSE,
  `verification_type` ENUM('registration', 'email_change') DEFAULT 'registration',
  `old_email` VARCHAR(255) NULL, -- For email change verification
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_token` (`token`),
  INDEX `idx_token_hash` (`token_hash`),
  INDEX `idx_email` (`email`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_expires_at` (`expires_at`),
  INDEX `idx_is_verified` (`is_verified`),
  INDEX `idx_verification_type` (`verification_type`),
  INDEX `idx_created_at` (`created_at`),
  INDEX `idx_expired_verifications` (`expires_at`, `is_verified`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- Stories table
-- =========================================
CREATE TABLE IF NOT EXISTS `stories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `published` BOOLEAN DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- Categories table
-- =========================================
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- Tags table
-- =========================================
CREATE TABLE IF NOT EXISTS `tags` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- Story Categories table
-- =========================================
CREATE TABLE IF NOT EXISTS `story_categories` (
  `story_id` INT UNSIGNED NOT NULL,
  `category_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`story_id`, `category_id`),
  FOREIGN KEY (`story_id`) REFERENCES `stories`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- Story Tags table
-- =========================================
CREATE TABLE IF NOT EXISTS `story_tags` (
  `story_id` INT UNSIGNED NOT NULL,
  `tag_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`story_id`, `tag_id`),
  FOREIGN KEY (`story_id`) REFERENCES `stories`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`tag_id`) REFERENCES `tags`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- Story Media table
-- =========================================
CREATE TABLE IF NOT EXISTS `story_media` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `story_id` INT UNSIGNED NOT NULL,
  `filename` VARCHAR(255) NOT NULL,
  `original_filename` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `file_size` INT UNSIGNED NOT NULL,
  `mime_type` VARCHAR(100) NOT NULL,
  `media_type` ENUM('image', 'video', 'audio', 'document', 'other') NOT NULL,
  `alt_text` TEXT NULL,
  `caption` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`story_id`) REFERENCES `stories`(`id`) ON DELETE CASCADE,
  INDEX `idx_story_id` (`story_id`),
  INDEX `idx_media_type` (`media_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- Story Versions table
-- =========================================
CREATE TABLE IF NOT EXISTS `story_versions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `story_id` INT UNSIGNED NOT NULL,
  `version_number` INT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `metadata` JSON NULL,
  `created_by` INT UNSIGNED NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `notes` TEXT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`story_id`) REFERENCES `stories`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  UNIQUE KEY `unique_story_version` (`story_id`, `version_number`),
  INDEX `idx_story_id` (`story_id`),
  INDEX `idx_version_number` (`version_number`),
  INDEX `idx_created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- Story Comments table
-- =========================================
CREATE TABLE IF NOT EXISTS `story_comments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `story_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `parent_id` INT UNSIGNED NULL,
  `content` TEXT NOT NULL,
  `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`story_id`) REFERENCES `stories`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`parent_id`) REFERENCES `story_comments`(`id`) ON DELETE CASCADE,
  INDEX `idx_story_id` (`story_id`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_parent_id` (`parent_id`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- User sessions table (optional - for database-based sessions)
-- =========================================
CREATE TABLE IF NOT EXISTS `user_sessions` (
  `id` VARCHAR(255) NOT NULL,
  `user_id` INT UNSIGNED NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `payload` LONGTEXT NOT NULL,
  `last_activity` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_last_activity` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- Security audit log table (optional)
-- =========================================
CREATE TABLE IF NOT EXISTS `security_audit_log` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NULL,
  `event_type` VARCHAR(50) NOT NULL,
  `event_description` TEXT NOT NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `additional_data` JSON NULL,
  `severity` ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_event_type` (`event_type`),
  INDEX `idx_severity` (`severity`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- Insert default admin user (optional)
-- Password: admin123 (change this in production!)
-- =========================================
INSERT INTO `users` (`username`, `email`, `password_hash`, `email_verified`, `email_verified_at`, `status`) 
VALUES ('admin', 'admin@renaltales.local', '$2y$12$LQv3c1yqBNVdECAJHbgCVelq.LQIeyCwCYcVpfx4NjwjHMrJOdgMy', TRUE, NOW(), 'active')
ON DUPLICATE KEY UPDATE `updated_at` = NOW();

-- =========================================
-- LOGGING SYSTEM TABLES
-- =========================================

-- User Registration Logs Table
CREATE TABLE IF NOT EXISTS `user_registration_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NULL,
  `username` VARCHAR(50) NULL,
  `email` VARCHAR(255) NOT NULL,
  `registration_status` ENUM('success', 'failed', 'pending') NOT NULL,
  `failure_reason` VARCHAR(255) NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` TEXT NULL,
  `country` VARCHAR(100) NULL,
  `city` VARCHAR(100) NULL,
  `registration_source` VARCHAR(50) DEFAULT 'web',
  `referrer` TEXT NULL,
  `additional_data` JSON NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_email` (`email`),
  INDEX `idx_ip_address` (`ip_address`),
  INDEX `idx_registration_status` (`registration_status`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Login Logs Table
CREATE TABLE IF NOT EXISTS `login_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `username` VARCHAR(50) NOT NULL,
  `login_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` TEXT NULL,
  `country` VARCHAR(100) NULL,
  `city` VARCHAR(100) NULL,
  `login_source` VARCHAR(50) DEFAULT 'web',
  `additional_data` JSON NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_ip_address` (`ip_address`),
  INDEX `idx_login_time` (`login_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Logout Logs Table
CREATE TABLE IF NOT EXISTS `logout_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `username` VARCHAR(50) NOT NULL,
  `logout_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` TEXT NULL,
  `country` VARCHAR(100) NULL,
  `city` VARCHAR(100) NULL,
  `logout_source` VARCHAR(50) DEFAULT 'web',
  `additional_data` JSON NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_ip_address` (`ip_address`),
  INDEX `idx_logout_time` (`logout_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Activity Logs Table
CREATE TABLE IF NOT EXISTS `user_activity_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `username` VARCHAR(50) NOT NULL,
  `action_type` VARCHAR(100) NOT NULL,
  `action_description` TEXT NOT NULL,
  `resource_type` VARCHAR(50) NULL,
  `resource_id` INT UNSIGNED NULL,
  `old_values` JSON NULL,
  `new_values` JSON NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` TEXT NULL,
  `country` VARCHAR(100) NULL,
  `city` VARCHAR(100) NULL,
  `request_method` VARCHAR(10) NULL,
  `request_url` TEXT NULL,
  `session_id` VARCHAR(255) NULL,
  `severity` ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
  `additional_data` JSON NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_action_type` (`action_type`),
  INDEX `idx_severity` (`severity`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Failed Login Attempts Table
CREATE TABLE IF NOT EXISTS `failed_login_attempts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NULL,
  `username_or_email` VARCHAR(255) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` TEXT NULL,
  `country` VARCHAR(100) NULL,
  `city` VARCHAR(100) NULL,
  `failure_reason` ENUM('invalid_credentials', 'account_suspended', 'account_inactive', 'too_many_attempts', 'user_not_found', 'email_not_verified', 'other') NOT NULL,
  `attempt_count` INT UNSIGNED DEFAULT 1,
  `is_blocked` BOOLEAN DEFAULT FALSE,
  `blocked_until` TIMESTAMP NULL,
  `threat_level` ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
  `additional_data` JSON NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_ip_address` (`ip_address`),
  INDEX `idx_failure_reason` (`failure_reason`),
  INDEX `idx_threat_level` (`threat_level`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- Cleanup procedures (optional)
-- =========================================

-- Procedure to clean up expired password reset tokens
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS CleanupExpiredPasswordResets()
BEGIN
    DELETE FROM `password_resets` 
    WHERE `expires_at` < NOW() 
    OR (`is_used` = TRUE AND `used_at` < DATE_SUB(NOW(), INTERVAL 7 DAY));
END //
DELIMITER ;

-- Procedure to clean up expired email verification tokens
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS CleanupExpiredEmailVerifications()
BEGIN
    DELETE FROM `email_verifications` 
    WHERE `expires_at` < NOW() 
    OR (`is_verified` = TRUE AND `verified_at` < DATE_SUB(NOW(), INTERVAL 7 DAY));
END //
DELIMITER ;

-- Procedure to clean up old audit log entries (keep last 6 months)
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS CleanupOldAuditLogs()
BEGIN
    DELETE FROM `security_audit_log` 
    WHERE `created_at` < DATE_SUB(NOW(), INTERVAL 6 MONTH);
END //
DELIMITER ;

-- =========================================
-- Event scheduler for automatic cleanup (optional)
-- =========================================

-- Enable event scheduler
SET GLOBAL event_scheduler = ON;

-- Daily cleanup of expired tokens
CREATE EVENT IF NOT EXISTS `daily_token_cleanup`
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    CALL CleanupExpiredPasswordResets();
    CALL CleanupExpiredEmailVerifications();
END;

-- Weekly cleanup of old audit logs
CREATE EVENT IF NOT EXISTS `weekly_audit_cleanup`
ON SCHEDULE EVERY 1 WEEK
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    CALL CleanupOldAuditLogs();
END;
