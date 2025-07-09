-- =========================================
-- Database Seeder Script
-- Seeds the normalized database with testing data
-- =========================================

-- Start transaction
START TRANSACTION;

-- Insert users
INSERT INTO `users_new` (`username`, `email`, `password_hash`, `status`)
VALUES 
('tester1', 'tester1@example.com', '$2y$10$example1', 'active'),
('tester2', 'tester2@example.com', '$2y$10$example2', 'inactive'),
('tester3', 'tester3@example.com', '$2y$10$example3', 'suspended');

-- Insert user profiles
INSERT INTO `user_profiles` (`user_id`, `first_name`, `last_name`, `display_name`, `timezone`, `language`)
VALUES 
(1, 'Test', 'User', 'Test User', 'UTC', 'en'),
(2, 'Another', 'Tester', 'A. Tester', 'CET', 'fr'),
(3, 'Sample', 'User', 'Sample User', 'PST', 'es');

-- Insert password resets
INSERT INTO `password_resets_new` (`user_id`, `token_hash`, `expires_at`)
VALUES
(1, '$2y$10$tokenhash1', NOW() + INTERVAL 1 DAY),
(2, '$2y$10$tokenhash2', NOW() + INTERVAL 1 DAY);

-- Insert email verifications
INSERT INTO `email_verifications_new` (`user_id`, `email`, `token_hash`, `expires_at`, `is_verified`)
VALUES
(1, 'tester1@example.com', '$2y$10$emailtoken1', NOW() + INTERVAL 1 DAY, FALSE),
(2, 'tester2@example.com', '$2y$10$emailtoken2', NOW() + INTERVAL 1 DAY, TRUE);

-- Insert user sessions
INSERT INTO `user_sessions_new` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`) 
VALUES
('session1', 1, '127.0.0.1', 'Test Agent', 'payload_data1'),
('session2', 2, '127.0.0.2', 'Another Agent', 'payload_data2');

-- Insert audit logs
INSERT INTO `audit_logs` (`user_id`, `event_type`, `event_category`, `description`, `ip_address`, `severity`)
VALUES
(1, 'login', 'auth', 'User logged in', '127.0.0.1', 'info'),
(2, 'logout', 'auth', 'User logged out', '127.0.0.2', 'info');

-- Insert security events
INSERT INTO `security_events` (`user_id`, `event_type`, `ip_address`, `country`, `city`, `risk_score`)
VALUES
(1, 'login_success', '127.0.0.1', 'US', 'New York', 2),
(2, 'login_failure', '127.0.0.2', 'FR', 'Paris', 3);

-- Insert language preferences
INSERT INTO `language_preferences` (`user_id`, `language_code`, `is_primary`)
VALUES
(1, 'en', TRUE),
(2, 'fr', TRUE),
(3, 'es', TRUE);

-- Commit transaction
COMMIT;

-- Success message
SELECT 'Database seeding completed successfully!' as message;
