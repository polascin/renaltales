<?php
/**
 * Auth Service Usage Examples
 * 
 * This file demonstrates how to use the Auth service for various
 * authentication and security operations.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use RenalTales\Security\AuthService;
use RenalTales\Security\AuthMiddleware;
use RenalTales\Core\Config;
use RenalTales\Model\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// Initialize config and auth service
$config = new Config(__DIR__ . '/../config/config.php');
$authService = new AuthService($config);

/**
 * Example 1: User Authentication
 */
function loginExample(AuthService $authService): void
{
    $email = 'user@example.com';
    $password = 'SecurePassword123!';
    $ipAddress = '192.168.1.100';

    try {
        // Authenticate user
        $user = $authService->authenticate($email, $password, $ipAddress);
        
        if ($user) {
            echo "✓ User authenticated successfully: {$user->username}\n";
            
            // Create session
            $sessionToken = $authService->createSession($user, false);
            echo "✓ Session created: {$sessionToken}\n";
            
            // Set session cookie (in real application)
            // setcookie('session_token', $sessionToken, time() + 3600, '/', '', true, true);
            
        } else {
            echo "✗ Authentication failed\n";
        }
    } catch (Exception $e) {
        echo "✗ Error: {$e->getMessage()}\n";
    }
}

/**
 * Example 2: Password Hashing
 */
function passwordHashingExample(AuthService $authService): void
{
    $password = 'MySecurePassword123!';
    
    // Hash password
    $hashedPassword = $authService->hashPassword($password);
    echo "✓ Password hashed: {$hashedPassword}\n";
    
    // Verify password
    $isValid = $authService->verifyPassword($password, $hashedPassword);
    echo $isValid ? "✓ Password verification successful\n" : "✗ Password verification failed\n";
    
    // Check if password needs rehashing
    $needsRehash = $authService->needsRehash($hashedPassword);
    echo $needsRehash ? "⚠ Password needs rehashing\n" : "✓ Password hash is up to date\n";
}

/**
 * Example 3: Session Management
 */
function sessionManagementExample(AuthService $authService): void
{
    // Assuming we have a user
    $user = User::find(1);
    if (!$user) {
        echo "✗ User not found\n";
        return;
    }
    
    // Create session with remember me
    $sessionToken = $authService->createSession($user, true);
    echo "✓ Remember me session created: {$sessionToken}\n";
    
    // Validate session
    $validatedUser = $authService->validateSession($sessionToken);
    if ($validatedUser) {
        echo "✓ Session validated for user: {$validatedUser->username}\n";
    }
    
    // Get user sessions
    $sessions = $authService->getUserSessions($user->id);
    echo "✓ User has " . count($sessions) . " active sessions\n";
    
    // Destroy specific session
    $authService->destroySession($sessionToken);
    echo "✓ Session destroyed\n";
    
    // Destroy all user sessions
    $authService->destroyAllUserSessions($user->id);
    echo "✓ All user sessions destroyed\n";
}

/**
 * Example 4: CSRF Protection
 */
function csrfProtectionExample(AuthService $authService): void
{
    // Generate CSRF token
    $csrfToken = $authService->generateCSRFToken();
    echo "✓ CSRF token generated: {$csrfToken}\n";
    
    // Validate CSRF token
    $isValid = $authService->validateCSRFToken($csrfToken);
    echo $isValid ? "✓ CSRF token is valid\n" : "✗ CSRF token is invalid\n";
    
    // Get CSRF token HTML for forms
    $csrfHTML = $authService->getCSRFTokenHTML();
    echo "✓ CSRF HTML: {$csrfHTML}\n";
}

/**
 * Example 5: Rate Limiting
 */
function rateLimitingExample(AuthService $authService): void
{
    $action = 'api_request';
    $identifier = 'user:123';
    $maxAttempts = 5;
    $timeWindow = 3600; // 1 hour
    
    // Check rate limit
    $allowed = $authService->checkRateLimit($action, $identifier, $maxAttempts, $timeWindow);
    echo $allowed ? "✓ Request allowed\n" : "✗ Rate limit exceeded\n";
}

/**
 * Example 6: Two-Factor Authentication
 */
