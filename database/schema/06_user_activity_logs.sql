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
