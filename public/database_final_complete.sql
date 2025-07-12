-- Complete Database Optimization - Final Steps
-- Complete the optimization process

-- Start transaction
START TRANSACTION;

-- Optimize and analyze all tables
SELECT 'Optimizing table structures' as Section;

OPTIMIZE TABLE users;
OPTIMIZE TABLE stories;
OPTIMIZE TABLE story_contents;
OPTIMIZE TABLE comments;
OPTIMIZE TABLE activity_logs;
OPTIMIZE TABLE audit_logs;
OPTIMIZE TABLE login_logs;
OPTIMIZE TABLE security_logs;
OPTIMIZE TABLE user_sessions;
OPTIMIZE TABLE user_activity_logs;
OPTIMIZE TABLE failed_login_attempts;
OPTIMIZE TABLE rate_limits;
OPTIMIZE TABLE system_logs;
OPTIMIZE TABLE security_events;

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
ANALYZE TABLE user_activity_logs;
ANALYZE TABLE failed_login_attempts;
ANALYZE TABLE rate_limits;
ANALYZE TABLE system_logs;
ANALYZE TABLE security_events;

-- Final verification and summary
SELECT 'Final verification and summary' as Section;

-- Count of tables
SELECT COUNT(*) as 'Total Tables' FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'renaltales';

-- Count of indexes
SELECT COUNT(*) as 'Total Indexes' FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = 'renaltales' AND INDEX_NAME != 'PRIMARY';

-- Count of foreign keys
SELECT COUNT(*) as 'Total Foreign Keys' FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = 'renaltales' AND REFERENCED_TABLE_NAME IS NOT NULL;

-- Check character encoding
SELECT 'Encoding Check:' as Info, 
       COUNT(*) as 'Total Tables',
       COUNT(CASE WHEN TABLE_COLLATION LIKE 'utf8mb4%' THEN 1 END) as 'utf8mb4 Tables'
FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = 'renaltales';

-- Summary of optimizations completed
SELECT 'Database optimization completed successfully!' as Result;

COMMIT;
