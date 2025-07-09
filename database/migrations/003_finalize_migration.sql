-- =========================================
-- Finalize Migration Script
-- Drops old tables and renames new tables to final names
-- IMPORTANT: Run this only after testing and confirming the migration worked correctly
-- =========================================

-- Start transaction
START TRANSACTION;

-- =========================================
-- 1. Drop old tables (backup first if needed)
-- =========================================

-- Drop views first
DROP VIEW IF EXISTS `users_view`;

-- Drop old tables
DROP TABLE IF EXISTS `user_registration_logs`;
DROP TABLE IF EXISTS `login_logs`;
DROP TABLE IF EXISTS `logout_logs`;
DROP TABLE IF EXISTS `user_activity_logs`;
DROP TABLE IF EXISTS `failed_login_attempts`;
DROP TABLE IF EXISTS `security_audit_log`;
DROP TABLE IF EXISTS `password_resets`;
DROP TABLE IF EXISTS `email_verifications`;
DROP TABLE IF EXISTS `user_sessions`;
DROP TABLE IF EXISTS `users`;

-- =========================================
-- 2. Rename new tables to final names
-- =========================================

RENAME TABLE `users_new` TO `users`;
RENAME TABLE `password_resets_new` TO `password_resets`;
RENAME TABLE `email_verifications_new` TO `email_verifications`;
RENAME TABLE `user_sessions_new` TO `user_sessions`;

-- =========================================
-- 3. Create additional indexes for performance
-- =========================================

-- Users table additional indexes
CREATE INDEX `idx_users_status_created` ON `users` (`status`, `created_at`);
CREATE INDEX `idx_users_email_verified_status` ON `users` (`email_verified`, `status`);
CREATE INDEX `idx_users_last_login_status` ON `users` (`last_login_at`, `status`);

-- User profiles additional indexes
CREATE INDEX `idx_profiles_name_search` ON `user_profiles` (`first_name`, `last_name`);
CREATE INDEX `idx_profiles_location` ON `user_profiles` (`country`, `city`);
CREATE INDEX `idx_profiles_language_country` ON `user_profiles` (`language`, `country`);

-- Password resets performance indexes
CREATE INDEX `idx_password_resets_user_created` ON `password_resets` (`user_id`, `created_at`);
CREATE INDEX `idx_password_resets_expires_used` ON `password_resets` (`expires_at`, `is_used`);

-- Email verifications performance indexes
CREATE INDEX `idx_email_verifications_user_created` ON `email_verifications` (`user_id`, `created_at`);
CREATE INDEX `idx_email_verifications_type_created` ON `email_verifications` (`verification_type`, `created_at`);

-- User sessions performance indexes
CREATE INDEX `idx_user_sessions_user_activity` ON `user_sessions` (`user_id`, `last_activity`);
CREATE INDEX `idx_user_sessions_expires_activity` ON `user_sessions` (`expires_at`, `last_activity`);

-- Audit logs performance indexes
CREATE INDEX `idx_audit_logs_category_created` ON `audit_logs` (`event_category`, `created_at`);
CREATE INDEX `idx_audit_logs_severity_created` ON `audit_logs` (`severity`, `created_at`);
CREATE INDEX `idx_audit_logs_resource_created` ON `audit_logs` (`resource_type`, `resource_id`, `created_at`);

-- Security events performance indexes
CREATE INDEX `idx_security_events_type_created` ON `security_events` (`event_type`, `created_at`);
CREATE INDEX `idx_security_events_risk_created` ON `security_events` (`risk_score`, `created_at`);
CREATE INDEX `idx_security_events_ip_created` ON `security_events` (`ip_address`, `created_at`);

-- Language preferences performance indexes
CREATE INDEX `idx_language_preferences_code_primary` ON `language_preferences` (`language_code`, `is_primary`);

-- =========================================
-- 4. Create stored procedures for common operations
-- =========================================

-- Procedure to get user with profile
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS GetUserWithProfile(IN user_id INT)
BEGIN
    SELECT 
        u.*, 
        p.first_name, p.last_name, p.display_name, p.avatar_url, p.bio,
        p.timezone, p.language, p.date_format, p.time_format,
        p.phone, p.date_of_birth, p.gender, p.country, p.city,
        p.privacy_settings, p.notification_settings
    FROM users u
    LEFT JOIN user_profiles p ON u.id = p.user_id
    WHERE u.id = user_id;
END //
DELIMITER ;

