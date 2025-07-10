<?php

/**
 * Simple Login Test
 * Tests the authentication without sessions
 */

// Define constants
define('APP_DIR', __DIR__);
define('DEFAULT_LANGUAGE', 'sk');
define('LANGUAGE_PATH', APP_DIR . '/resources/lang/');
define('APP_TITLE', 'Renal Tales');
define('DEBUG_MODE', true);

require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/models/User.php';

try {
    echo "Testing user authentication...\n";
    
    // Test finding user by email
    $userModel = new User();
    $user = $userModel->findByEmail('test@example.com');
    
    if ($user) {
        echo "✓ User found:\n";
        echo "  ID: " . $user['id'] . "\n";
        echo "  Username: " . $user['username'] . "\n";
        echo "  Email: " . $user['email'] . "\n";
        echo "  Full Name: " . $user['full_name'] . "\n";
        echo "  Role: " . $user['role'] . "\n";
        
        // Test password verification
        $testPassword = 'password123';
        if (password_verify($testPassword, $user['password_hash'])) {
            echo "✓ Password verification successful\n";
        } else {
            echo "✗ Password verification failed\n";
        }
        
    } else {
        echo "✗ User not found\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nSimple authentication test completed!\n";

?>
