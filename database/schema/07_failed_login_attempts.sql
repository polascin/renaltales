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
