<?php
declare(strict_types=1);

/**
 * PHPUnit Bootstrap File
 * Initializes the test environment for RenalTales application
 */

// Error reporting for testing
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Define test environment
define('TESTING', true);
define('APP_ENV', 'testing');

// Set root path
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', ROOT_PATH . '/config');

// Load Composer autoloader
require_once ROOT_PATH . '/vendor/autoload.php';

// Load environment variables for testing
$dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
$dotenv->load();

// Override some environment variables for testing
$_ENV['APP_ENV'] = 'testing';
$_ENV['DB_NAME'] = $_ENV['DB_NAME'] ?? 'renaltales_test';
$_ENV['APP_DEBUG'] = 'true';

// Load application bootstrap
require_once ROOT_PATH . '/bootstrap/autoload.php';

// Load configuration
require_once CONFIG_PATH . '/config.php';

// Initialize test database connection
try {
    $testDbConfig = [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'database' => $_ENV['DB_NAME'] ?? 'renaltales_test',
        'username' => $_ENV['DB_USER'] ?? 'root',
        'password' => $_ENV['DB_PASS'] ?? '',
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    ];
    
    // Set global test database configuration
    $GLOBALS['test_db_config'] = $testDbConfig;
    
} catch (Exception $e) {
    echo "Warning: Could not initialize test database: " . $e->getMessage() . "\n";
}

// Set up test helpers
class TestHelper
{
    private static ?PDO $testDb = null;
    
    public static function getTestDatabase(): PDO
    {
        if (self::$testDb === null) {
            $config = $GLOBALS['test_db_config'];
            $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
            self::$testDb = new PDO($dsn, $config['username'], $config['password'], $config['options']);
        }
        
        return self::$testDb;
    }
    
    public static function resetDatabase(): void
    {
        $db = self::getTestDatabase();
        
        // Disable foreign key checks
        $db->exec('SET FOREIGN_KEY_CHECKS = 0');
        
        // Get all tables
        $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        
        // Truncate all tables
        foreach ($tables as $table) {
            $db->exec("TRUNCATE TABLE `{$table}`");
        }
        
        // Re-enable foreign key checks
        $db->exec('SET FOREIGN_KEY_CHECKS = 1');
    }
    
    public static function createTestUser(array $userData = []): array
    {
        $defaultData = [
            'username' => 'testuser_' . uniqid(),
            'email' => 'test' . uniqid() . '@example.com',
            'password_hash' => password_hash('password123', PASSWORD_ARGON2ID),
            'full_name' => 'Test User',
            'role' => 'user',
            'language_preference' => 'en',
            'email_verified_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $userData = array_merge($defaultData, $userData);
        
        $db = self::getTestDatabase();
        $fields = implode(', ', array_keys($userData));
        $placeholders = implode(', ', array_fill(0, count($userData), '?'));
        
        $stmt = $db->prepare("INSERT INTO users ({$fields}) VALUES ({$placeholders})");
        $stmt->execute(array_values($userData));
        
        $userData['id'] = (int) $db->lastInsertId();
        
        return $userData;
    }
    
    public static function createTestStory(int $userId, array $storyData = []): array
    {
        $defaultData = [
            'user_id' => $userId,
            'category_id' => 1,
            'original_language' => 'en',
            'status' => 'draft',
            'access_level' => 'public',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $storyData = array_merge($defaultData, $storyData);
        
        $db = self::getTestDatabase();
        $fields = implode(', ', array_keys($storyData));
        $placeholders = implode(', ', array_fill(0, count($storyData), '?'));
        
        $stmt = $db->prepare("INSERT INTO stories ({$fields}) VALUES ({$placeholders})");
        $stmt->execute(array_values($storyData));
        
        $storyData['id'] = (int) $db->lastInsertId();
        
        return $storyData;
    }
}

// Create required directories for test results
$testDirs = [
    ROOT_PATH . '/tests/coverage',
    ROOT_PATH . '/tests/coverage/html',
    ROOT_PATH . '/tests/results'
];

foreach ($testDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

echo "PHPUnit bootstrap completed successfully.\n";
