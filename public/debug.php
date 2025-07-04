<?php
/**
 * Comprehensive Web Debug Script
 * Tests each component step by step to identify the exact failure point
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Start output buffering to capture any output before headers
ob_start();

echo "<h1>RenalTales Comprehensive Debug</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;}.success{color:green;}.error{color:red;}.warning{color:orange;}</style>";

$step = 1;

try {
    echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
    echo "<p><strong>Current Directory:</strong> " . __DIR__ . "</p>";
    echo "<p><strong>Parent Directory:</strong> " . dirname(__DIR__) . "</p>";
    
    // Check if ROOT_PATH can be defined
    if (!defined('ROOT_PATH')) {
        define('ROOT_PATH', dirname(__DIR__));
        echo "<p><strong>ROOT_PATH defined:</strong> " . ROOT_PATH . "</p>";
    } else {
        echo "<p><strong>ROOT_PATH already defined:</strong> " . ROOT_PATH . "</p>";
    }
    
    // Check bootstrap file exists
    $bootstrapPath = ROOT_PATH . '/bootstrap/autoload.php';
    if (file_exists($bootstrapPath)) {
        echo "<p><strong>Bootstrap file:</strong> EXISTS</p>";
        
        // Try to load bootstrap
        echo "<p>Loading bootstrap...</p>";
        require_once $bootstrapPath;
        echo "<p><strong>Bootstrap:</strong> LOADED</p>";
    } else {
        echo "<p><strong>Bootstrap file:</strong> NOT FOUND at $bootstrapPath</p>";
    }
    
    // Check config file
    $configPath = ROOT_PATH . '/config/config.php';
    if (file_exists($configPath)) {
        echo "<p><strong>Config file:</strong> EXISTS</p>";
        
        // Try to load config
        echo "<p>Loading config...</p>";
        $config = require_once $configPath;
        echo "<p><strong>Config:</strong> LOADED</p>";
        echo "<p><strong>App Name:</strong> " . ($config['app']['name'] ?? 'Not set') . "</p>";
        echo "<p><strong>Database Host:</strong> " . ($config['database']['host'] ?? 'Not set') . "</p>";
    } else {
        echo "<p><strong>Config file:</strong> NOT FOUND at $configPath</p>";
    }
    
    // Test session
    if (session_status() === PHP_SESSION_NONE) {
        echo "<p>Starting session...</p>";
        session_start();
        echo "<p><strong>Session:</strong> STARTED</p>";
    } else {
        echo "<p><strong>Session:</strong> ALREADY ACTIVE</p>";
    }
    
    // Check server variables
    echo "<h2>Server Variables</h2>";
    echo "<p><strong>REQUEST_METHOD:</strong> " . ($_SERVER['REQUEST_METHOD'] ?? 'Not set') . "</p>";
    echo "<p><strong>REQUEST_URI:</strong> " . ($_SERVER['REQUEST_URI'] ?? 'Not set') . "</p>";
    echo "<p><strong>HTTP_HOST:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'Not set') . "</p>";
    echo "<p><strong>SCRIPT_NAME:</strong> " . ($_SERVER['SCRIPT_NAME'] ?? 'Not set') . "</p>";
    
    // Test core classes if config loaded
    if (isset($config)) {
        echo "<h2>Core Classes Test</h2>";
        
        // Set globals
        $GLOBALS['CONFIG'] = $config;
        $GLOBALS['SUPPORTED_STORY_LANGUAGES'] = $config['story_languages'];
        echo "<p><strong>Globals:</strong> SET</p>";
        
        // Test each core file
        $coreFiles = [
            'Database' => ROOT_PATH . '/app/Core/Database.php',
            'Security' => ROOT_PATH . '/app/Core/Security.php',
            'Language' => ROOT_PATH . '/app/Core/Language.php',
            'Router' => ROOT_PATH . '/app/Core/Router.php',
            'Controller' => ROOT_PATH . '/app/Core/Controller.php'
        ];
        
        foreach ($coreFiles as $name => $path) {
            if (file_exists($path)) {
                try {
                    require_once $path;
                    echo "<p><strong>$name:</strong> LOADED</p>";
                } catch (Exception $e) {
                    echo "<p><strong>$name:</strong> ERROR - " . $e->getMessage() . "</p>";
                }
            } else {
                echo "<p><strong>$name:</strong> FILE NOT FOUND</p>";
            }
        }
        
        // Test class instantiation
        echo "<h2>Class Instantiation Test</h2>";
        try {
            $security = new Security();
            echo "<p><strong>Security:</strong> INSTANTIATED</p>";
        } catch (Exception $e) {
            echo "<p><strong>Security:</strong> FAILED - " . $e->getMessage() . "</p>";
        }
        
        try {
            $language = new Language();
            echo "<p><strong>Language:</strong> INSTANTIATED</p>";
        } catch (Exception $e) {
            echo "<p><strong>Language:</strong> FAILED - " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<h2>SUCCESS</h2>";
    echo "<p>All tests completed successfully!</p>";
    
} catch (Exception $e) {
    echo "<h2>EXCEPTION</h2>";
    echo "<p><strong>Message:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "<pre><strong>Trace:</strong>\n" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "<h2>FATAL ERROR</h2>";
    echo "<p><strong>Message:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "<pre><strong>Trace:</strong>\n" . $e->getTraceAsString() . "</pre>";
}

// Get any buffered output and display it
$output = ob_get_clean();
echo $output;
?>
