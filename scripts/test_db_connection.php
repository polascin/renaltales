<?php
/**
 * Simple database connection test
 */

// Database configuration
$config = [
    'host' => 'mariadb114.r6.websupport.sk',
    'port' => 3306,
    'database' => 'SvwfeoXW',
    'username' => 'by80b9pH',
    'password' => 'WsVZOl#;D07ju~0@_dF@',
    'charset' => 'utf8mb4',
];

try {
    // Create database connection
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
    
    echo "Attempting to connect to database...\n";
    echo "Host: {$config['host']}\n";
    echo "Port: {$config['port']}\n";
    echo "Database: {$config['database']}\n";
    echo "Username: {$config['username']}\n";
    echo "Password: " . str_repeat('*', strlen($config['password'])) . "\n\n";
    
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    echo "✅ Database connection successful!\n";
    
    // Test query
    $result = $pdo->query("SELECT COUNT(*) as count FROM languages");
    $row = $result->fetch();
    echo "📊 Languages table contains: {$row['count']} records\n";
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "📍 Error Code: " . $e->getCode() . "\n";
    exit(1);
}
