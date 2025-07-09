# Modern User Management System - Documentation

## Overview

The Modern User Management System for RenalTales provides a comprehensive, secure, and scalable solution for user authentication, authorization, and management. This system implements industry best practices for security and user experience.

## Features Implemented

### ✅ 1. Secure Authentication System with Password Hashing
- **Argon2ID** password hashing (fallback to BCrypt)
- Strong password requirements with validation
- Protection against common passwords
- Secure password change functionality

### ✅ 2. Role-Based Access Control (RBAC)
- Flexible role and permission system
- User role assignments with expiration dates
- Permission-based access control
- Hierarchical role structure

### ✅ 3. User Session Management with Secure Tokens
- Secure session handling with anti-hijacking protection
- Session timeout management
- IP address and user agent verification
- CSRF token protection

### ✅ 4. Password Reset Functionality with Email Verification
- Secure token generation for password reset
- Email verification integration
- Time-limited reset tokens
- Rate limiting for security

### ✅ 5. Two-Factor Authentication (2FA)
- TOTP (Time-based One-Time Password) support
- Google Authenticator compatibility
- Backup codes generation
- QR code generation for easy setup

### ✅ 6. User Profile Management
- Comprehensive user profile system
- Profile data validation
- Language preferences
- Privacy settings

### ✅ 7. Admin Panel for User Administration
- User management interface
- Role assignment functionality
- Permission management
- System monitoring

## System Architecture

### Core Components

1. **AuthenticationManager** (`core/AuthenticationManager.php`)
   - Handles login, registration, and password management
   - Implements brute force protection
   - Integrates with 2FA system

2. **RBACManager** (`core/RBACManager.php`)
   - Manages roles and permissions
   - Handles user role assignments
   - Provides permission checking

3. **TwoFactorAuthManager** (`core/TwoFactorAuthManager.php`)
   - TOTP implementation
   - Backup codes management
   - QR code generation

4. **SessionManager** (`core/SessionManager.php`)
   - Secure session handling
   - Session hijacking protection
   - CSRF protection

5. **ProfileManager** (`core/ProfileManager.php`)
   - User profile operations
   - Profile data validation

6. **AdminPanel** (`core/AdminPanel.php`)
   - Administrative functions
   - User management interface

7. **EmailManager** (`core/EmailManager.php`)
   - Email notifications
   - Password reset emails
   - Security alerts

8. **PasswordResetManager** (`core/PasswordResetManager.php`)
   - Password reset token management
   - Email verification

## Database Schema

### Main Tables

1. **users_new** - Core user accounts
2. **user_profiles** - Extended user profile information
3. **roles** - System roles
4. **permissions** - System permissions
5. **user_roles** - User-role assignments
6. **role_permissions** - Role-permission assignments
7. **user_two_factor_auth** - 2FA settings
8. **password_resets** - Password reset tokens
9. **email_verifications** - Email verification tokens
10. **security_events** - Security audit log
11. **user_sessions** - Session tracking

### Default Roles

- **admin** - Full system access
- **moderator** - Content and user management
- **user** - Standard user permissions (default)
- **guest** - Limited access

### Permission Categories

- **user_management** - User administration
- **content** - Content management
- **system** - System administration
- **security** - Security management
- **profile** - Profile management

## Security Features

### Password Security
- Argon2ID hashing (64MB memory, 4 iterations, 3 threads)
- BCrypt fallback (cost factor 12)
- Password strength validation
- Common password protection

### Session Security
- Secure session configuration
- Session ID regeneration
- IP address tracking
- User agent verification
- CSRF token protection

### Brute Force Protection
- Failed login attempt tracking
- IP-based rate limiting
- Account lockout mechanism
- Security event logging

### Two-Factor Authentication
- TOTP algorithm (RFC 6238)
- 30-second time windows
- Base32 secret encoding
- Backup codes (10 per user)

## Usage Examples

### Basic Authentication

```php
// Initialize authentication manager
$authManager = new AuthenticationManager();

// Register new user
$result = $authManager->register([
    'username' => 'john_doe',
    'email' => 'john@example.com',
    'password' => 'SecurePassword123!',
    'profile' => [
        'first_name' => 'John',
        'last_name' => 'Doe'
    ]
]);

// Login user
$result = $authManager->authenticate('john@example.com', 'SecurePassword123!');

if ($result['success']) {
    $user = $result['user'];
    $token = $result['session_token'];
}
```

### Role Management

