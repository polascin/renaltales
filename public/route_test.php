<?php
/**
 * Route Testing Script
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Route Testing</h1>";

try {
    // Define constants
    if (!defined('ROOT_PATH')) {
        define('ROOT_PATH', dirname(__DIR__));
    }
    define('APP_PATH', ROOT_PATH . '/app');
    define('CONFIG_PATH', ROOT_PATH . '/config');
    define('CONTROLLERS_PATH', APP_PATH . '/Controllers');
    define('VIEWS_PATH', APP_PATH . '/Views');
    
    // Load bootstrap
    require_once ROOT_PATH . '/bootstrap/autoload.php';
    
    // Load config
    $config = require_once CONFIG_PATH . '/config.php';
    
    // Set globals
    $GLOBALS['STORY_CATEGORIES'] = $config['story_categories'];
    $GLOBALS['SUPPORTED_STORY_LANGUAGES'] = $config['story_languages'];
    $GLOBALS['USER_ROLES'] = $config['user_roles'];
    $GLOBALS['ACCESS_LEVELS'] = $config['access_levels'];
    $GLOBALS['CONFIG'] = $config;
    
    // Load core files
    require_once APP_PATH . '/Core/Database.php';
    require_once APP_PATH . '/Core/Router.php';
    
    echo "<h2>Current Request Info</h2>";
    echo "<p><strong>REQUEST_URI:</strong> " . ($_SERVER['REQUEST_URI'] ?? 'Not set') . "</p>";
    echo "<p><strong>REQUEST_METHOD:</strong> " . ($_SERVER['REQUEST_METHOD'] ?? 'Not set') . "</p>";
    
    // Parse the URL like the main app does
    $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $method = $_SERVER['REQUEST_METHOD'];
    $url = rtrim($url, '/') ?: '/';
    
    echo "<p><strong>Parsed URL:</strong> '$url'</p>";
    echo "<p><strong>Method:</strong> $method</p>";
    
    // Create router and add just the home route
    $router = new Router();
    $router->get('/', 'HomeController@index');
    
    echo "<h2>Testing Route Matching</h2>";
    
    // Test if the route matches
    if ($url === '/') {
        echo "<p style='color:green'>✅ URL matches home route ('/')</p>";
        
        // Test if HomeController file exists
        $controllerFile = CONTROLLERS_PATH . '/HomeController.php';
        if (file_exists($controllerFile)) {
            echo "<p style='color:green'>✅ HomeController file exists</p>";
            
            // Try to load just the controller file
            require_once $controllerFile;
            
            if (class_exists('HomeController')) {
                echo "<p style='color:green'>✅ HomeController class loaded</p>";
                echo "<p><strong>Issue:</strong> The routing should work. The problem is likely in the Controller base class or HomeController execution.</p>";
            } else {
                echo "<p style='color:red'>❌ HomeController class not found after loading file</p>";
            }
        } else {
            echo "<p style='color:red'>❌ HomeController file not found at: $controllerFile</p>";
        }
    } else {
        echo "<p style='color:orange'>⚠️ URL '$url' does not match home route ('/')</p>";
        echo "<p><strong>Expected URL:</strong> /</p>";
        echo "<p><strong>Your URL:</strong> $url</p>";
        echo "<p><strong>Solution:</strong> Access the site at: <a href='/'>http://localhost/renaltales/public/</a></p>";
    }
    
    echo "<h2>View File Check</h2>";
    $viewFile = VIEWS_PATH . '/home/index.php';
    if (file_exists($viewFile)) {
        echo "<p style='color:green'>✅ Home view file exists: $viewFile</p>";
    } else {
        echo "<p style='color:red'>❌ Home view file not found: $viewFile</p>";
    }
    
} catch (Exception $e) {
    echo "<h2 style='color:red'>❌ Error:</h2>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
}
?>