function twoFactorAuthExample(AuthService $authService): void
{
    $user = User::find(1);
    if (!$user) {
        echo "✗ User not found\n";
        return;
    }
    
    // Enable 2FA
    $twoFactorData = $authService->enable2FA($user);
    echo "✓ 2FA enabled\n";
    echo "Secret: {$twoFactorData['secret']}\n";
    echo "Backup codes: " . implode(', ', $twoFactorData['backup_codes']) . "\n";
    
    // Verify 2FA code (example with backup code)
    $backupCode = $twoFactorData['backup_codes'][0];
    $verified = $authService->verify2FA($user, $backupCode);
    echo $verified ? "✓ 2FA code verified\n" : "✗ 2FA code invalid\n";
}

/**
 * Example 7: Using Auth Middleware
 */
function middlewareExample(): void
{
    $config = new Config(__DIR__ . '/../config/config.php');
    $authService = new AuthService($config);
    $middleware = new AuthMiddleware($authService, $config);
    
    // Simulate a request
    $request = Request::createFromGlobals();
    
    // Define a simple controller
    $controller = function(Request $request): Response {
        $user = $request->attributes->get('user');
        $authenticated = $request->attributes->get('authenticated');
        
        $content = $authenticated 
            ? "Hello, {$user->username}!" 
            : "Please log in.";
            
        return new Response($content);
    };
    
    // Handle request through middleware
    try {
        $response = $middleware->handle($request, $controller);
        echo "✓ Middleware processed request\n";
        echo "Response: {$response->getContent()}\n";
    } catch (Exception $e) {
        echo "✗ Middleware error: {$e->getMessage()}\n";
    }
}

/**
 * Example 8: Encryption/Decryption
 */
function encryptionExample(AuthService $authService): void
{
    $sensitiveData = "This is sensitive information";
    
    try {
        // Encrypt data
        $encrypted = $authService->encrypt($sensitiveData);
        echo "✓ Data encrypted: {$encrypted}\n";
        
        // Decrypt data
        $decrypted = $authService->decrypt($encrypted);
        echo "✓ Data decrypted: {$decrypted}\n";
        
        // Verify decryption
        $match = ($sensitiveData === $decrypted);
        echo $match ? "✓ Encryption/decryption successful\n" : "✗ Encryption/decryption failed\n";
    } catch (Exception $e) {
        echo "✗ Encryption error: {$e->getMessage()}\n";
    }
}

/**
 * Example 9: Security Event Logging
 */
function securityLoggingExample(AuthService $authService): void
{
    $authService->logSecurityEvent('user_login', [
        'user_id' => 123,
        'ip_address' => '192.168.1.100',
        'user_agent' => 'Mozilla/5.0...',
        'success' => true
    ]);
    
    $authService->logSecurityEvent('failed_login_attempt', [
        'email' => 'test@example.com',
        'ip_address' => '192.168.1.200',
        'reason' => 'invalid_password'
    ]);
    
    echo "✓ Security events logged\n";
}

/**
 * Example 10: Route Permission Checking
 */
function routePermissionExample(AuthService $authService): void
{
    $user = User::find(1);
    $routes = [
        '/' => 'GET',
        '/admin/users' => 'GET',
        '/stories/create' => 'POST',
        '/api/admin/settings' => 'PUT'
    ];
    
    foreach ($routes as $route => $method) {
        $hasPermission = $authService->hasPermission($user, $route, $method);
        $status = $hasPermission ? "✓" : "✗";
        echo "{$status} {$method} {$route}\n";
    }
}

// Run examples
echo "=== Auth Service Examples ===\n\n";

echo "1. User Authentication:\n";
loginExample($authService);
echo "\n";

echo "2. Password Hashing:\n";
passwordHashingExample($authService);
echo "\n";

echo "3. Session Management:\n";
sessionManagementExample($authService);
echo "\n";

echo "4. CSRF Protection:\n";
csrfProtectionExample($authService);
echo "\n";

echo "5. Rate Limiting:\n";
rateLimitingExample($authService);
echo "\n";

echo "6. Two-Factor Authentication:\n";
twoFactorAuthExample($authService);
echo "\n";

echo "7. Auth Middleware:\n";
middlewareExample();
echo "\n";

echo "8. Encryption/Decryption:\n";
encryptionExample($authService);
echo "\n";

echo "9. Security Event Logging:\n";
securityLoggingExample($authService);
echo "\n";

echo "10. Route Permissions:\n";
routePermissionExample($authService);
echo "\n";

echo "=== Examples Complete ===\n";
