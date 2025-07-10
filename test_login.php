<?php

/**
 * Test Login Functionality
 * Tests the login system with the test user
 */

// Define constants
define('APP_DIR', __DIR__);
define('DEFAULT_LANGUAGE', 'sk');
define('LANGUAGE_PATH', APP_DIR . '/resources/lang/');
define('APP_TITLE', 'Renal Tales');
define('DEBUG_MODE', true);

require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/SessionManager.php';
require_once __DIR__ . '/core/AuthenticationManager.php';
require_once __DIR__ . '/models/LanguageModel.php';

try {
    echo "Testing login functionality...\n";
    
    // Initialize dependencies
    $languageModel = new LanguageModel();
    $sessionManager = new SessionManager($languageModel->getAllTexts(), true);
    $authManager = new AuthenticationManager($sessionManager);
    
    // Test credentials
    $email = 'test@example.com';
    $password = 'password123';
    
    echo "Testing with credentials: $email / $password\n";
    
    // Attempt authentication
    $result = $authManager->authenticate($email, $password, '127.0.0.1');
    
    if ($result['success']) {
        echo "✓ Authentication successful!\n";
        echo "  User ID: " . $result['user']['id'] . "\n";
        echo "  Username: " . $result['user']['username'] . "\n";
        echo "  Email: " . $result['user']['email'] . "\n";
        echo "  Full Name: " . $result['user']['full_name'] . "\n";
        echo "  Role: " . $result['user']['role'] . "\n";
        
        // Test if user is authenticated
        if ($authManager->isAuthenticated()) {
            echo "✓ User is authenticated\n";
        } else {
            echo "✗ User is not authenticated\n";
        }
        
        // Test getCurrentUser
        $currentUser = $authManager->getCurrentUser();
        if ($currentUser) {
            echo "✓ getCurrentUser() works\n";
            echo "  Current user: " . $currentUser['username'] . "\n";
        } else {
            echo "✗ getCurrentUser() failed\n";
        }
        
    } else {
        echo "✗ Authentication failed: " . $result['message'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nLogin test completed!\n";

?>
