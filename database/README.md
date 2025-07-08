# Renal Tales - User Security Features Setup

This document provides instructions for setting up the user security features including password reset and email verification functionality.

## Database Setup

### Prerequisites
- MySQL/MariaDB server running on localhost
- Database: `renaltales`
- Username: `root`
- Password: (empty for local development)

### Installation Steps

1. **Create the database and tables:**
   ```sql
   mysql -u root -p < setup_database.sql
   ```

   Or import through phpMyAdmin or your preferred MySQL client.

2. **Verify the installation:**
   Check that the following tables were created:
   - `users`
   - `password_resets`
   - `email_verifications`
   - `user_sessions` (optional)
   - `security_audit_log` (optional)

## Table Descriptions

### Users Table (`users`)
Contains user account information with the following key fields:
- `id`: Primary key
- `username`: Unique username
- `email`: Unique email address
- `password_hash`: Hashed password using PHP's password_hash()
- `email_verified`: Boolean flag for email verification status
- `email_verified_at`: Timestamp when email was verified
- `status`: User status (active, inactive, suspended)

### Password Resets Table (`password_resets`)
Manages password reset tokens with security features:
- `id`: Primary key
- `user_id`: Foreign key to users table
- `token`: Secure random token (64 characters)
- `token_hash`: SHA-256 hash of the token for secure storage
- `expires_at`: Token expiration timestamp (1 hour default)
- `is_used`: Boolean flag to prevent token reuse
- `ip_address` & `user_agent`: Security tracking fields

### Email Verifications Table (`email_verifications`)
Handles email verification tokens:
- `id`: Primary key
- `user_id`: Foreign key to users table
- `token`: Secure random token (64 characters)
- `token_hash`: SHA-256 hash of the token
- `expires_at`: Token expiration timestamp (24 hours default)
- `is_verified`: Boolean flag for verification status
- `verification_type`: Type of verification (registration, email_change)

## PHP Classes

### Database.php
Singleton database connection class providing:
- Secure PDO connection with prepared statements
- Transaction support
- Error handling and logging

### PasswordResetManager.php
Manages password reset functionality:
- `createPasswordResetToken($email)`: Generate reset token
- `validatePasswordResetToken($token)`: Validate token
- `resetPassword($token, $newPassword)`: Reset password
- `cleanupExpiredTokens()`: Remove expired tokens

### EmailVerificationManager.php
Manages email verification:
- `createEmailVerificationToken($email)`: Generate verification token
- `validateEmailVerificationToken($token)`: Validate token
- `verifyEmail($token)`: Mark email as verified
- `cleanupExpiredTokens()`: Remove expired tokens

## Security Features

### Token Security
- 64-character random tokens using `random_bytes()`
- SHA-256 hashing for secure storage
- Automatic expiration (1 hour for password reset, 24 hours for email verification)
- One-time use enforcement
- Rate limiting to prevent abuse

### Password Security
- Password strength validation (minimum 8 characters, uppercase, lowercase, digits, special characters)
- PHP's `password_hash()` with default algorithm
- Automatic invalidation of all user tokens after password reset

### Audit Trail
- IP address and user agent tracking
- Comprehensive logging of security events
- Automatic cleanup of old audit logs

## Usage Examples

### Password Reset Flow
```php
require_once 'core/PasswordResetManager.php';

$resetManager = new PasswordResetManager();

// 1. Create reset token
$tokenInfo = $resetManager->createPasswordResetToken('user@example.com');
if ($tokenInfo) {
    // Send email with token to user
    $resetLink = "https://yoursite.com/reset-password?token=" . $tokenInfo['token'];
    // Send $resetLink via email
}

// 2. Validate and reset password
if (isset($_POST['token']) && isset($_POST['new_password'])) {
    $success = $resetManager->resetPassword($_POST['token'], $_POST['new_password']);
    if ($success) {
        echo "Password reset successful!";
    } else {
        echo "Password reset failed. Invalid or expired token.";
    }
}
```

### Email Verification Flow
```php
require_once 'core/EmailVerificationManager.php';

$verificationManager = new EmailVerificationManager();

// 1. Create verification token
$tokenInfo = $verificationManager->createEmailVerificationToken('user@example.com');
if ($tokenInfo) {
    // Send verification email
    $verifyLink = "https://yoursite.com/verify-email?token=" . $tokenInfo['token'];
    // Send $verifyLink via email
}

// 2. Verify email
if (isset($_GET['token'])) {
    $success = $verificationManager->verifyEmail($_GET['token']);
    if ($success) {
        echo "Email verified successfully!";
    } else {
        echo "Email verification failed. Invalid or expired token.";
    }
}
```

## Maintenance

### Automatic Cleanup
The database includes stored procedures and events for automatic cleanup:
- Daily cleanup of expired tokens
- Weekly cleanup of old audit logs

### Manual Cleanup
```php
// Clean up expired password reset tokens
$resetManager = new PasswordResetManager();
$deleted = $resetManager->cleanupExpiredTokens();
echo "Deleted $deleted expired password reset tokens";

// Clean up expired email verification tokens
$verificationManager = new EmailVerificationManager();
$deleted = $verificationManager->cleanupExpiredTokens();
echo "Deleted $deleted expired email verification tokens";
```

## Configuration

### Token Expiry Times
You can modify the token expiry times in the respective manager classes:
- `PasswordResetManager`: `$tokenExpiry = 3600` (1 hour)
- `EmailVerificationManager`: `$tokenExpiry = 86400` (24 hours)

### Database Configuration
Update the database connection details in `Database.php` if needed:
```php
$this->host = 'localhost';
$this->database = 'renaltales';
$this->username = 'root';
$this->password = '';
```

## Integration with Existing Application

To integrate with the existing Renal Tales application, add the following to `public/index.php`:

```php
// Add after other require_once statements
require_once APP_DIR . '/core/Database.php';
require_once APP_DIR . '/core/PasswordResetManager.php';
require_once APP_DIR . '/core/EmailVerificationManager.php';
```

## Default Admin User

The setup script creates a default admin user:
- Username: `admin`
- Email: `admin@renaltales.local`
- Password: `admin123`

**Important:** Change this password immediately in production!

## Testing

You can test the database setup using the following SQL commands:

```sql
-- Check tables were created
SHOW TABLES;

-- Verify default admin user
SELECT * FROM users WHERE username = 'admin';

-- Test token creation (manual insert for testing)
INSERT INTO password_resets (user_id, email, token, token_hash, expires_at) 
VALUES (1, 'admin@renaltales.local', 'test_token', SHA2('test_token', 256), DATE_ADD(NOW(), INTERVAL 1 HOUR));
```

## Security Considerations

1. **Always use HTTPS** for password reset and email verification links
2. **Implement rate limiting** at the application level
3. **Use proper email security** (SPF, DKIM, DMARC)
4. **Monitor security logs** for suspicious activity
5. **Regular security audits** of user accounts and tokens
6. **Backup strategy** for user data and audit logs

## Troubleshooting

### Common Issues

1. **Database connection failed**: Check MySQL service and credentials
2. **Tables not created**: Check MySQL user permissions
3. **Token validation fails**: Verify token expiry and database constraints
4. **Email not sending**: Check email configuration and logs

### Debug Mode
Enable debug mode in the application to see detailed error messages:
```php
define('DEBUG_MODE', true);
```

For production environments, always set `DEBUG_MODE` to `false` and monitor error logs.
