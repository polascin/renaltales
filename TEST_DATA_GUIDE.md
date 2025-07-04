# RenalTales Test Data Guide

## Overview
This document explains the comprehensive test data that has been created for the RenalTales application. The test data includes users with different roles, stories in multiple languages, comments, and activity logs.

## Execution Summary
✅ **Test data successfully inserted into the database!**

The SQL script `sample_test_data.sql` has been executed and populated the following tables:
- `users` - 7 test users including 1 admin
- `story_categories` - Used existing categories (not overwritten)
- `stories` - 8 stories in different languages
- `story_contents` - 8 story content records with rich text
- `comments` - 14 approved comments across stories
- `activity_logs` - User activity tracking records
- `security_logs` - Sample security events

## Test User Accounts

### Admin User
- **Username:** `admin`
- **Email:** `admin@renaltales.test`
- **Password:** `password`
- **Role:** `admin`
- **Full Name:** Administrator

### Regular Users
| Username | Email | Password | Role | Full Name | Language | Email Verified |
|----------|-------|----------|------|-----------|----------|----------------|
| john_doe | john@renaltales.test | password | user | John Doe | en | No |
| maria_gonzalez | maria@renaltales.test | password | verified_user | Maria Gonzalez | es | Yes |
| peter_novak | peter@renaltales.test | password | translator | Peter Novák | sk | Yes |
| sarah_wilson | sarah@renaltales.test | password | moderator | Sarah Wilson | en | Yes |
| anna_kovacova | anna@renaltales.test | password | verified_user | Anna Kováčová | sk | Yes |
| carlos_rodriguez | carlos@renaltales.test | password | user | Carlos Rodriguez | es | No |

## Test Stories Created

### English Stories
1. **"My Journey with Kidney Disease"** (maria_gonzalez) - Personal journey story, public access
2. **"Dialysis: What I Wish I Had Known"** (john_doe) - Medical experience, registered access
3. **"Finding Strength After Transplant"** (anna_kovacova) - Recovery story, public access
4. **"Supporting a Loved One Through Treatment"** (john_doe) - Caregiver perspective, draft status

### Slovak Stories
5. **"Môj príbeh s ochorením obličiek"** (peter_novak) - Personal journey, public access
6. **"Rodina a podpora počas liečby"** (anna_kovacova) - Family support, verified user access

### Spanish Stories
7. **"Mi experiencia con la diálisis"** (maria_gonzalez) - Dialysis experience, public access
8. **"Superando los obstáculos"** (carlos_rodriguez) - Overcoming challenges, public access

## Story Features Demonstrated

### Access Levels
- **Public:** Stories accessible to all visitors
- **Registered:** Stories requiring user login
- **Verified:** Stories requiring email verification

### Multi-language Content
- English (en): 4 stories
- Slovak (sk): 2 stories  
- Spanish (es): 2 stories

### Story Status
- **Published:** 7 stories available for viewing
- **Draft:** 1 story (for testing draft functionality)

## Comments and Engagement
- **14 approved comments** across multiple stories
- Comments in **English, Slovak, and Spanish**
- Comments from different user types (regular users, moderators, translators)
- Demonstrates community engagement features

## Testing Scenarios

### User Role Testing
1. **Admin User (`admin`):**
   - Full access to all features
   - Can moderate content
   - Access to admin panels

2. **Moderator (`sarah_wilson`):**
   - Can moderate stories and comments
   - Extended permissions

3. **Translator (`peter_novak`):**
   - Can translate content
   - Created Slovak content

4. **Verified Users (`maria_gonzalez`, `anna_kovacova`):**
   - Can access verified-only content
   - Email verification demonstrated

5. **Regular Users (`john_doe`, `carlos_rodriguez`):**
   - Basic user functionality
   - Some without email verification

### Access Control Testing
- Test public story access (no login required)
- Test registered-only story access (login required)
- Test verified-only story access (email verification required)

### Multi-language Testing
- Browse stories in different languages
- Test language preferences
- Verify proper character encoding (Slovak and Spanish characters)

### Comment System Testing
- View existing comments
- Test comment posting (with different user roles)
- Test comment moderation

## How to Test

### 1. Login Testing
Try logging in with any of the test accounts:
```
URL: http://localhost/renaltales/login
Username: admin (or any other test username)
Password: password
```

### 2. Story Browsing
- Visit the stories page to see multilingual content
- Test filtering by language
- Test access controls with different user logins

### 3. Comment Testing
- View stories with existing comments
- Login and try posting new comments
- Test as moderator to approve/reject comments

### 4. Admin Testing
- Login as `admin` user
- Access admin features (if available)
- Test user management
- Test content moderation

## Database Verification

You can verify the test data using these SQL queries:

```sql
-- Check users
SELECT username, email, role, full_name FROM users;

-- Check stories with authors
SELECT s.id, u.username, s.original_language, s.status, s.access_level 
FROM stories s JOIN users u ON s.user_id = u.id;

-- Check story content
SELECT story_id, language, title FROM story_contents ORDER BY story_id;

-- Check comments
SELECT COUNT(*) as comment_count FROM comments WHERE status = 'approved';
```

## Security Notes

⚠️ **Important:** This is test data for development/testing purposes only!

- All passwords are set to `password` (hashed properly)
- Email addresses use `.test` domain
- IP addresses are localhost (127.0.0.1)
- Security logs contain sample data

## Cleanup

To remove test data (if needed):
```sql
-- Remove test users (except keep admin if needed)
DELETE FROM users WHERE email LIKE '%@renaltales.test' AND username != 'admin';

-- Remove test stories and related data
DELETE FROM story_contents WHERE story_id IN (SELECT id FROM stories WHERE user_id IN (SELECT id FROM users WHERE email LIKE '%@renaltales.test'));
DELETE FROM comments WHERE story_id IN (SELECT id FROM stories WHERE user_id IN (SELECT id FROM users WHERE email LIKE '%@renaltales.test'));
DELETE FROM stories WHERE user_id IN (SELECT id FROM users WHERE email LIKE '%@renaltales.test');

-- Clean up logs
DELETE FROM activity_logs WHERE user_id IN (SELECT id FROM users WHERE email LIKE '%@renaltales.test');
DELETE FROM security_logs WHERE data LIKE '%@renaltales.test%';
```

## Next Steps

1. **Test the application** with the provided user accounts
2. **Verify multilingual functionality** works correctly
3. **Test access controls** with different user roles
4. **Check comment system** functionality
5. **Validate admin features** with the admin account
6. **Add more test data** as needed for specific features

---

**Test Data Creation Date:** 2024-02-15  
**Database:** renaltales  
**Environment:** Local development (Laragon)
