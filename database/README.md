# Database Migration Documentation

## Overview
This document outlines the changes made during the database migration to a normalized schema with UTF8MB4 charset support for the `renaltales` project.

### Key Changes
- Creation of normalized tables with appropriate relationships.
- Implementation of foreign key constraints and performance indexes.
- Transition from old tables to new structures with migration scripts.
- Setup of UTF8MB4 charset for multilingual content support.
- Introduction of seeders for testing data.
- Addition of stored procedures, triggers, and cleanup events.

## Tables

### 1. `users`
- **Columns:** `id`, `username`, `email`, `password_hash`, `status`, `created_at`, `updated_at`
- **Changes:**
  - Added indexes for performance.
  - Moved profile information to `user_profiles`.

### 2. `user_profiles`
- **Columns:** `user_id`, `first_name`, `last_name`, `display_name`, `timezone`, `language`
- **Description:** Stores additional user profile information.

### 3. `password_resets`
- **Columns:** `id`, `user_id`, `token_hash`, `expires_at`, `created_at`, `is_used`
- **Indexes:** Added for expiration and usage searches.

### 4. `email_verifications`
- **Columns:** `id`, `user_id`, `email`, `token_hash`, `expires_at`, `created_at`, `is_verified`
- **Indexes:** Added for verification and expiration.

### 5. `user_sessions`
- **Columns:** `id`, `user_id`, `ip_address`, `user_agent`, `payload`
- **Indexes:** Added for session activity.

### 6. `audit_logs`
- **Columns:** `id`, `user_id`, `event_type`, `description`, `created_at`
- **Description:** Centralized logging for security and user activities.

### 7. `security_events`
- **Columns:** `id`, `user_id`, `event_type`, `ip_address`, `risk_score`
- **Description:** Specialized logs for security-related actions.

### 8. `language_preferences`
- **Columns:** `user_id`, `language_code`, `is_primary`
- **Description:** Manages language preferences for users.

## Migration Scripts
- **001_create_normalized_schema.sql**: Creates new tables and relationships.
- **002_migrate_existing_data.sql**: Transfers data from old tables to new structures.
- **003_finalize_migration.sql**: Removes old tables and reindexes new ones.

## Stored Procedures
- **GetUserWithProfile**: Retrieves user data along with profile information.
- **CleanExpiredTokens**: Removes expired tokens and logs the action.

## Seeders
- **001_seeder.sql**: Populates the database with initial test data.

## Execution
- Detailed execution steps through `migrate.php` script.
- Use `php migrate.php help` for available commands.

## Contact
For any issues with the migration, please contact the database admin team. 

---
Documentation generated on 2025-07-09 by database migration automation.
