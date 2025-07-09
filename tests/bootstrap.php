<?php
/**
 * Test Bootstrap File
 * 
 * Initializes the testing environment and sets up necessary configurations
 * for running PHPUnit tests.
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

// Define test environment
define('APP_ENV', 'testing');
define('APP_ROOT', dirname(__DIR__));
define('TESTS_ROOT', __DIR__);

// Load Composer autoloader
require_once APP_ROOT . '/vendor/autoload.php';

// Load environment variables for testing
use Symfony\Component\Dotenv\Dotenv;

if (class_exists(Dotenv::class)) {
    $dotenv = new Dotenv();
    
    // Load main .env file first
    if (file_exists(APP_ROOT . '/.env')) {
        $dotenv->loadEnv(APP_ROOT . '/.env');
    }
    
    // Load test-specific .env file if exists
    if (file_exists(APP_ROOT . '/.env.testing')) {
        $dotenv->loadEnv(APP_ROOT . '/.env.testing');
    }
}

// Set timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'UTC');

// Set error reporting for testing
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Initialize testing database configuration
$_ENV['DB_DATABASE'] = $_ENV['DB_DATABASE'] ?? 'renaltales_test';
$_ENV['APP_ENV'] = 'testing';
$_ENV['APP_DEBUG'] = 'true';

// Create test directories if they don't exist
$testDirs = [
    'tests/coverage',
    'tests/results',
    'tests/fixtures',
    'tests/mocks',
    'storage/testing',
    'storage/testing/cache',
    'storage/testing/logs',
    'storage/testing/sessions',
    'storage/testing/uploads'
];

foreach ($testDirs as $dir) {
    $fullPath = APP_ROOT . '/' . $dir;
    if (!is_dir($fullPath)) {
        mkdir($fullPath, 0755, true);
    }
}

// Set up testing configuration
$GLOBALS['config'] = [
    'app' => [
        'name' => 'RenalTales Testing',
        'env' => 'testing',
        'debug' => true,
        'timezone' => $_ENV['APP_TIMEZONE'] ?? 'UTC',
    ],
    'database' => [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'name' => $_ENV['DB_DATABASE'] ?? 'renaltales_test',
        'user' => $_ENV['DB_USERNAME'] ?? 'root',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
        'collation' => $_ENV['DB_COLLATION'] ?? 'utf8mb4_unicode_ci',
    ],
    'security' => [
        'csrf_protection' => false, // Disabled for testing
        'rate_limiting' => false,   // Disabled for testing
        'session_encryption' => false,
    ],
    'cache' => [
        'driver' => 'array',
        'path' => APP_ROOT . '/storage/testing/cache',
    ],
    'session' => [
        'driver' => 'array',
        'path' => APP_ROOT . '/storage/testing/sessions',
    ],
    'logging' => [
        'path' => APP_ROOT . '/storage/testing/logs',
        'level' => 'debug',
    ],
    'mail' => [
        'driver' => 'log',
        'path' => APP_ROOT . '/storage/testing/logs/mail.log',
    ],
];

// Initialize logging for tests
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\TestHandler;

if (class_exists(Logger::class)) {
    $logger = new Logger('renaltales-test');
    $logger->pushHandler(new StreamHandler(APP_ROOT . '/storage/testing/logs/test.log', Logger::DEBUG));
    $logger->pushHandler(new TestHandler(Logger::DEBUG));
    
    $GLOBALS['logger'] = $logger;
}

// Helper function to get test database connection
function getTestDatabase() {
    static $testDb = null;
    
    if ($testDb === null) {
        require_once APP_ROOT . '/core/Database.php';
        $testDb = Database::getInstance();
    }
    
    return $testDb;
}

// Helper function to truncate test database
function truncateTestDatabase() {
    $db = getTestDatabase();
    
    // Get all tables
    $tables = $db->select("SHOW TABLES");
    $tableColumn = "Tables_in_" . $_ENV['DB_DATABASE'];
    
    // Disable foreign key checks
    $db->execute("SET FOREIGN_KEY_CHECKS = 0");
    
    // Truncate each table
    foreach ($tables as $table) {
        $tableName = $table[$tableColumn];
        if ($tableName !== 'database_migrations') {
            $db->execute("TRUNCATE TABLE `$tableName`");
        }
    }
    
    // Re-enable foreign key checks
    $db->execute("SET FOREIGN_KEY_CHECKS = 1");
}

// Helper function to seed test data
function seedTestData() {
    $db = getTestDatabase();
    
    // Create test user
    $db->execute("INSERT INTO users_new (username, email, password_hash, email_verified, status) VALUES (?, ?, ?, ?, ?)", [
        'test_user',
        'test@example.com',
        password_hash('password123', PASSWORD_DEFAULT),
        true,
        'active'
    ]);
    
    // Create test admin user
    $db->execute("INSERT INTO users_new (username, email, password_hash, email_verified, status) VALUES (?, ?, ?, ?, ?)", [
        'admin',
        'admin@example.com',
        password_hash('admin123', PASSWORD_DEFAULT),
        true,
        'active'
    ]);
}

// Helper function to create test fixtures
function createTestFixtures() {
    $fixturesDir = APP_ROOT . '/tests/fixtures';
    
    // Create test image
    $testImage = imagecreate(100, 100);
    $white = imagecolorallocate($testImage, 255, 255, 255);
    $black = imagecolorallocate($testImage, 0, 0, 0);
    imagestring($testImage, 5, 10, 10, 'TEST', $black);
    imagepng($testImage, $fixturesDir . '/test-image.png');
    imagedestroy($testImage);
    
    // Create test document
    file_put_contents($fixturesDir . '/test-document.txt', 'This is a test document for testing file uploads.');
}

// Initialize test environment
if (php_sapi_name() === 'cli') {
    echo "Initializing test environment...\n";
    
    // Create test fixtures
    createTestFixtures();
    
    echo "Test environment initialized successfully!\n";
}

// Custom assertion helpers
class TestAssertions {
    public static function assertDatabaseHas($table, $data) {
        $db = getTestDatabase();
        $conditions = [];
        $params = [];
        
        foreach ($data as $column => $value) {
            $conditions[] = "`$column` = ?";
            $params[] = $value;
        }
        
        $sql = "SELECT COUNT(*) as count FROM `$table` WHERE " . implode(' AND ', $conditions);
        $result = $db->selectOne($sql, $params);
        
        return $result['count'] > 0;
    }
    
    public static function assertDatabaseMissing($table, $data) {
        return !self::assertDatabaseHas($table, $data);
    }
    
    public static function assertApiResponse($response, $expectedStatus = 200) {
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response');
        }
        
        return $data;
    }
}

// Make assertions globally available
function assertDatabaseHas($table, $data) {
    return TestAssertions::assertDatabaseHas($table, $data);
}

function assertDatabaseMissing($table, $data) {
    return TestAssertions::assertDatabaseMissing($table, $data);
}

function assertApiResponse($response, $expectedStatus = 200) {
    return TestAssertions::assertApiResponse($response, $expectedStatus);
}

// Test completed indicator
if (php_sapi_name() === 'cli') {
    echo "Test bootstrap completed successfully!\n";
}
