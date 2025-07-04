<?php
/**
 * Simple Test - Minimal Application Test
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Simple Application Test</h1>";

try {
    // Start session
    session_start();
    echo "<p>✅ Session started</p>";
    
    // Define constants
    if (!defined('ROOT_PATH')) {
        define('ROOT_PATH', dirname(__DIR__));
    }
    define('APP_PATH', ROOT_PATH . '/app');
    define('CONFIG_PATH', ROOT_PATH . '/config');
    echo "<p>✅ Constants defined</p>";
    
    // Load bootstrap
    require_once ROOT_PATH . '/bootstrap/autoload.php';
    echo "<p>✅ Bootstrap loaded</p>";
    
    // Load config
    $config = require_once CONFIG_PATH . '/config.php';
    echo "<p>✅ Config loaded</p>";
    
    // Set essential globals
    $GLOBALS['CONFIG'] = $config;
    $GLOBALS['SUPPORTED_STORY_LANGUAGES'] = $config['story_languages'];
    echo "<p>✅ Globals set</p>";
    
    // Load only essential core files
    require_once APP_PATH . '/Core/Database.php';
    require_once APP_PATH . '/Core/Security.php';  
    require_once APP_PATH . '/Core/Language.php';
    echo "<p>✅ Core files loaded</p>";
    
    // Test basic instantiation
    $security = new Security();
    echo "<p>✅ Security instantiated</p>";
    
    $language = new Language();
    echo "<p>✅ Language instantiated</p>";
    
    $db = Database::getInstance();
    echo "<p>✅ Database instantiated</p>";
    
    // Simple HTML page without complex routing
    echo "<h2>🎉 Core application working!</h2>";
    echo "<p>All basic components are functioning properly.</p>";
    echo "<p>Issue is likely in the Controller initialization or routing logic.</p>";
    
    echo "<h3>Next Steps:</h3>";
    echo "<ul>";
    echo "<li>Check the debug.php output in browser</li>";
    echo "<li>Look at the Controller class initialization</li>";
    echo "<li>Check if view files exist</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<h2 style='color:red'>❌ Error:</h2>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
} catch (Error $e) {
    echo "<h2 style='color:red'>❌ Fatal Error:</h2>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
