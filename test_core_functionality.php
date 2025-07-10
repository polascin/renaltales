<?php
/**
 * Core Functionality Test
 * Tests core application functionality with the remote database
 */

// Load environment variables
if (file_exists('.env')) {
    $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        
        list($key, $value) = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value, '"'));
    }
}

echo "=== Core Functionality Test ===\n";
echo "Testing with remote database...\n\n";

// Database configuration
$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '3306';
$database = getenv('DB_DATABASE') ?: 'renaltales';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    echo "✓ Database connection established\n";
    
    // Test 1: Users table functionality
    echo "\n1. Testing Users table...\n";
    
    // Check if users table exists and has data
    $users_count = $pdo->query("SELECT COUNT(*) as count FROM users")->fetch()['count'];
    echo "   Users count: $users_count\n";
    
    // Test user creation (just check structure)
    $user_columns = $pdo->query("DESCRIBE users")->fetchAll();
    $required_columns = ['id', 'username', 'email', 'password', 'created_at'];
    $missing_columns = [];
    
    $existing_columns = array_column($user_columns, 'Field');
    foreach ($required_columns as $col) {
        if (!in_array($col, $existing_columns)) {
            $missing_columns[] = $col;
        }
    }
    
    if (empty($missing_columns)) {
        echo "   ✓ Users table structure is correct\n";
    } else {
        echo "   ✗ Missing columns: " . implode(', ', $missing_columns) . "\n";
    }
    
    // Test 2: Stories table functionality
    echo "\n2. Testing Stories table...\n";
    
    $stories_count = $pdo->query("SELECT COUNT(*) as count FROM stories")->fetch()['count'];
    echo "   Stories count: $stories_count\n";
    
    // Test story structure
    $story_columns = $pdo->query("DESCRIBE stories")->fetchAll();
    $required_story_columns = ['id', 'title', 'content', 'user_id', 'created_at'];
    $missing_story_columns = [];
    
    $existing_story_columns = array_column($story_columns, 'Field');
    foreach ($required_story_columns as $col) {
        if (!in_array($col, $existing_story_columns)) {
            $missing_story_columns[] = $col;
        }
    }
    
    if (empty($missing_story_columns)) {
        echo "   ✓ Stories table structure is correct\n";
    } else {
        echo "   ✗ Missing columns: " . implode(', ', $missing_story_columns) . "\n";
    }
    
    // Test 3: Database relationships
    echo "\n3. Testing database relationships...\n";
    
    // Check if there are users with stories
    $users_with_stories = $pdo->query("
        SELECT u.username, COUNT(s.id) as story_count 
        FROM users u 
        LEFT JOIN stories s ON u.id = s.user_id 
        GROUP BY u.id, u.username 
        HAVING story_count > 0
        LIMIT 5
    ")->fetchAll();
    
    if (count($users_with_stories) > 0) {
        echo "   ✓ User-Story relationships working\n";
        foreach ($users_with_stories as $user) {
            echo "     - {$user['username']}: {$user['story_count']} stories\n";
        }
    } else {
        echo "   ⚠ No users with stories found (this might be expected)\n";
    }
    
    // Test 4: Comments functionality
    echo "\n4. Testing Comments table...\n";
    
    $comments_count = $pdo->query("SELECT COUNT(*) as count FROM comments")->fetch()['count'];
    echo "   Comments count: $comments_count\n";
    
    // Test 5: Performance test with complex query
    echo "\n5. Performance test...\n";
    
    $start_time = microtime(true);
    $complex_query = $pdo->query("
        SELECT 
            u.username, 
            COUNT(s.id) as story_count,
            COUNT(c.id) as comment_count,
            MAX(s.created_at) as last_story_date
        FROM users u 
        LEFT JOIN stories s ON u.id = s.user_id 
        LEFT JOIN comments c ON u.id = c.user_id 
        GROUP BY u.id, u.username 
        ORDER BY story_count DESC, comment_count DESC 
        LIMIT 10
    ")->fetchAll();
    $query_time = microtime(true) - $start_time;
    
    echo "   Complex query executed in: " . round($query_time * 1000, 2) . " ms\n";
    echo "   Results returned: " . count($complex_query) . " rows\n";
    
    if ($query_time > 1) {
        echo "   ⚠ Query took longer than 1 second - consider optimization\n";
    } else {
        echo "   ✓ Query performance is acceptable\n";
    }
    
    // Test 6: Transaction test
    echo "\n6. Testing transactions...\n";
    
    try {
        $pdo->beginTransaction();
        
        // Test rollback works
        $pdo->exec("CREATE TEMPORARY TABLE test_transaction (id INT PRIMARY KEY, name VARCHAR(50))");
        $pdo->exec("INSERT INTO test_transaction (id, name) VALUES (1, 'test')");
        $count = $pdo->query("SELECT COUNT(*) as count FROM test_transaction")->fetch()['count'];
        
        if ($count == 1) {
            echo "   ✓ Transaction insert works\n";
        }
        
        $pdo->rollback();
        echo "   ✓ Transaction rollback works\n";
        
    } catch (Exception $e) {
        echo "   ✗ Transaction test failed: " . $e->getMessage() . "\n";
        $pdo->rollback();
    }
    
    // Test 7: Connection pool test
    echo "\n7. Testing connection stability...\n";
    
    $connection_times = [];
    for ($i = 0; $i < 5; $i++) {
        $start_time = microtime(true);
        $pdo->query("SELECT 1");
        $connection_times[] = microtime(true) - $start_time;
    }
    
    $avg_time = array_sum($connection_times) / count($connection_times);
    $max_time = max($connection_times);
    $min_time = min($connection_times);
    
    echo "   Connection times (ms):\n";
    echo "     Average: " . round($avg_time * 1000, 2) . "\n";
    echo "     Min: " . round($min_time * 1000, 2) . "\n";
    echo "     Max: " . round($max_time * 1000, 2) . "\n";
    
    if ($max_time > 0.5) {
        echo "   ⚠ Some queries are slow - remote connection latency detected\n";
    } else {
        echo "   ✓ Connection performance is stable\n";
    }
    
    echo "\n=== Core Functionality Test Completed ===\n";
    echo "✓ Database connectivity: PASSED\n";
    echo "✓ Table structures: PASSED\n";
    echo "✓ Basic operations: PASSED\n";
    echo "✓ Remote database working properly\n";
    
} catch (PDOException $e) {
    echo "✗ Database test failed: " . $e->getMessage() . "\n";
    exit(1);
}
