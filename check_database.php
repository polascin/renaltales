<?php

/**
 * Check Database Structure Script
 * Shows the structure of existing tables
 */

require_once __DIR__ . '/core/Database.php';

try {
    echo "Checking database structure...\n";
    
    $db = Database::getInstance();
    
    // Show users table structure
    echo "\nUsers table structure:\n";
    $result = $db->execute("DESCRIBE users");
    
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
    
    // Show existing users
    echo "\nExisting users:\n";
    $users = $db->select("SELECT id, username, email, full_name, role FROM users LIMIT 5");
    
    foreach ($users as $user) {
        echo "- ID: " . $user['id'] . ", Username: " . $user['username'] . ", Email: " . $user['email'] . ", Full Name: " . $user['full_name'] . ", Role: " . $user['role'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

?>
