<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=renaltales;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Users table structure:\n";
    $result = $pdo->query('DESCRIBE users');
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . ' - ' . $row['Type'] . "\n";
    }
    
    echo "\nStories table structure:\n";
    $result = $pdo->query('DESCRIBE stories');
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . ' - ' . $row['Type'] . "\n";
    }
    
    echo "\nStory contents table structure:\n";
    $result = $pdo->query('DESCRIBE story_contents');
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . ' - ' . $row['Type'] . "\n";
    }
    
    echo "\nExisting users:\n";
    $result = $pdo->query('SELECT id, username, email, role, email_verified_at FROM users LIMIT 10');
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo $row['id'] . ' - ' . $row['username'] . ' (' . $row['email'] . ') - Role: ' . $row['role'] . ' - Verified: ' . ($row['email_verified_at'] ? 'Yes' : 'No') . "\n";
    }
    
    echo "\nExisting stories:\n";
    $result = $pdo->query('SELECT id, user_id, status, access_level, created_at FROM stories LIMIT 5');
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "Story ID: {$row['id']}, User: {$row['user_id']}, Status: {$row['status']}, Access: {$row['access_level']}, Created: {$row['created_at']}\n";
    }
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
?>
