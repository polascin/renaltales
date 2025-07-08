-- =========================================
-- Renal Tales Logging System Setup Script
-- This script creates all necessary logging tables for comprehensive audit trail
-- =========================================

-- Use the renaltales database
USE `renaltales`;

-- =========================================
-- User Registration Logs Table
-- Captures registration events with IP and user agent
-- =========================================
CREATE TABLE IF NOT EXISTS `user_registration_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NULL, -- NULL if registration failed
  `username` VARCHAR(50) NULL,
  `email` VARCHAR(255) NOT NULL,
  `registration_status` ENUM('success', 'failed', 'pending') NOT NULL,
  `failure_reason` VARCHAR(255) NULL, -- Reason for failed registration
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` TEXT NULL,
  `country` VARCHAR(100) NULL, -- Detected country from IP
  `city` VARCHAR(100) NULL, -- Detected city from IP
  `registration_source` VARCHAR(50) DEFAULT 'web', -- web, mobile, api, etc.
  `referrer` TEXT NULL, -- HTTP referrer
  `additional_data` JSON NULL, -- Additional registration data
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_email` (`email`),
  INDEX `idx_ip_address` (`ip_address`),
  INDEX `idx_registration_status` (`registration_status`),
  INDEX `idx_created_at` (`created_at`),
  INDEX `idx_registration_source` (`registration_source`),
  INDEX `idx_failed_registrations` (`registration_status`, `ip_address`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- Login Logs Table
-- Tracks user login events with timestamp and location
-- =========================================
CREATE TABLE IF NOT EXISTS `login_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `username` VARCHAR(50) NOT NULL,
  `login_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` TEXT NULL,
  `country` VARCHAR(100) NULL, -- Detected country from IP
  `city` VARCHAR(100) NULL, -- Detected city from IP
  `login_source` VARCHAR(50) DEFAULT 'web', -- web, mobile, api, etc.
  `additional_data` JSON NULL, -- Additional login data
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_username` (`username`),
  INDEX `idx_ip_address` (`ip_address`),
  INDEX `idx_login_time` (`login_time`),
  INDEX `idx_login_source` (`login_source`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- Logout Logs Table
-- Tracks user logout events with timestamp and location
-- =========================================
CREATE TABLE IF NOT EXISTS `logout_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `username` VARCHAR(50) NOT NULL,
  `logout_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` TEXT NULL,
  `country` VARCHAR(100) NULL, -- Detected country from IP
  `city` VARCHAR(100) NULL, -- Detected city from IP
  `logout_source` VARCHAR(50) DEFAULT 'web', -- web, mobile, api, etc.
  `additional_data` JSON NULL, -- Additional logout data
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_username` (`username`),
  INDEX `idx_ip_address` (`ip_address`),
  INDEX `idx_logout_time` (`logout_time`),
  INDEX `idx_logout_source` (`logout_source`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- User Activity Logs Table
-- Audits user actions within the application
-- =========================================
CREATE TABLE IF NOT EXISTS `user_activity_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `username` VARCHAR(50) NOT NULL,
  `action_type` VARCHAR(100) NOT NULL, -- login, logout, profile_update, password_change, etc.
  `action_description` TEXT NOT NULL,
  `resource_type` VARCHAR(50) NULL, -- user, profile, settings, etc.
  `resource_id` INT UNSIGNED NULL, -- ID of the affected resource
  `old_values` JSON NULL, -- Previous values before change
  `new_values` JSON NULL, -- New values after change
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` TEXT NULL,
  `country` VARCHAR(100) NULL, -- Detected country from IP
  `city` VARCHAR(100) NULL, -- Detected city from IP
  `request_method` VARCHAR(10) NULL, -- GET, POST, PUT, DELETE, etc.
  `request_url` TEXT NULL, -- URL of the request
  `session_id` VARCHAR(255) NULL, -- Session identifier
  `severity` ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
  `additional_data` JSON NULL, -- Additional activity data
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_username` (`username`),
  INDEX `idx_action_type` (`action_type`),
  INDEX `idx_resource_type` (`resource_type`),
  INDEX `idx_resource_id` (`resource_id`),
  INDEX `idx_ip_address` (`ip_address`),
  INDEX `idx_severity` (`severity`),
  INDEX `idx_created_at` (`created_at`),
  INDEX `idx_session_id` (`session_id`),
  INDEX `idx_user_actions` (`user_id`, `action_type`, `created_at`),
  INDEX `idx_critical_actions` (`severity`, `action_type`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- Failed Login Attempts Table
-- Records failed login attempts and potential security threats
-- =========================================
CREATE TABLE IF NOT EXISTS `failed_login_attempts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NULL, -- NULL if username/email not found
  `username_or_email` VARCHAR(255) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` TEXT NULL,
  `country` VARCHAR(100) NULL, -- Detected country from IP
  `city` VARCHAR(100) NULL, -- Detected city from IP
  `failure_reason` ENUM('invalid_credentials', 'account_suspended', 'account_inactive', 'too_many_attempts', 'user_not_found', 'email_not_verified', 'other') NOT NULL,
  `attempted_password_hash` VARCHAR(255) NULL, -- For security analysis (optional)
  `request_method` VARCHAR(10) DEFAULT 'POST',
  `request_url` TEXT NULL,
  `session_id` VARCHAR(255) NULL,
  `attempt_count` INT UNSIGNED DEFAULT 1, -- Number of consecutive failed attempts from this IP
  `is_blocked` BOOLEAN DEFAULT FALSE, -- Whether this IP/user is temporarily blocked
  `blocked_until` TIMESTAMP NULL, -- When the block expires
  `threat_level` ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
  `additional_data` JSON NULL, -- Additional security data
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_username_or_email` (`username_or_email`),
  INDEX `idx_ip_address` (`ip_address`),
  INDEX `idx_failure_reason` (`failure_reason`),
  INDEX `idx_is_blocked` (`is_blocked`),
  INDEX `idx_blocked_until` (`blocked_until`),
  INDEX `idx_threat_level` (`threat_level`),
  INDEX `idx_created_at` (`created_at`),
  INDEX `idx_ip_attempts` (`ip_address`, `created_at`),
  INDEX `idx_user_attempts` (`user_id`, `created_at`),
  INDEX `idx_active_blocks` (`is_blocked`, `blocked_until`),
  INDEX `idx_threat_analysis` (`threat_level`, `failure_reason`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- Cleanup procedures for logging tables
-- =========================================

-- Procedure to clean up old registration logs (keep last 12 months)
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS CleanupOldRegistrationLogs()
BEGIN
    DELETE FROM `user_registration_logs` 
    WHERE `created_at` < DATE_SUB(NOW(), INTERVAL 12 MONTH);
END //
DELIMITER ;

-- Procedure to clean up old login logs (keep last 6 months)
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS CleanupOldLoginLogs()
BEGIN
    DELETE FROM `login_logs` 
    WHERE `login_time` < DATE_SUB(NOW(), INTERVAL 6 MONTH);
END //
DELIMITER ;

-- Procedure to clean up old logout logs (keep last 6 months)
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS CleanupOldLogoutLogs()
BEGIN
    DELETE FROM `logout_logs` 
    WHERE `logout_time` < DATE_SUB(NOW(), INTERVAL 6 MONTH);
END //
DELIMITER ;

-- Procedure to clean up old activity logs (keep last 3 months for non-critical, 12 months for critical)
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS CleanupOldActivityLogs()
BEGIN
    -- Clean up non-critical activity logs older than 3 months
    DELETE FROM `user_activity_logs` 
    WHERE `created_at` < DATE_SUB(NOW(), INTERVAL 3 MONTH)
    AND `severity` IN ('low', 'medium');
    
    -- Clean up critical activity logs older than 12 months
    DELETE FROM `user_activity_logs` 
    WHERE `created_at` < DATE_SUB(NOW(), INTERVAL 12 MONTH)
    AND `severity` IN ('high', 'critical');
END //
DELIMITER ;

-- Procedure to clean up old failed login attempts (keep last 3 months)
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS CleanupOldFailedLoginAttempts()
BEGIN
    DELETE FROM `failed_login_attempts` 
    WHERE `created_at` < DATE_SUB(NOW(), INTERVAL 3 MONTH);
END //
DELIMITER ;

-- Procedure to unblock expired IP blocks
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS UnblockExpiredIPs()
BEGIN
    UPDATE `failed_login_attempts` 
    SET `is_blocked` = FALSE 
    WHERE `is_blocked` = TRUE 
    AND `blocked_until` < NOW();
END //
DELIMITER ;

-- =========================================
-- Event scheduler for automatic cleanup
-- =========================================

-- Enable event scheduler
SET GLOBAL event_scheduler = ON;

-- Daily cleanup of expired IP blocks
CREATE EVENT IF NOT EXISTS `daily_ip_unblock`
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    CALL UnblockExpiredIPs();
END;

-- Weekly cleanup of old logs
CREATE EVENT IF NOT EXISTS `weekly_log_cleanup`
ON SCHEDULE EVERY 1 WEEK
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    CALL CleanupOldRegistrationLogs();
    CALL CleanupOldLoginLogs();
    CALL CleanupOldLogoutLogs();
    CALL CleanupOldActivityLogs();
    CALL CleanupOldFailedLoginAttempts();
END;

-- =========================================
-- Sample data for testing (optional)
-- =========================================

-- Insert sample registration log
INSERT INTO `user_registration_logs` 
(`user_id`, `username`, `email`, `registration_status`, `ip_address`, `user_agent`, `registration_source`) 
VALUES 
(1, 'admin', 'admin@renaltales.local', 'success', '127.0.0.1', 'Mozilla/5.0 Test Browser', 'web');

-- Insert sample login log
INSERT INTO `login_logs` 
(`user_id`, `username`, `ip_address`, `user_agent`, `login_source`) 
VALUES 
(1, 'admin', '127.0.0.1', 'Mozilla/5.0 Test Browser', 'web');

-- Insert sample activity log
INSERT INTO `user_activity_logs` 
(`user_id`, `username`, `action_type`, `action_description`, `ip_address`, `user_agent`) 
VALUES 
(1, 'admin', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 Test Browser');

-- Success message
SELECT 'Logging system setup completed successfully!' AS message;
