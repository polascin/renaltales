-- =========================================
-- Data Migration Script
-- Transfers existing data from old tables to new normalized structure
-- =========================================

-- Start transaction to ensure data integrity
START TRANSACTION;

-- =========================================
-- 1. Migrate users data
-- =========================================
INSERT INTO `users_new` (
    `id`, `username`, `email`, `password_hash`, `email_verified`, `email_verified_at`, 
    `status`, `last_login_at`, `created_at`, `updated_at`
)
SELECT 
    `id`, `username`, `email`, `password_hash`, `email_verified`, `email_verified_at`,
    `status`, `last_login_at`, `created_at`, `updated_at`
FROM `users`
WHERE NOT EXISTS (SELECT 1 FROM `users_new` WHERE `users_new`.`id` = `users`.`id`);

-- =========================================
-- 2. Migrate user profiles from JSON data
-- =========================================
INSERT INTO `user_profiles` (
    `user_id`, `first_name`, `last_name`, `display_name`, `timezone`, `language`, 
    `date_format`, `time_format`, `phone`, `date_of_birth`, `gender`, `country`, 
    `city`, `privacy_settings`, `notification_settings`, `created_at`, `updated_at`
)
SELECT 
    `id` as `user_id`,
    JSON_UNQUOTE(JSON_EXTRACT(`profile_data`, '$.first_name')) as `first_name`,
    JSON_UNQUOTE(JSON_EXTRACT(`profile_data`, '$.last_name')) as `last_name`,
    JSON_UNQUOTE(JSON_EXTRACT(`profile_data`, '$.display_name')) as `display_name`,
    COALESCE(JSON_UNQUOTE(JSON_EXTRACT(`profile_data`, '$.timezone')), 'UTC') as `timezone`,
    COALESCE(JSON_UNQUOTE(JSON_EXTRACT(`profile_data`, '$.language')), 'en') as `language`,
    COALESCE(JSON_UNQUOTE(JSON_EXTRACT(`profile_data`, '$.date_format')), 'Y-m-d') as `date_format`,
    COALESCE(JSON_UNQUOTE(JSON_EXTRACT(`profile_data`, '$.time_format')), 'H:i') as `time_format`,
    JSON_UNQUOTE(JSON_EXTRACT(`profile_data`, '$.phone')) as `phone`,
    JSON_UNQUOTE(JSON_EXTRACT(`profile_data`, '$.date_of_birth')) as `date_of_birth`,
    JSON_UNQUOTE(JSON_EXTRACT(`profile_data`, '$.gender')) as `gender`,
    JSON_UNQUOTE(JSON_EXTRACT(`profile_data`, '$.country')) as `country`,
    JSON_UNQUOTE(JSON_EXTRACT(`profile_data`, '$.city')) as `city`,
    JSON_EXTRACT(`profile_data`, '$.privacy_settings') as `privacy_settings`,
    JSON_EXTRACT(`profile_data`, '$.notification_settings') as `notification_settings`,
    `created_at`,
    `updated_at`
FROM `users`
WHERE `profile_data` IS NOT NULL 
    AND NOT EXISTS (SELECT 1 FROM `user_profiles` WHERE `user_profiles`.`user_id` = `users`.`id`);

-- Create default profiles for users without profile data
INSERT INTO `user_profiles` (`user_id`, `created_at`, `updated_at`)
SELECT `id`, `created_at`, `updated_at`
FROM `users`
WHERE `profile_data` IS NULL 
    AND NOT EXISTS (SELECT 1 FROM `user_profiles` WHERE `user_profiles`.`user_id` = `users`.`id`);

-- =========================================
-- 3. Migrate password resets
-- =========================================
INSERT INTO `password_resets_new` (
    `id`, `user_id`, `token_hash`, `expires_at`, `created_at`, `used_at`, 
    `ip_address`, `user_agent`, `is_used`
)
SELECT 
    `id`, `user_id`, `token_hash`, `expires_at`, `created_at`, `used_at`,
    `ip_address`, `user_agent`, `is_used`
FROM `password_resets`
WHERE NOT EXISTS (SELECT 1 FROM `password_resets_new` WHERE `password_resets_new`.`id` = `password_resets`.`id`);

-- =========================================
-- 4. Migrate email verifications
-- =========================================
INSERT INTO `email_verifications_new` (
    `id`, `user_id`, `email`, `token_hash`, `expires_at`, `created_at`, 
    `verified_at`, `ip_address`, `user_agent`, `is_verified`, 
    `verification_type`, `old_email`
)
SELECT 
    `id`, `user_id`, `email`, `token_hash`, `expires_at`, `created_at`,
    `verified_at`, `ip_address`, `user_agent`, `is_verified`,
    `verification_type`, `old_email`
FROM `email_verifications`
WHERE NOT EXISTS (SELECT 1 FROM `email_verifications_new` WHERE `email_verifications_new`.`id` = `email_verifications`.`id`);

