-- Rollback migration script for basic schema
-- Drops stories and users tables in correct order (foreign key dependencies)

-- Drop stories table first (has foreign key to users)
DROP TABLE IF EXISTS stories;

-- Drop users table
DROP TABLE IF EXISTS users;
