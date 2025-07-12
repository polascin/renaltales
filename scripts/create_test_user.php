<?php

/**
 * Create Test User Script
 * Creates a test user for testing the login system
 */

require_once __DIR__ . '/core/Database.php';

try {
    echo "Checking database and creating test user...\n";
    
    $db = Database::getInstance();
    
    // Check if users table exists
    $result = $db->execute("SHOW TABLES LIKE 'users'");
    if ($result->rowCount() == 0) {
        echo "Creating users table...\n";
        
        $createUserTableSQL = "
        CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(255) UNIQUE NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            name VARCHAR(255) NOT NULL,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(50) DEFAULT 'user',
            is_active BOOLEAN DEFAULT TRUE,
            email_verified BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $db->execute($createUserTableSQL);
        echo "Users table created successfully!\n";
    }
    
    // Check if test user exists
    $existingUser = $db->selectOne("SELECT id FROM users WHERE email = ?", ['test@example.com']);
    
    if (!$existingUser) {
        echo "Creating test user...\n";
        
        // Create a test user with password 'password123'
        $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
        
        $insertUserSQL = "
        INSERT INTO users (username, email, full_name, password_hash, role, two_factor_enabled, email_verified_at, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW(), NOW())";
        
        $db->execute($insertUserSQL, [
            'testuser',
            'test@example.com',
            'Test User',
            $hashedPassword,
            'user',
            0
        ]);
        
        echo "Test user created successfully!\n";
        echo "Email: test@example.com\n";
        echo "Password: password123\n";
    } else {
        echo "Test user already exists!\n";
        echo "Email: test@example.com\n";
        echo "Password: password123\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

?>
