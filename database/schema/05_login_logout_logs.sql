-- =========================================
-- User Login and Logout Logs Table
-- Tracks user login and logout events with timestamp and location
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
