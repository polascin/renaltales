# Authentication Core Service

This directory contains a comprehensive authentication and security system for the RenalTales application. The Auth service provides secure password hashing, session management, login throttling, route protection middleware, and CSRF token generation/validation.

## Components

### 1. AuthService (`AuthService.php`)
The main authentication service that orchestrates all security features.

**Key Features:**
- Secure password hashing using PasswordLock
- User authentication with throttling protection
- Session management with security validation
- CSRF token generation and validation
- Route permission checking
- Data encryption/decryption
- 2FA support with backup codes
- Rate limiting
- Security event logging

**Main Methods:**
```php
// Authentication
$user = $authService->authenticate($email, $password, $ipAddress);
$sessionToken = $authService->createSession($user, $rememberMe);
$user = $authService->validateSession($sessionToken);

// Password Management
$hash = $authService->hashPassword($password);
$isValid = $authService->verifyPassword($password, $hash);

// CSRF Protection
$token = $authService->generateCSRFToken();
$isValid = $authService->validateCSRFToken($token);

// 2FA
$data = $authService->enable2FA($user);
$verified = $authService->verify2FA($user, $code);

// Security
$allowed = $authService->checkRateLimit($action, $identifier, $maxAttempts, $timeWindow);
$hasAccess = $authService->hasPermission($user, $route, $method);
```

### 2. SessionManager (`SessionManager.php`)
Handles secure session management with database storage.

**Features:**
- Database-stored sessions with encryption
- IP and User Agent validation
- Remember me functionality
- Session security validation
- Session cleanup and management
- Multiple active session support

**Usage:**
```php
$sessionManager = new SessionManager($config);
$token = $sessionManager->createSession($userId, $rememberMe);
$userId = $sessionManager->validateSession($token);
$sessionManager->destroySession($token);
```

### 3. LoginThrottling (`LoginThrottling.php`)
Protects against brute force attacks with intelligent throttling.

**Features:**
- IP-based and user-based throttling
- Progressive lockout duration
- Rate limiting for any action
- IP banning and whitelisting
- Attack statistics and monitoring
- Automatic cleanup of expired records

**Usage:**
```php
$throttling = new LoginThrottling($config);
$isThrottled = $throttling->isThrottled($ipAddress);
$throttling->recordFailedAttempt($email, $ipAddress);
$throttling->clearFailedAttempts($email, $ipAddress);
```

### 4. AuthMiddleware (`AuthMiddleware.php`)
Route protection middleware with comprehensive security features.

**Features:**
- Route-based authentication requirements
- Permission and role-based access control
- CSRF validation for state-changing requests
- 2FA enforcement for sensitive routes
- Rate limiting for API endpoints
- Security headers injection
- IP ban checking
- Security event logging

**Usage:**
```php
$middleware = new AuthMiddleware($authService, $config);
$response = $middleware->handle($request, $next);

// Or use static helpers
$protectedHandler = AuthMiddleware::requireAuth($handler);
$adminHandler = AuthMiddleware::requireRole('admin', $handler);
```

## Database Tables

The system requires several database tables:

1. **user_sessions** - Stores active user sessions
2. **login_attempts** - Tracks failed login attempts
3. **rate_limits** - Rate limiting records
4. **ip_bans** - Banned IP addresses
5. **ip_whitelist** - Whitelisted IP addresses
6. **user_2fa_backup_codes** - Encrypted 2FA backup codes
7. **password_reset_tokens** - Password reset tokens
8. **email_verification_tokens** - Email verification tokens
9. **api_tokens** - API authentication tokens
10. **security_events** - Security event logs

Run the migration: `database/migrations/004_create_auth_security_tables.sql`

## Configuration

Configure the security settings in `config/security.php`:

```php
return [
    'session_lifetime' => 3600,
    'max_login_attempts' => 5,
    'lockout_time' => 900,
    'api_rate_limit' => 100,
    'route_permissions' => [
        '/admin/*' => 'admin',
        '/stories/create' => 'create_stories',
        // ...
    ],
    'require_2fa_routes' => [
        '/admin/*',
        '/settings/security'
    ],
    // ... more settings
];
```

## Environment Variables

Add these to your `.env` file:

```env
ENCRYPTION_KEY=your-encryption-key-here
BACKUP_ENCRYPTION_KEY=your-backup-key-here
SECURITY_ALERT_EMAIL=admin@example.com
MAINTENANCE_SECRET=your-maintenance-secret
DISABLE_2FA=false
DISABLE_RATE_LIMITING=false
DISABLE_CSRF=false
```

## Security Features

### Password Security
- Uses PasswordLock library for secure hashing
- Enforces strong password requirements
- Automatic password rehashing when needed
- Protection against timing attacks

### Session Security
- Cryptographically secure session tokens
- IP and User Agent validation
- Session hijacking protection
- Automatic session cleanup
- Remember me functionality with extended lifetime

### Login Protection
- Intelligent brute force protection
- Progressive lockout duration
- IP and user-based throttling
- Attack statistics and monitoring
- Automatic IP banning for persistent attackers

### CSRF Protection
- Token-based CSRF protection
- Automatic token generation and validation
- Route-specific CSRF requirements
- Token lifetime management

### Route Protection
- Permission-based access control
- Role-based access control
- Route pattern matching
- 2FA enforcement for sensitive routes
- API rate limiting

### Data Protection
- Encryption for sensitive data
- Secure backup code storage
- Data integrity validation
- Secure random token generation

## Usage Examples

See `examples/AuthServiceExample.php` for comprehensive usage examples covering:

1. User authentication
2. Password hashing and verification
3. Session management
4. CSRF protection
5. Rate limiting
6. Two-factor authentication
7. Middleware usage
8. Data encryption
9. Security logging
10. Route permissions

## Security Best Practices

1. **Always use HTTPS** in production
2. **Set secure cookie flags** (Secure, HttpOnly, SameSite)
3. **Implement proper CSP headers**
4. **Regularly update dependencies**
5. **Monitor security logs**
6. **Use strong encryption keys**
7. **Implement proper error handling**
8. **Regular security audits**
9. **Keep backup codes secure**
10. **Implement proper session timeout**

## Error Handling

The service throws specific exceptions for different scenarios:

- `RuntimeException` - For authentication failures and security violations
- `InvalidArgumentException` - For invalid input parameters
- `Exception` - For general errors

Always wrap calls in try-catch blocks and handle errors appropriately.

## Performance Considerations

- Database indexes are optimized for security queries
- Session cleanup is automated
- Rate limiting uses efficient time-window queries
- Failed attempt records are automatically cleaned up
- Consider implementing Redis for session storage in high-traffic applications

## Testing

Unit tests should cover:

- Password hashing and verification
- Session creation and validation
- CSRF token generation and validation
- Rate limiting behavior
- Permission checking
- 2FA functionality
- Middleware behavior

## Monitoring and Alerting

The system logs security events that should be monitored:

- Failed login attempts
- Successful logins
- Password changes
- Permission escalations
- 2FA events
- Rate limit violations
- IP bans

Set up alerts for suspicious patterns and security violations.

## Compliance

This implementation helps with:

- **GDPR** - User data protection and right to erasure
- **OWASP Top 10** - Protection against common vulnerabilities
- **PCI DSS** - If handling payment data
- **SOC 2** - Security controls and monitoring

## Support

For questions or issues with the Auth service, please:

1. Check the examples in `examples/AuthServiceExample.php`
2. Review the configuration in `config/security.php`
3. Check the logs for detailed error information
4. Consult the security documentation
