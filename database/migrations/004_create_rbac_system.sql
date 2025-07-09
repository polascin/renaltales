-- =========================================
-- RBAC System Migration
-- Creates tables for Role-Based Access Control
-- =========================================

-- Create roles table
CREATE TABLE IF NOT EXISTS `roles` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `display_name` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `is_default` BOOLEAN DEFAULT FALSE,
    `is_system` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_role_name` (`name`),
    INDEX `idx_is_default` (`is_default`),
    INDEX `idx_is_system` (`is_system`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create permissions table
CREATE TABLE IF NOT EXISTS `permissions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `display_name` VARCHAR(150) NOT NULL,
    `description` TEXT NULL,
    `category` VARCHAR(50) NOT NULL DEFAULT 'general',
    `is_system` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_permission_name` (`name`),
    INDEX `idx_category` (`category`),
    INDEX `idx_is_system` (`is_system`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create role_permissions table (many-to-many)
CREATE TABLE IF NOT EXISTS `role_permissions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `role_id` INT UNSIGNED NOT NULL,
    `permission_id` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_role_permission` (`role_id`, `permission_id`),
    INDEX `idx_role_id` (`role_id`),
    INDEX `idx_permission_id` (`permission_id`),
    CONSTRAINT `fk_role_permissions_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_role_permissions_permission_id` FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create user_roles table (many-to-many)
CREATE TABLE IF NOT EXISTS `user_roles` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `role_id` INT UNSIGNED NOT NULL,
    `assigned_by` INT UNSIGNED NULL,
    `assigned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `expires_at` TIMESTAMP NULL,
    `is_active` BOOLEAN DEFAULT TRUE,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_user_role` (`user_id`, `role_id`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_role_id` (`role_id`),
    INDEX `idx_assigned_by` (`assigned_by`),
    INDEX `idx_expires_at` (`expires_at`),
    INDEX `idx_is_active` (`is_active`),
    CONSTRAINT `fk_user_roles_user_id` FOREIGN KEY (`user_id`) REFERENCES `users_new`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_user_roles_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_user_roles_assigned_by` FOREIGN KEY (`assigned_by`) REFERENCES `users_new`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create two-factor authentication table
CREATE TABLE IF NOT EXISTS `user_two_factor_auth` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `secret_key` VARCHAR(255) NOT NULL,
    `backup_codes` JSON NULL,
    `recovery_codes` JSON NULL,
    `is_enabled` BOOLEAN DEFAULT FALSE,
    `enabled_at` TIMESTAMP NULL,
    `last_used_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_user_id` (`user_id`),
    INDEX `idx_is_enabled` (`is_enabled`),
    CONSTRAINT `fk_user_2fa_user_id` FOREIGN KEY (`user_id`) REFERENCES `users_new`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default roles
INSERT INTO `roles` (`name`, `display_name`, `description`, `is_default`, `is_system`) VALUES
('admin', 'Administrator', 'Full system access with all permissions', FALSE, TRUE),
('moderator', 'Moderator', 'Can moderate content and manage users', FALSE, TRUE),
('user', 'User', 'Standard user with basic permissions', TRUE, TRUE),
('guest', 'Guest', 'Limited access for unregistered users', FALSE, TRUE);

-- Insert default permissions
INSERT INTO `permissions` (`name`, `display_name`, `description`, `category`, `is_system`) VALUES
-- User management
('user.view', 'View Users', 'Can view user profiles and information', 'user_management', TRUE),
('user.create', 'Create Users', 'Can create new user accounts', 'user_management', TRUE),
('user.edit', 'Edit Users', 'Can edit user profiles and information', 'user_management', TRUE),
('user.delete', 'Delete Users', 'Can delete user accounts', 'user_management', TRUE),
('user.manage_roles', 'Manage User Roles', 'Can assign and remove roles from users', 'user_management', TRUE),

-- Content management
('content.view', 'View Content', 'Can view content and posts', 'content', TRUE),
('content.create', 'Create Content', 'Can create new content and posts', 'content', TRUE),
('content.edit', 'Edit Content', 'Can edit existing content', 'content', TRUE),
('content.delete', 'Delete Content', 'Can delete content and posts', 'content', TRUE),
('content.publish', 'Publish Content', 'Can publish and unpublish content', 'content', TRUE),

-- System administration
('system.admin', 'System Administration', 'Full system administration access', 'system', TRUE),
('system.settings', 'System Settings', 'Can modify system settings', 'system', TRUE),
('system.logs', 'View System Logs', 'Can view system logs and audit trails', 'system', TRUE),
('system.backup', 'System Backup', 'Can create and restore system backups', 'system', TRUE),

-- Security
('security.audit', 'Security Audit', 'Can view security logs and audit trails', 'security', TRUE),
('security.manage', 'Security Management', 'Can manage security settings', 'security', TRUE),

-- Profile management
('profile.view_own', 'View Own Profile', 'Can view own profile', 'profile', TRUE),
('profile.edit_own', 'Edit Own Profile', 'Can edit own profile', 'profile', TRUE),
('profile.view_others', 'View Other Profiles', 'Can view other user profiles', 'profile', TRUE);

-- Assign permissions to roles
INSERT INTO `role_permissions` (`role_id`, `permission_id`) 
SELECT r.id, p.id FROM `roles` r, `permissions` p 
WHERE r.name = 'admin';

INSERT INTO `role_permissions` (`role_id`, `permission_id`) 
SELECT r.id, p.id FROM `roles` r, `permissions` p 
WHERE r.name = 'moderator' AND p.name IN (
    'user.view', 'user.edit', 'user.manage_roles',
    'content.view', 'content.create', 'content.edit', 'content.delete', 'content.publish',
    'profile.view_own', 'profile.edit_own', 'profile.view_others'
);

INSERT INTO `role_permissions` (`role_id`, `permission_id`) 
SELECT r.id, p.id FROM `roles` r, `permissions` p 
WHERE r.name = 'user' AND p.name IN (
    'content.view', 'content.create', 'content.edit',
    'profile.view_own', 'profile.edit_own'
);

INSERT INTO `role_permissions` (`role_id`, `permission_id`) 
SELECT r.id, p.id FROM `roles` r, `permissions` p 
WHERE r.name = 'guest' AND p.name IN (
    'content.view'
);

-- Record this migration
INSERT INTO `database_migrations` (`migration`, `batch`) VALUES ('004_create_rbac_system', 1);
