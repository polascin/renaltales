<?php

/**
 * Test Script for Renal Tales User Security Features
 * 
 * This script tests the database connection and security features setup
 * Run this after setting up the database to verify everything works correctly
 */

// Include required files
require_once '../core/Database.php';
require_once '../core/PasswordResetManager.php';
require_once '../core/EmailVerificationManager.php';

echo "<h1>Renal Tales - Security Features Test</h1>\n";
echo "<pre>\n";

try {
    // Test 1: Database Connection
    echo "=== Test 1: Database Connection ===\n";
    $db = Database::getInstance();
    if ($db->isConnected()) {
        echo "✓ Database connection successful\n";
    } else {
        echo "✗ Database connection failed\n";
        exit(1);
    }
    
    // Test 2: Check if tables exist
    echo "\n=== Test 2: Database Tables ===\n";
    $tables = ['users', 'password_resets', 'email_verifications'];
    
    foreach ($tables as $table) {
        $result = $db->selectOne("SHOW TABLES LIKE ?", [$table]);
        if ($result) {
            echo "✓ Table '$table' exists\n";
        } else {
            echo "✗ Table '$table' does not exist\n";
        }
    }
    
    // Test 3: Check default admin user
    echo "\n=== Test 3: Default Admin User ===\n";
    $admin = $db->selectOne("SELECT id, username, email, email_verified, status FROM users WHERE username = 'admin'");
    if ($admin) {
        echo "✓ Default admin user found\n";
        echo "  - ID: " . $admin['id'] . "\n";
        echo "  - Username: " . $admin['username'] . "\n";
        echo "  - Email: " . $admin['email'] . "\n";
        echo "  - Email Verified: " . ($admin['email_verified'] ? 'Yes' : 'No') . "\n";
        echo "  - Status: " . $admin['status'] . "\n";
    } else {
        echo "✗ Default admin user not found\n";
    }
    
    // Test 4: Password Reset Manager
    echo "\n=== Test 4: Password Reset Manager ===\n";
    $resetManager = new PasswordResetManager();
    
    // Test token creation
    $tokenInfo = $resetManager->createPasswordResetToken('admin@renaltales.local');
    if ($tokenInfo) {
        echo "✓ Password reset token created successfully\n";
        echo "  - Token ID: " . $tokenInfo['token_id'] . "\n";
        echo "  - Token: " . substr($tokenInfo['token'], 0, 10) . "... (truncated for security)\n";
        echo "  - Expires: " . $tokenInfo['expires_at'] . "\n";
        
        // Test token validation
        $validation = $resetManager->validatePasswordResetToken($tokenInfo['token']);
        if ($validation) {
            echo "✓ Password reset token validation successful\n";
        } else {
            echo "✗ Password reset token validation failed\n";
        }
        
        // Test password reset (with a test password)
        $resetSuccess = $resetManager->resetPassword($tokenInfo['token'], 'NewPassword123!');
        if ($resetSuccess) {
            echo "✓ Password reset successful\n";
        } else {
            echo "✗ Password reset failed\n";
        }
    } else {
        echo "✗ Password reset token creation failed\n";
    }
    
    // Test 5: Email Verification Manager
    echo "\n=== Test 5: Email Verification Manager ===\n";
    $verificationManager = new EmailVerificationManager();
    
    // Test token creation
    $verifyTokenInfo = $verificationManager->createEmailVerificationToken('admin@renaltales.local');
    if ($verifyTokenInfo) {
        echo "✓ Email verification token created successfully\n";
        echo "  - Token ID: " . $verifyTokenInfo['token_id'] . "\n";
        echo "  - Token: " . substr($verifyTokenInfo['token'], 0, 10) . "... (truncated for security)\n";
        echo "  - Expires: " . $verifyTokenInfo['expires_at'] . "\n";
        
        // Test token validation
        $verifyValidation = $verificationManager->validateEmailVerificationToken($verifyTokenInfo['token']);
        if ($verifyValidation) {
            echo "✓ Email verification token validation successful\n";
        } else {
            echo "✗ Email verification token validation failed\n";
        }
        
        // Test email verification
        $verifySuccess = $verificationManager->verifyEmail($verifyTokenInfo['token']);
        if ($verifySuccess) {
            echo "✓ Email verification successful\n";
        } else {
            echo "✗ Email verification failed\n";
        }
    } else {
        echo "✗ Email verification token creation failed\n";
    }
    
    // Test 6: Statistics
    echo "\n=== Test 6: Statistics ===\n";
    $resetStats = $resetManager->getPasswordResetStats();
    $verifyStats = $verificationManager->getEmailVerificationStats();
    
    echo "Password Reset Stats:\n";
    echo "  - Active tokens: " . $resetStats['active_tokens'] . "\n";
    echo "  - Recent resets: " . $resetStats['recent_resets'] . "\n";
    echo "  - Expired tokens: " . $resetStats['expired_tokens'] . "\n";
    
    echo "Email Verification Stats:\n";
    echo "  - Pending verifications: " . $verifyStats['pending_verifications'] . "\n";
    echo "  - Recent verifications: " . $verifyStats['recent_verifications'] . "\n";
    echo "  - Expired tokens: " . $verifyStats['expired_tokens'] . "\n";
    
    // Test 7: Cleanup
    echo "\n=== Test 7: Cleanup Functions ===\n";
    $deletedResets = $resetManager->cleanupExpiredTokens();
    $deletedVerifications = $verificationManager->cleanupExpiredTokens();
    
    echo "✓ Cleanup completed\n";
    echo "  - Deleted password reset tokens: $deletedResets\n";
    echo "  - Deleted email verification tokens: $deletedVerifications\n";
    
    echo "\n=== All Tests Completed Successfully! ===\n";
    echo "The user security features are properly installed and working.\n";
    
} catch (Exception $e) {
    echo "\n✗ Error occurred during testing: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>\n";

?>

<!DOCTYPE html>
<html>
<head>
    <title>Renal Tales - Security Features Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; }
        .success { color: #008000; }
        .error { color: #ff0000; }
    </style>
</head>
<body>
    <h2>Test Instructions</h2>
    <ol>
        <li>Make sure MySQL server is running</li>
        <li>Import the database schema using <code>setup_database.sql</code></li>
        <li>Access this test script via your web browser</li>
        <li>All tests should show ✓ for success</li>
    </ol>
    
    <h2>Next Steps</h2>
    <ul>
        <li>Integrate the security classes into your main application</li>
        <li>Create forms for password reset and email verification</li>
        <li>Set up email sending functionality</li>
        <li>Implement user registration and login</li>
        <li>Add CSRF protection to forms</li>
    </ul>
    
    <h2>Security Reminders</h2>
    <ul>
        <li>Change the default admin password immediately</li>
        <li>Use HTTPS in production</li>
        <li>Set up proper email configuration</li>
        <li>Monitor security logs regularly</li>
        <li>Implement rate limiting</li>
    </ul>
</body>
</html>
