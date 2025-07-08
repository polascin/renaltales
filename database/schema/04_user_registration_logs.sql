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
