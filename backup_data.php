<?php
/**
 * Backup current database data before Laravel migration
 */

try {
    $pdo = new PDO('mysql:host=localhost;dbname=renaltales;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $backupData = [];
    
    // Backup users
    echo "Backing up users...\n";
    $users = $pdo->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);
    $backupData['users'] = $users;
    echo "Backed up " . count($users) . " users\n";
    
    // Backup story categories
    echo "Backing up story categories...\n";
    $categories = $pdo->query("SELECT * FROM story_categories")->fetchAll(PDO::FETCH_ASSOC);
    $backupData['story_categories'] = $categories;
    echo "Backed up " . count($categories) . " categories\n";
    
    // Backup stories
    echo "Backing up stories...\n";
    $stories = $pdo->query("SELECT * FROM stories")->fetchAll(PDO::FETCH_ASSOC);
    $backupData['stories'] = $stories;
    echo "Backed up " . count($stories) . " stories\n";
    
    // Backup story contents
    echo "Backing up story contents...\n";
    $contents = $pdo->query("SELECT * FROM story_contents")->fetchAll(PDO::FETCH_ASSOC);
    $backupData['story_contents'] = $contents;
    echo "Backed up " . count($contents) . " story contents\n";
    
    // Backup story revisions
    echo "Backing up story revisions...\n";
    $revisions = $pdo->query("SELECT * FROM story_revisions")->fetchAll(PDO::FETCH_ASSOC);
    $backupData['story_revisions'] = $revisions;
    echo "Backed up " . count($revisions) . " revisions\n";
    
    // Save backup to JSON file
    $backupFile = 'renaltales_backup_' . date('Y-m-d_H-i-s') . '.json';
    file_put_contents($backupFile, json_encode($backupData, JSON_PRETTY_PRINT));
    echo "Data backed up to: $backupFile\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
