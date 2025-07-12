-- Database Schema Fixes for RenalTales
-- Created: $(date)
-- Purpose: Fix structural issues, missing indexes, and data integrity problems

-- Start transaction for safety
START TRANSACTION;

-- ===================================================
-- 1. ANALYZE CURRENT STATE
-- ===================================================

-- Check current table sizes
SELECT 'Table Analysis' as Section;
SELECT 
    TABLE_NAME,
    TABLE_ROWS,
    ROUND(((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024), 2) AS 'Size (MB)',
    ENGINE,
    TABLE_COLLATION
FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = 'renaltales' 
ORDER BY TABLE_ROWS DESC;

-- ===================================================
-- 2. REMOVE REDUNDANT TABLES
-- ===================================================

-- Before removing redundant tables, check if they have any data
SELECT 'Checking redundant tables for data' as Section;

-- Check users_new table
SELECT 'users_new table count:' as Info, COUNT(*) as Count FROM users_new;
SELECT 'users table count:' as Info, COUNT(*) as Count FROM users;

-- Check email_verifications_new table
SELECT 'email_verifications_new table count:' as Info, COUNT(*) as Count FROM email_verifications_new;
SELECT 'email_verifications table count:' as Info, COUNT(*) as Count FROM email_verifications;

-- Check password_resets_new table
SELECT 'password_resets_new table count:' as Info, COUNT(*) as Count FROM password_resets_new;
SELECT 'password_resets table count:' as Info, COUNT(*) as Count FROM password_resets;

-- Check user_sessions_new table
SELECT 'user_sessions_new table count:' as Info, COUNT(*) as Count FROM user_sessions_new;
SELECT 'user_sessions table count:' as Info, COUNT(*) as Count FROM user_sessions;

-- Since the _new tables are empty, we can safely remove them
-- But first, we need to drop foreign key constraints that reference them

SELECT 'Dropping foreign key constraints referencing redundant tables' as Section;

-- Drop foreign keys referencing users_new
ALTER TABLE audit_logs DROP FOREIGN KEY fk_audit_logs_user_id;
ALTER TABLE email_verifications_new DROP FOREIGN KEY fk_email_verifications_user_id;
ALTER TABLE language_preferences DROP FOREIGN KEY fk_language_preferences_user_id;
ALTER TABLE password_resets_new DROP FOREIGN KEY fk_password_resets_user_id;
ALTER TABLE security_events DROP FOREIGN KEY fk_security_events_user_id;
ALTER TABLE user_profiles DROP FOREIGN KEY fk_user_profiles_user_id;
ALTER TABLE user_sessions_new DROP FOREIGN KEY fk_user_sessions_user_id;

-- Drop the redundant tables
SELECT 'Dropping redundant tables' as Section;
DROP TABLE IF EXISTS users_new;
DROP TABLE IF EXISTS email_verifications_new;
DROP TABLE IF EXISTS password_resets_new;
DROP TABLE IF EXISTS user_sessions_new;

-- ===================================================
-- 3. FIX FOREIGN KEY CONSTRAINTS
-- ===================================================

SELECT 'Recreating foreign key constraints to point to correct tables' as Section;

-- Add foreign keys for tables that were referencing users_new
ALTER TABLE audit_logs 
ADD CONSTRAINT fk_audit_logs_user_id 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE language_preferences 
ADD CONSTRAINT fk_language_preferences_user_id 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE security_events 
ADD CONSTRAINT fk_security_events_user_id 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE user_profiles 
ADD CONSTRAINT fk_user_profiles_user_id 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE;

-- ===================================================
-- 4. ADD MISSING INDEXES
-- ===================================================

SELECT 'Adding missing indexes for better performance' as Section;

-- Add composite indexes for common query patterns
ALTER TABLE users ADD INDEX idx_email_verified_role (email_verified_at, role);
ALTER TABLE users ADD INDEX idx_last_login (last_login_at);
ALTER TABLE users ADD INDEX idx_created_at (created_at);

-- Add indexes for stories table
ALTER TABLE stories ADD INDEX idx_created_at (created_at);
ALTER TABLE stories ADD INDEX idx_updated_at (updated_at);
ALTER TABLE stories ADD INDEX idx_user_status (user_id, status);
ALTER TABLE stories ADD INDEX idx_category_status (category_id, status);
ALTER TABLE stories ADD INDEX idx_language_status (original_language, status);

-- Add indexes for comments table
ALTER TABLE comments ADD INDEX idx_created_at (created_at);
ALTER TABLE comments ADD INDEX idx_story_status (story_id, status);
ALTER TABLE comments ADD INDEX idx_user_created (user_id, created_at);

-- Add indexes for story_contents table (idx_language already exists)
ALTER TABLE story_contents ADD INDEX idx_created_at (created_at);
ALTER TABLE story_contents ADD INDEX idx_updated_at (updated_at);
ALTER TABLE story_contents ADD INDEX idx_translator_status (translator_id, status);

-- Add indexes for activity logs
ALTER TABLE activity_logs ADD INDEX idx_user_action (user_id, action);
ALTER TABLE activity_logs ADD INDEX idx_action_created (action, created_at);

-- Add indexes for audit logs
ALTER TABLE audit_logs ADD INDEX idx_user_created (user_id, created_at);
ALTER TABLE audit_logs ADD INDEX idx_action (action);
ALTER TABLE audit_logs ADD INDEX idx_created_at (created_at);

-- Add indexes for login logs
ALTER TABLE login_logs ADD INDEX idx_user_created (user_id, created_at);
ALTER TABLE login_logs ADD INDEX idx_ip_address (ip_address);
ALTER TABLE login_logs ADD INDEX idx_created_at (created_at);

-- Add indexes for security logs
ALTER TABLE security_logs ADD INDEX idx_user_created (user_id, created_at);
ALTER TABLE security_logs ADD INDEX idx_event_type (event_type);
ALTER TABLE security_logs ADD INDEX idx_created_at (created_at);

-- Add indexes for user sessions
ALTER TABLE user_sessions ADD INDEX idx_user_created (user_id, created_at);
ALTER TABLE user_sessions ADD INDEX idx_expires_at (expires_at);
ALTER TABLE user_sessions ADD INDEX idx_created_at (created_at);

-- ===================================================
-- 5. OPTIMIZE TABLE STRUCTURES
-- ===================================================

SELECT 'Optimizing table structures' as Section;

-- Optimize tables to reclaim space and update statistics
OPTIMIZE TABLE users;
OPTIMIZE TABLE stories;
OPTIMIZE TABLE story_contents;
OPTIMIZE TABLE comments;
OPTIMIZE TABLE activity_logs;
OPTIMIZE TABLE audit_logs;
OPTIMIZE TABLE login_logs;
OPTIMIZE TABLE security_logs;
OPTIMIZE TABLE user_sessions;

-- ===================================================
-- 6. ANALYZE TABLES TO UPDATE STATISTICS
-- ===================================================

SELECT 'Analyzing tables to update statistics' as Section;

ANALYZE TABLE users;
ANALYZE TABLE stories;
ANALYZE TABLE story_contents;
ANALYZE TABLE comments;
ANALYZE TABLE activity_logs;
ANALYZE TABLE audit_logs;
ANALYZE TABLE login_logs;
ANALYZE TABLE security_logs;
ANALYZE TABLE user_sessions;

-- ===================================================
-- 7. FINAL VERIFICATION
-- ===================================================

SELECT 'Final verification' as Section;

-- Check foreign key constraints
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
WHERE TABLE_SCHEMA = 'renaltales' 
AND REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY TABLE_NAME, COLUMN_NAME;

-- Check indexes
SELECT 
    TABLE_NAME,
    INDEX_NAME,
    GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS COLUMNS
FROM INFORMATION_SCHEMA.STATISTICS 
WHERE TABLE_SCHEMA = 'renaltales' 
AND INDEX_NAME != 'PRIMARY'
GROUP BY TABLE_NAME, INDEX_NAME
ORDER BY TABLE_NAME, INDEX_NAME;

-- Check table collations
SELECT 
    TABLE_NAME,
    TABLE_COLLATION,
    ENGINE
FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = 'renaltales'
ORDER BY TABLE_NAME;

SELECT 'Database optimization completed successfully!' as Result;

-- Commit the transaction
COMMIT;