-- Procedure to clean expired tokens
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS CleanExpiredTokens()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE deleted_count INT DEFAULT 0;
    
    -- Clean expired password resets
    DELETE FROM password_resets WHERE expires_at < NOW() OR (is_used = TRUE AND used_at < DATE_SUB(NOW(), INTERVAL 7 DAY));
    SET deleted_count = ROW_COUNT();
    
    -- Clean expired email verifications
    DELETE FROM email_verifications WHERE expires_at < NOW() OR (is_verified = TRUE AND verified_at < DATE_SUB(NOW(), INTERVAL 7 DAY));
    SET deleted_count = deleted_count + ROW_COUNT();
    
    -- Clean expired sessions
    DELETE FROM user_sessions WHERE expires_at < NOW() OR last_activity < DATE_SUB(NOW(), INTERVAL 30 DAY);
    SET deleted_count = deleted_count + ROW_COUNT();
    
    -- Log cleanup
    INSERT INTO system_logs (level, message, context, channel)
    VALUES ('info', 'Expired tokens cleaned', JSON_OBJECT('deleted_count', deleted_count), 'cleanup');
    
    SELECT CONCAT('Cleaned ', deleted_count, ' expired records') as message;
END //
DELIMITER ;

-- Procedure to get user security events
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS GetUserSecurityEvents(IN user_id INT, IN limit_count INT)
BEGIN
    SELECT 
        event_type, ip_address, country, city, risk_score,
        failure_reason, attempt_count, created_at
    FROM security_events
    WHERE user_id = user_id
    ORDER BY created_at DESC
    LIMIT limit_count;
END //
DELIMITER ;

-- Procedure to update user last login
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS UpdateUserLastLogin(IN user_id INT, IN ip_addr VARCHAR(45))
BEGIN
    UPDATE users 
    SET last_login_at = NOW(), failed_login_count = 0, locked_until = NULL
    WHERE id = user_id;
    
    INSERT INTO security_events (user_id, event_type, ip_address, created_at)
    VALUES (user_id, 'login_success', ip_addr, NOW());
END //
DELIMITER ;

-- =========================================
-- 5. Create triggers for audit logging
-- =========================================

-- Trigger for user updates
DELIMITER //
CREATE TRIGGER IF NOT EXISTS users_update_audit
AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (
        user_id, event_type, event_category, description, 
        old_values, new_values, ip_address, severity, created_at
    ) VALUES (
        NEW.id, 'user_update', 'user', 'User data updated',
        JSON_OBJECT('email', OLD.email, 'status', OLD.status),
        JSON_OBJECT('email', NEW.email, 'status', NEW.status),
        '127.0.0.1', 'info', NOW()
    );
END //
DELIMITER ;

-- Trigger for password reset usage
DELIMITER //
CREATE TRIGGER IF NOT EXISTS password_reset_used_audit
AFTER UPDATE ON password_resets
FOR EACH ROW
BEGIN
    IF NEW.is_used = TRUE AND OLD.is_used = FALSE THEN
        INSERT INTO audit_logs (
            user_id, event_type, event_category, description, 
            ip_address, severity, created_at
        ) VALUES (
            NEW.user_id, 'password_reset_used', 'security', 'Password reset token used',
            NEW.ip_address, 'warning', NOW()
        );
    END IF;
END //
DELIMITER ;

-- =========================================
-- 6. Create events for automatic cleanup
-- =========================================

-- Enable event scheduler if not already enabled
SET GLOBAL event_scheduler = ON;

-- Daily cleanup event
CREATE EVENT IF NOT EXISTS `daily_cleanup`
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    CALL CleanExpiredTokens();
END;

-- Weekly audit log cleanup (keep last 3 months)
CREATE EVENT IF NOT EXISTS `weekly_audit_cleanup`
ON SCHEDULE EVERY 1 WEEK
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    DELETE FROM audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 3 MONTH);
    DELETE FROM security_events WHERE created_at < DATE_SUB(NOW(), INTERVAL 3 MONTH);
    DELETE FROM system_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 MONTH);
END;

-- =========================================
-- 7. Update table statistics
-- =========================================

ANALYZE TABLE `users`;
ANALYZE TABLE `user_profiles`;
ANALYZE TABLE `password_resets`;
ANALYZE TABLE `email_verifications`;
ANALYZE TABLE `user_sessions`;
ANALYZE TABLE `audit_logs`;
ANALYZE TABLE `security_events`;
ANALYZE TABLE `system_logs`;
ANALYZE TABLE `language_preferences`;

-- =========================================
-- 8. Record migration completion
-- =========================================

INSERT INTO `database_migrations` (`migration`, `batch`) VALUES ('003_finalize_migration', 1);

-- Log migration completion
INSERT INTO `system_logs` (`level`, `message`, `context`, `channel`)
VALUES ('info', 'Database migration completed successfully', 
        JSON_OBJECT('migration', '003_finalize_migration', 'tables_migrated', 9), 
        'migration');

-- Commit transaction
COMMIT;

-- Success message
SELECT 'Migration finalized successfully! Old tables dropped, new tables renamed, and performance optimizations applied.' as message;