```php
// Initialize RBAC manager
$rbacManager = new RBACManager();

// Assign role to user
$rbacManager->assignRole($userId, 'moderator');

// Check user permission
if ($rbacManager->hasPermission($userId, 'user.edit')) {
    // User can edit other users
}

// Check user role
if ($rbacManager->hasRole($userId, 'admin')) {
    // User is an admin
}
```

### Two-Factor Authentication

```php
// Initialize 2FA manager
$twoFactorManager = new TwoFactorAuthManager();

// Generate secret for user
$secret = $twoFactorManager->generateSecret($userId);

// Generate QR code URL
$qrUrl = $twoFactorManager->generateQRCodeUrl($userId, $userEmail, $secret);

// Enable 2FA after verification
$twoFactorManager->enable2FA($userId, $verificationCode);

// Verify 2FA code during login
$isValid = $twoFactorManager->verify2FACode($userId, $code);
```

### Profile Management

```php
// Initialize profile manager
$profileManager = new ProfileManager();

// Get user profile
$profile = $profileManager->getProfile($userId);

// Update profile
$profileManager->updateProfile($userId, [
    'first_name' => 'John',
    'last_name' => 'Smith',
    'bio' => 'Software developer'
]);
```

## Installation Steps

1. **Database Setup**
   ```bash
   php database/migrate.php migrate
   ```

2. **Create Admin User**
   ```php
   $authManager = new AuthenticationManager();
   $rbacManager = new RBACManager();
   
   // Register admin user
   $result = $authManager->register([
       'username' => 'admin',
       'email' => 'admin@renaltales.com',
       'password' => 'SecureAdminPassword123!'
   ]);
   
   // Assign admin role
   $rbacManager->assignRole($result['user_id'], 'admin');
   ```

3. **Configure Email Settings**
   ```php
   $emailManager = new EmailManager([
       'smtp_host' => 'smtp.gmail.com',
       'smtp_port' => 587,
       'smtp_username' => 'your-email@gmail.com',
       'smtp_password' => 'your-app-password'
   ]);
   ```

## Security Considerations

### Production Deployment
1. Enable HTTPS for all authentication endpoints
2. Configure proper SMTP settings for email notifications
3. Set up regular security log monitoring
4. Implement rate limiting at the web server level
5. Use environment variables for sensitive configuration

### Monitoring
- Monitor failed login attempts
- Track suspicious IP addresses
- Alert on multiple failed 2FA attempts
- Log all administrative actions

## API Integration

The system provides a solid foundation for REST API authentication:

```php
// API authentication middleware
function authenticateAPI($token) {
    $authManager = new AuthenticationManager();
    $tokenData = $authManager->validateSessionToken($token);
    
    if (!$tokenData) {
        http_response_code(401);
        return false;
    }
    
    return $tokenData['user_id'];
}

// Permission check for API endpoints
function requirePermission($userId, $permission) {
    $rbacManager = new RBACManager();
    
    if (!$rbacManager->hasPermission($userId, $permission)) {
        http_response_code(403);
        exit('Access denied');
    }
}
```

## Testing

Run the test script to verify system functionality:

```bash
php test_user_management.php
```

## Configuration

### Email Settings
Configure email settings in your application configuration:

```php
return [
    'email' => [
        'from_email' => 'noreply@renaltales.com',
        'from_name' => 'RenalTales',
        'smtp_host' => 'localhost',
        'smtp_port' => 587,
        'smtp_username' => '',
        'smtp_password' => '',
        'smtp_secure' => 'tls'
    ]
];
```

### Security Settings
```php
return [
    'security' => [
        'max_login_attempts' => 5,
        'lockout_duration' => 1800, // 30 minutes
        'session_timeout' => 3600,  // 1 hour
        'password_reset_expiry' => 3600 // 1 hour
    ]
];
```

## Maintenance

### Regular Tasks
1. Clean expired password reset tokens
2. Clean expired email verification tokens
3. Archive old security events
4. Review and update user permissions

### Monitoring Queries
```sql
-- Check failed login attempts
SELECT COUNT(*) FROM security_events 
WHERE event_type = 'login_failure' 
AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR);

-- Check 2FA adoption rate
SELECT 
    COUNT(CASE WHEN is_enabled = 1 THEN 1 END) as enabled_2fa,
    COUNT(*) as total_users
FROM user_two_factor_auth;
```

## Support

For issues or questions regarding the user management system:

1. Check the error logs in `/logs/` directory
2. Review security events in the database
3. Verify email configuration for password reset issues
4. Check session configuration for login problems

## Conclusion

The Modern User Management System provides a robust, secure foundation for user authentication and authorization in the RenalTales application. It implements industry best practices and provides comprehensive security features while maintaining ease of use and scalability.
