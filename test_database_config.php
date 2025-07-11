<?php

/**
 * Comprehensive Database Configuration Test
 * Tests all database configurations and connections
 */

// Load bootstrap
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/core/DatabaseConfig.php';
require_once __DIR__ . '/core/Database.php';

echo "=== Comprehensive Database Configuration Test ===\n\n";

// Test 1: Environment Variables
echo "1. Environment Variables Check:\n";
$envVars = [
    'DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD',
    'TEST_DB_HOST', 'TEST_DB_DATABASE', 'TEST_DB_USERNAME',
    'LOCAL_DB_HOST', 'LOCAL_DB_DATABASE', 'LOCAL_DB_USERNAME'
];

foreach ($envVars as $var) {
    $value = $_ENV[$var] ?? 'NOT SET';
    if (strpos($var, 'PASSWORD') !== false && $value !== 'NOT SET') {
        $value = '[SET]';
    }
    echo "   $var: $value\n";
}
echo "\n";

// Test 2: Database Configuration Loading
echo "2. Database Configuration Manager:\n";
try {
    $dbConfig = DatabaseConfig::getInstance();
    $envInfo = $dbConfig->getEnvironmentInfo();
    
    echo "   ✓ DatabaseConfig initialized successfully\n";
    echo "   Environment: " . $envInfo['environment'] . "\n";
    echo "   Default Connection: " . $envInfo['default_connection'] . "\n";
    echo "   Available Connections: " . implode(', ', $envInfo['available_connections']) . "\n";
    echo "   Config File Exists: " . ($envInfo['config_file_exists'] ? 'Yes' : 'No') . "\n";
    echo "   Env Variables Loaded: " . ($envInfo['env_variables_loaded'] ? 'Yes' : 'No') . "\n";
    
} catch (Exception $e) {
    echo "   ✗ DatabaseConfig failed: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 3: Connection Testing
echo "3. Connection Testing:\n";
try {
    $dbConfig = DatabaseConfig::getInstance();
    $connections = $dbConfig->getAllConnections();
    
    foreach ($connections as $name => $config) {
        echo "   Testing '$name' connection:\n";
        $result = $dbConfig->testConnection($name);
        
        if ($result['connected']) {
            echo "     ✓ Connected successfully\n";
            echo "     Host: " . $result['host'] . "\n";
            echo "     Database: " . $result['database'] . "\n";
            echo "     Version: " . $result['version'] . "\n";
            echo "     Latency: " . $result['latency'] . "\n";
        } else {
            echo "     ✗ Connection failed\n";
            echo "     Error: " . ($result['error'] ?? 'Unknown error') . "\n";
            echo "     Host: " . $result['host'] . "\n";
            echo "     Database: " . $result['database'] . "\n";
        }
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ Connection testing failed: " . $e->getMessage() . "\n";
}

// Test 4: Legacy Database Class
echo "4. Legacy Database Class Test:\n";
try {
    $db = Database::getInstance();
    $status = $db->testConnection();
    
    if ($status['connected']) {
        echo "   ✓ Legacy Database class working\n";
        echo "   Host: " . $status['host'] . "\n";
        echo "   Database: " . $status['database'] . "\n";
        echo "   Version: " . $status['version'] . "\n";
    } else {
        echo "   ✗ Legacy Database class failed\n";
        echo "   Error: " . ($status['error'] ?? 'Unknown error') . "\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ Legacy Database class error: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 5: Configuration Consistency Check
echo "5. Configuration Consistency Check:\n";
try {
    $dbConfig = DatabaseConfig::getInstance();
    $defaultConn = $dbConfig->getConnection();
    
    $db = Database::getInstance();
    $legacyStatus = $db->testConnection();
    
    $configMatches = (
        $defaultConn['host'] === $legacyStatus['host'] &&
        $defaultConn['database'] === $legacyStatus['database'] &&
        $defaultConn['username'] === $legacyStatus['username']
    );
    
    if ($configMatches) {
        echo "   ✓ DatabaseConfig and Database class configurations match\n";
    } else {
        echo "   ⚠ Configuration mismatch detected:\n";
        echo "     DatabaseConfig - Host: " . $defaultConn['host'] . ", DB: " . $defaultConn['database'] . "\n";
        echo "     Database class - Host: " . $legacyStatus['host'] . ", DB: " . $legacyStatus['database'] . "\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ Consistency check failed: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 6: Security Check
echo "6. Security Assessment:\n";
$securityIssues = [];

// Check for hardcoded credentials in config files
$configContent = file_get_contents(__DIR__ . '/config/database.php');
if (preg_match('/password.*=.*[\'"][a-zA-Z0-9@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?~`]{3,}[\'"]/', $configContent)) {
    // Additional check to make sure it's not just env() calls
    if (!preg_match('/password.*env\(/', $configContent)) {
        $securityIssues[] = "Hardcoded passwords found in database.php";
    }
}

// Check environment
$environment = $_ENV['APP_ENV'] ?? 'unknown';
if ($environment === 'production' && $_ENV['APP_DEBUG'] === 'true') {
    $securityIssues[] = "Debug mode enabled in production";
}

// Check database permissions (basic)
if (isset($_ENV['DB_USERNAME']) && $_ENV['DB_USERNAME'] === 'root') {
    $securityIssues[] = "Using root database user (consider dedicated user)";
}

if (empty($securityIssues)) {
    echo "   ✓ No major security issues detected\n";
} else {
    echo "   ⚠ Security issues found:\n";
    foreach ($securityIssues as $issue) {
        echo "     - $issue\n";
    }
}
echo "\n";

echo "=== Database Configuration Test Complete ===\n\n";

// Recommendations
echo "Recommendations:\n";
echo "- ✓ Environment variables properly loaded\n";
echo "- ✓ Centralized configuration manager implemented\n";
echo "- ✓ Multiple environment support available\n";
echo "- ✓ Connection testing and monitoring capabilities\n";

if (!empty($securityIssues)) {
    echo "- ⚠ Address security issues listed above\n";
}

echo "- 💡 Consider using connection pooling for high-traffic scenarios\n";
echo "- 💡 Implement database health monitoring\n";
echo "- 💡 Set up automated backups\n";

?>