-- =========================================
-- 5. Migrate user sessions
-- =========================================
INSERT INTO `user_sessions_new` (
    `id`, `user_id`, `ip_address`, `user_agent`, `payload`, 
    `last_activity`, `created_at`
)
SELECT 
    `id`, `user_id`, `ip_address`, `user_agent`, `payload`,
    FROM_UNIXTIME(`last_activity`) as `last_activity`,
    `created_at`
FROM `user_sessions`
WHERE NOT EXISTS (SELECT 1 FROM `user_sessions_new` WHERE `user_sessions_new`.`id` = `user_sessions`.`id`);

-- =========================================
-- 6. Migrate audit logs from existing security and activity logs
-- =========================================

-- Migrate from security_audit_log
INSERT INTO `audit_logs` (
    `user_id`, `event_type`, `event_category`, `description`, `ip_address`, 
    `user_agent`, `severity`, `additional_data`, `created_at`
)
SELECT 
    `user_id`, `event_type`, 'security' as `event_category`, `event_description`,
    `ip_address`, `user_agent`, `severity`, `additional_data`, `created_at`
FROM `security_audit_log`
WHERE NOT EXISTS (
    SELECT 1 FROM `audit_logs` 
    WHERE `audit_logs`.`user_id` = `security_audit_log`.`user_id` 
    AND `audit_logs`.`event_type` = `security_audit_log`.`event_type`
    AND `audit_logs`.`created_at` = `security_audit_log`.`created_at`
);

