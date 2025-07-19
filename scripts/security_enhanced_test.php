<?php

declare(strict_types=1);

/**
 * Security Features Test Script
 *
 * This script demonstrates and tests the enhanced security features including
 * CSRF protection, XSS prevention, rate limiting, and Argon2 password hashing.
 *
 * @package RenalTales
 * @version 2025.v3.1.dev
 * @author Ľubomír Polaščín
 */

// Directory separator constant for cross-platform compatibility
define('DS', DIRECTORY_SEPARATOR);

// Include application constants definitions
require_once dirname(__DIR__) . '/config/constants.php';

// Include bootstrap for proper setup
require_once APP_ROOT . '/bootstrap.php';

use RenalTales\Core\SecurityManager;
use RenalTales\Core\SessionManager;
use RenalTales\Services\RateLimiterService;
use RenalTales\Services\PasswordHashingService;

echo "=== RenalTales Security Features Test ===\n\n";

// Load security configuration
$securityConfig = include APP_ROOT . '/config/security.php';

// Initialize services
$sessionManager = new SessionManager($securityConfig['session'] ?? []);
$rateLimiter = new RateLimiterService($securityConfig['rate_limiting'] ?? []);
$passwordHasher = new PasswordHashingService($securityConfig['password_hashing'] ?? []);

// Initialize security manager
$securityManager = new SecurityManager($sessionManager, $securityConfig);
$securityManager->setRateLimiter($rateLimiter);
$securityManager->setPasswordHasher($passwordHasher);

// Test 1: CSRF Protection
echo "1. Testing CSRF Protection\n";
echo "   - Generating CSRF token...\n";
$csrfToken = $securityManager->getCSRFToken();
echo "   - Token generated: " . substr($csrfToken, 0, 16) . "...\n";

echo "   - Validating token...\n";
$isValid = $securityManager->validateCSRFToken($csrfToken);
echo "   - Validation result: " . ($isValid ? "VALID" : "INVALID") . "\n";

echo "   - Testing invalid token...\n";
$invalidToken = $securityManager->validateCSRFToken('invalid_token');
echo "   - Invalid token result: " . ($invalidToken ? "VALID" : "INVALID") . "\n";

echo "   - CSRF token field: " . $securityManager->getCSRFTokenField() . "\n\n";

// Test 2: XSS Protection
echo "2. Testing XSS Protection\n";
$xssTests = [
    '<script>alert("XSS")</script>',
    '<img src="x" onerror="alert(1)">',
    'javascript:alert("XSS")',
    '<p>Normal text</p>',
    '<a href="https://example.com">Link</a>'
];

foreach ($xssTests as $test) {
    echo "   - Input: " . htmlspecialchars($test) . "\n";
    $sanitized = $securityManager->sanitizeInput($test);
    echo "   - Sanitized: " . htmlspecialchars($sanitized) . "\n";
    $isValid = $securityManager->validateInput($test);
    echo "   - Valid: " . ($isValid ? "YES" : "NO") . "\n\n";
}

// Test 3: Rate Limiting
echo "3. Testing Rate Limiting\n";
$testIP = '127.0.0.1';

echo "   - Testing login rate limit for IP: $testIP\n";
for ($i = 1; $i <= 7; $i++) {
    $allowed = !$securityManager->isRateLimited('login', $testIP);
    echo "   - Attempt $i: " . ($allowed ? "ALLOWED" : "BLOCKED") . "\n";

    if ($allowed) {
        $securityManager->recordRateLimitAttempt('login', $testIP);
    }

    $remaining = $securityManager->getRemainingAttempts('login', $testIP);
    echo "     Remaining attempts: $remaining\n";
}

echo "   - Time until reset: " . $securityManager->getTimeUntilReset('login', $testIP) . " seconds\n\n";

// Test 4: Password Hashing with Argon2
echo "4. Testing Password Hashing (Argon2)\n";
$testPasswords = [
    'password123',
    'StrongP@ssw0rd!',
    'MySecurePassword123!',
    'weak'
];

