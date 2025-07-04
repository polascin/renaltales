<?php
/**
 * Simple test page to verify application setup
 */

echo "<h1>RenalTales Setup Test</h1>";

// Test basic PHP functionality
echo "<h2>PHP Version: " . PHP_VERSION . "</h2>";

// Test if we can access the parent directory
$rootPath = dirname(__DIR__);
echo "<p><strong>Root Path:</strong> " . $rootPath . "</p>";

// Test if config files exist
$configPath = $rootPath . '/config/config.php';
echo "<p><strong>Config file exists:</strong> " . (file_exists($configPath) ? "✅ Yes" : "❌ No") . "</p>";

$envPath = $rootPath . '/.env';
echo "<p><strong>.env file exists:</strong> " . (file_exists($envPath) ? "✅ Yes" : "❌ No") . "</p>";

// Test autoloader
$autoloadPath = $rootPath . '/bootstrap/autoload.php';
echo "<p><strong>Autoloader exists:</strong> " . (file_exists($autoloadPath) ? "✅ Yes" : "❌ No") . "</p>";

// Test if we can load environment variables
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
    echo "<p><strong>Environment loaded:</strong> ✅ Yes</p>";
    
    // Test database connection
    try {
        if (class_exists('PDO')) {
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $database = $_ENV['DB_DATABASE'] ?? 'renaltales';
            $username = $_ENV['DB_USERNAME'] ?? 'root';
            $password = $_ENV['DB_PASSWORD'] ?? '';
            
            $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
            echo "<p><strong>Database connection:</strong> ✅ Success</p>";
        } else {
            echo "<p><strong>Database connection:</strong> ❌ PDO not available</p>";
        }
    } catch (Exception $e) {
        echo "<p><strong>Database connection:</strong> ❌ " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p><strong>Environment loaded:</strong> ❌ No</p>";
}

echo "<hr>";
echo "<p><strong>Next steps:</strong></p>";
echo "<ul>";
echo "<li>Access the main application at: <a href='http://renaltales.test'>http://renaltales.test</a> or <a href='http://localhost/renaltales/'>http://localhost/renaltales/</a></li>";
echo "<li>If you see a 404 error, the .htaccess rules are working correctly</li>";
echo "<li>If you see any other errors, check the error log at storage/logs/</li>";
echo "</ul>";
?>