-- Migrate from user_activity_logs
INSERT INTO `audit_logs` (
    `user_id`, `event_type`, `event_category`, `description`, `resource_type`, 
    `resource_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, 
    `session_id`, `request_method`, `request_url`, `severity`, `additional_data`, `created_at`
)
SELECT 
    `user_id`, `action_type`, 'user' as `event_category`, `action_description`,
    `resource_type`, `resource_id`, `old_values`, `new_values`, `ip_address`, 
    `user_agent`, `session_id`, `request_method`, `request_url`, `severity`, 
    `additional_data`, `created_at`
FROM `user_activity_logs`
WHERE NOT EXISTS (
    SELECT 1 FROM `audit_logs` 
    WHERE `audit_logs`.`user_id` = `user_activity_logs`.`user_id` 
    AND `audit_logs`.`event_type` = `user_activity_logs`.`action_type`
    AND `audit_logs`.`created_at` = `user_activity_logs`.`created_at`
);

-- =========================================
-- 7. Migrate security events from various log tables
-- =========================================

-- Migrate from login_logs
INSERT INTO `security_events` (
    `user_id`, `event_type`, `ip_address`, `user_agent`, `country`, 
    `city`, `additional_data`, `created_at`
)
SELECT 
    `user_id`, 'login_success' as `event_type`, `ip_address`, `user_agent`,
    `country`, `city`, `additional_data`, `login_time` as `created_at`
FROM `login_logs`
WHERE NOT EXISTS (
    SELECT 1 FROM `security_events` 
    WHERE `security_events`.`user_id` = `login_logs`.`user_id` 
    AND `security_events`.`event_type` = 'login_success'
    AND `security_events`.`created_at` = `login_logs`.`login_time`
);

-- Migrate from logout_logs
INSERT INTO `security_events` (
    `user_id`, `event_type`, `ip_address`, `user_agent`, `country`, 
    `city`, `additional_data`, `created_at`
)
SELECT 
    `user_id`, 'logout' as `event_type`, `ip_address`, `user_agent`,
    `country`, `city`, `additional_data`, `logout_time` as `created_at`
FROM `logout_logs`
WHERE NOT EXISTS (
    SELECT 1 FROM `security_events` 
    WHERE `security_events`.`user_id` = `logout_logs`.`user_id` 
    AND `security_events`.`event_type` = 'logout'
    AND `security_events`.`created_at` = `logout_logs`.`logout_time`
);

-- Migrate from failed_login_attempts
INSERT INTO `security_events` (
    `user_id`, `event_type`, `ip_address`, `user_agent`, `country`, 
    `city`, `risk_score`, `failure_reason`, `attempt_count`, `blocked_until`, 
    `additional_data`, `created_at`
)
SELECT 
    `user_id`, 'login_failure' as `event_type`, `ip_address`, `user_agent`,
    `country`, `city`, 
    CASE 
        WHEN `threat_level` = 'low' THEN 1
        WHEN `threat_level` = 'medium' THEN 2
        WHEN `threat_level` = 'high' THEN 3
        WHEN `threat_level` = 'critical' THEN 4
        ELSE 1
    END as `risk_score`,
    `failure_reason`, `attempt_count`, `blocked_until`, `additional_data`, `created_at`
FROM `failed_login_attempts`
WHERE NOT EXISTS (
    SELECT 1 FROM `security_events` 
    WHERE `security_events`.`user_id` = `failed_login_attempts`.`user_id` 
    AND `security_events`.`event_type` = 'login_failure'
    AND `security_events`.`created_at` = `failed_login_attempts`.`created_at`
);

-- Migrate from user_registration_logs
INSERT INTO `security_events` (
    `user_id`, `event_type`, `ip_address`, `user_agent`, `country`, 
    `city`, `failure_reason`, `additional_data`, `created_at`
)
SELECT 
    `user_id`, 'login_success' as `event_type`, `ip_address`, `user_agent`,
    `country`, `city`, `failure_reason`, `additional_data`, `created_at`
FROM `user_registration_logs`
WHERE `registration_status` = 'success'
    AND NOT EXISTS (
        SELECT 1 FROM `security_events` 
        WHERE `security_events`.`user_id` = `user_registration_logs`.`user_id` 
        AND `security_events`.`event_type` = 'login_success'
        AND `security_events`.`created_at` = `user_registration_logs`.`created_at`
    );

-- =========================================
-- 8. Create language preferences from user profiles
-- =========================================
INSERT INTO `language_preferences` (`user_id`, `language_code`, `is_primary`, `created_at`, `updated_at`)
SELECT 
    `user_id`, `language`, TRUE as `is_primary`, `created_at`, `updated_at`
FROM `user_profiles`
WHERE `language` IS NOT NULL 
    AND `language` != ''
    AND NOT EXISTS (
        SELECT 1 FROM `language_preferences` 
        WHERE `language_preferences`.`user_id` = `user_profiles`.`user_id`
        AND `language_preferences`.`language_code` = `user_profiles`.`language`
    );

-- =========================================
-- 9. Data validation and cleanup
-- =========================================

-- Update user_agent field length where needed (truncate if too long)
UPDATE `password_resets_new` SET `user_agent` = LEFT(`user_agent`, 500) WHERE LENGTH(`user_agent`) > 500;
UPDATE `email_verifications_new` SET `user_agent` = LEFT(`user_agent`, 500) WHERE LENGTH(`user_agent`) > 500;
UPDATE `user_sessions_new` SET `user_agent` = LEFT(`user_agent`, 500) WHERE LENGTH(`user_agent`) > 500;
UPDATE `audit_logs` SET `user_agent` = LEFT(`user_agent`, 500) WHERE LENGTH(`user_agent`) > 500;
UPDATE `security_events` SET `user_agent` = LEFT(`user_agent`, 500) WHERE LENGTH(`user_agent`) > 500;

-- Set expires_at for sessions (30 days from creation)
UPDATE `user_sessions_new` 
SET `expires_at` = DATE_ADD(`created_at`, INTERVAL 30 DAY) 
WHERE `expires_at` IS NULL;

-- Clean up invalid data
DELETE FROM `password_resets_new` WHERE `user_id` NOT IN (SELECT `id` FROM `users_new`);
DELETE FROM `email_verifications_new` WHERE `user_id` NOT IN (SELECT `id` FROM `users_new`);
DELETE FROM `user_sessions_new` WHERE `user_id` IS NOT NULL AND `user_id` NOT IN (SELECT `id` FROM `users_new`);
DELETE FROM `user_profiles` WHERE `user_id` NOT IN (SELECT `id` FROM `users_new`);
DELETE FROM `language_preferences` WHERE `user_id` NOT IN (SELECT `id` FROM `users_new`);

-- =========================================
-- 10. Update statistics and optimize tables
-- =========================================

-- Update table statistics
ANALYZE TABLE `users_new`;
ANALYZE TABLE `user_profiles`;
ANALYZE TABLE `password_resets_new`;
ANALYZE TABLE `email_verifications_new`;
ANALYZE TABLE `user_sessions_new`;
ANALYZE TABLE `audit_logs`;
ANALYZE TABLE `security_events`;
ANALYZE TABLE `language_preferences`;

-- Record this migration
INSERT INTO `database_migrations` (`migration`, `batch`) VALUES ('002_migrate_existing_data', 1);

-- Commit the transaction
COMMIT;

-- =========================================
-- 11. Create views for backward compatibility (temporary)
-- =========================================

-- Create view for users table (temporary compatibility)
CREATE OR REPLACE VIEW `users_view` AS
SELECT 
    u.`id`, u.`username`, u.`email`, u.`password_hash`, u.`email_verified`, 
    u.`email_verified_at`, u.`status`, u.`last_login_at`, u.`created_at`, u.`updated_at`,
    JSON_OBJECT(
        'first_name', p.`first_name`,
        'last_name', p.`last_name`,
        'display_name', p.`display_name`,
        'avatar_url', p.`avatar_url`,
        'bio', p.`bio`,
        'timezone', p.`timezone`,
        'language', p.`language`,
        'date_format', p.`date_format`,
        'time_format', p.`time_format`,
        'phone', p.`phone`,
        'date_of_birth', p.`date_of_birth`,
        'gender', p.`gender`,
        'country', p.`country`,
        'city', p.`city`,
        'privacy_settings', p.`privacy_settings`,
        'notification_settings', p.`notification_settings`
    ) as `profile_data`
FROM `users_new` u
LEFT JOIN `user_profiles` p ON u.`id` = p.`user_id`;

-- Success message
SELECT 'Data migration completed successfully!' as message;