foreach ($testPasswords as $password) {
    echo "   - Testing password: $password\n";

    // Validate password requirements
    $isValid = $securityManager->validatePassword($password);
    echo "     Meets requirements: " . ($isValid ? "YES" : "NO") . "\n";

    if ($isValid) {
        try {
            // Hash the password
            $hash = $securityManager->hashPassword($password);
            echo "     Hash: " . substr($hash, 0, 50) . "...\n";

            // Verify the password
            $verified = $securityManager->verifyPassword($password, $hash);
            echo "     Verification: " . ($verified ? "SUCCESS" : "FAILED") . "\n";

            // Test wrong password
            $wrongVerified = $securityManager->verifyPassword('wrongpassword', $hash);
            echo "     Wrong password test: " . ($wrongVerified ? "FAILED" : "SUCCESS") . "\n";

        } catch (Exception $e) {
            echo "     Error: " . $e->getMessage() . "\n";
        }
    }

    // Calculate strength
    $strength = $securityManager->calculatePasswordStrength($password);
    echo "     Strength: $strength/100\n\n";
}

// Test 5: Password Requirements
echo "5. Password Requirements\n";
$requirements = $securityManager->getPasswordRequirements();
foreach ($requirements as $requirement) {
    echo "   - $requirement\n";
}
echo "\n";

// Test 6: Algorithm Information
echo "6. Algorithm Information\n";
$algoInfo = $passwordHasher->getAlgorithmInfo();
echo "   - Algorithm: " . $algoInfo['algorithm_name'] . "\n";
echo "   - Memory cost: " . $algoInfo['options']['memory_cost'] . " KB\n";
echo "   - Time cost: " . $algoInfo['options']['time_cost'] . " iterations\n";
echo "   - Threads: " . $algoInfo['options']['threads'] . "\n\n";

// Test 7: Performance Benchmark
echo "7. Performance Benchmark\n";
$benchmark = $passwordHasher->benchmarkPerformance();
echo "   - Algorithm: " . $benchmark['algorithm'] . "\n";
echo "   - Iterations: " . $benchmark['iterations'] . "\n";
echo "   - Average time: " . number_format($benchmark['average_time'], 4) . " seconds\n";
echo "   - Hashes per second: " . number_format($benchmark['hashes_per_second'], 2) . "\n\n";

// Test 8: Input Sanitization
echo "8. Input Sanitization\n";
$inputs = [
    'Normal text',
    '<script>alert("XSS")</script>',
    ['array' => ['nested' => '<script>alert("XSS")</script>']],
    123,
    true
];

foreach ($inputs as $input) {
    echo "   - Input: " . json_encode($input) . "\n";
    $sanitized = $securityManager->sanitizeInput($input);
    echo "   - Sanitized: " . json_encode($sanitized) . "\n\n";
}

// Test 9: Token Generation
echo "9. Token Generation\n";
$token = $securityManager->generateSecureToken();
echo "   - Generated token: " . $token . "\n";

$nonce = $securityManager->generateCSPNonce();
echo "   - Generated CSP nonce: " . $nonce . "\n\n";

// Test 10: Rate Limit Cleanup
echo "10. Rate Limit Cleanup\n";
echo "   - Performing cleanup...\n";
$rateLimiter->cleanup();
echo "   - Cleanup completed\n\n";

// Test 11: Legacy Password Migration
echo "11. Legacy Password Migration\n";
$legacyPassword = 'testpassword123';
$bcryptHash = password_hash($legacyPassword, PASSWORD_BCRYPT);
echo "   - Legacy bcrypt hash: " . substr($bcryptHash, 0, 30) . "...\n";

$migratedHash = $passwordHasher->migrateLegacyPassword($legacyPassword, $bcryptHash);
if ($migratedHash) {
    echo "   - Migrated to Argon2: " . substr($migratedHash, 0, 30) . "...\n";
    echo "   - Migration: SUCCESS\n";
} else {
    echo "   - Migration: FAILED\n";
}

echo "\n=== Security Test Complete ===\n";
echo "All security features have been tested.\n";
echo "Check the logs directory for security event logs.\n";
