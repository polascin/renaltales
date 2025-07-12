<?php
/**
 * Comprehensive Test for Language and Session Integration
 * 
 * This test validates the integration between LanguageManager and SessionManager
 * and ensures they work together properly.
 */

require_once 'bootstrap.php';
require_once 'src/Core/LanguageManager.php';
require_once 'src/Core/SessionManager.php';

use RenalTales\Core\LanguageManager;
use RenalTales\Core\SessionManager;

// Initialize error tracking
$errors = [];
$successes = [];

try {
    // Initialize systems
    $languageManager = new LanguageManager();
    $sessionManager = new SessionManager([], true, ['127.0.0.1', '::1'], 1800);
    
    // Test 1: Language System Basic Functionality
    echo "<h2>Test 1: Language System Basic Functionality</h2>";
    
    $supportedLanguages = $languageManager->getSupportedLanguages();
    if (empty($supportedLanguages)) {
        $errors[] = "No supported languages found";
    } else {
        $successes[] = "Found " . count($supportedLanguages) . " supported languages";
        echo "<p>Supported languages: " . implode(', ', $supportedLanguages) . "</p>";
    }
    
    // Test if 'en' is supported
    if ($languageManager->isSupported('en')) {
        $successes[] = "English language is supported";
    } else {
        $errors[] = "English language is not supported";
    }
    
    // Test 2: Language Detection
    echo "<h2>Test 2: Language Detection</h2>";
    
    $detectedLanguage = $languageManager->detectLanguage();
    if ($detectedLanguage) {
        $successes[] = "Language detected: " . $detectedLanguage;
        echo "<p>Detected language: <strong>" . $detectedLanguage . "</strong></p>";
    } else {
        $errors[] = "Failed to detect language";
    }
    
    // Test 3: Session Integration
    echo "<h2>Test 3: Session Integration</h2>";
    
    // Test setting language in session
    $testLanguage = 'en';
    $sessionManager->setSession('language', $testLanguage);
    $retrievedLanguage = $sessionManager->getSession('language');
    
    if ($retrievedLanguage === $testLanguage) {
        $successes[] = "Language successfully stored and retrieved from session";
        echo "<p>Session language: <strong>" . $retrievedLanguage . "</strong></p>";
    } else {
        $errors[] = "Failed to store/retrieve language from session";
    }
    
    // Test 4: Language Switching
    echo "<h2>Test 4: Language Switching</h2>";
    
    if (isset($_GET['switch_to'])) {
        $newLanguage = $_GET['switch_to'];
        if ($languageManager->isSupported($newLanguage)) {
            $languageManager->setLanguage($newLanguage);
            $sessionManager->setSession('language', $newLanguage);
            $successes[] = "Language switched to: " . $newLanguage;
            echo "<p>Language switched to: <strong>" . $newLanguage . "</strong></p>";
        } else {
            $errors[] = "Attempted to switch to unsupported language: " . $newLanguage;
        }
    }
    
    // Test 5: Session Persistence
    echo "<h2>Test 5: Session Persistence</h2>";
    
    $sessionInfo = $sessionManager->getSessionInfo();
    if ($sessionInfo['is_initialized']) {
        $successes[] = "Session is properly initialized";
        echo "<p>Session ID: " . $sessionInfo['id'] . "</p>";
        echo "<p>Session Status: " . $sessionInfo['status'] . "</p>";
        echo "<p>Session Variables Count: " . $sessionInfo['variables_count'] . "</p>";
    } else {
        $errors[] = "Session is not properly initialized";
    }
    
    // Test 6: Language Flag Integration
    echo "<h2>Test 6: Language Flag Integration</h2>";
    
    $currentLang = $sessionManager->getSession('language') ?: $detectedLanguage;
    $flagPath = $languageManager->getFlagPath($currentLang);
    $flagCode = $languageManager->getFlagCode($currentLang);
    
    echo "<p>Current language flag: <strong>" . $flagCode . "</strong></p>";
    echo "<p>Flag path: <strong>" . $flagPath . "</strong></p>";
    
    // Test 7: Language Information
    echo "<h2>Test 7: Language Information</h2>";
    
    $languageInfo = $languageManager->getLanguageInfo($currentLang);
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>Property</th><th>Value</th></tr>";
    foreach ($languageInfo as $key => $value) {
        echo "<tr><td>" . htmlspecialchars($key) . "</td><td>" . htmlspecialchars(is_bool($value) ? ($value ? 'true' : 'false') : $value) . "</td></tr>";
    }
    echo "</table>";
    
    // Test 8: Cookie Integration
    echo "<h2>Test 8: Cookie Integration</h2>";
    
    $cookieLanguage = $_COOKIE['language'] ?? 'Not set';
    echo "<p>Cookie language: <strong>" . htmlspecialchars($cookieLanguage) . "</strong></p>";
    
    // Test 9: System Statistics
    echo "<h2>Test 9: System Statistics</h2>";
    
    $systemStats = $languageManager->getSystemStats();
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>Statistic</th><th>Value</th></tr>";
    foreach ($systemStats as $key => $value) {
        $displayValue = is_array($value) ? json_encode($value) : $value;
        echo "<tr><td>" . htmlspecialchars($key) . "</td><td>" . htmlspecialchars($displayValue) . "</td></tr>";
    }
    echo "</table>";
    
    // Test 10: Debug Information
    echo "<h2>Test 10: Debug Information</h2>";
    
    $debugInfo = $languageManager->getDebugInfo();
    if ($debugInfo['debug_mode']) {
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><th>Debug Property</th><th>Value</th></tr>";
        foreach ($debugInfo as $key => $value) {
            $displayValue = is_array($value) ? json_encode($value) : $value;
            echo "<tr><td>" . htmlspecialchars($key) . "</td><td>" . htmlspecialchars($displayValue) . "</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Debug mode is not enabled</p>";
    }
    
} catch (Exception $e) {
    $errors[] = "Critical error: " . $e->getMessage();
    echo "<p style='color: red;'>Critical error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Language & Session Integration Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .success {
            color: green;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 10px;
            margin: 5px 0;
            border-radius: 4px;
        }
        .error {
            color: red;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            margin: 5px 0;
            border-radius: 4px;
        }
        .language-switch {
            margin: 20px 0;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        .language-switch a {
            display: inline-block;
            margin: 5px;
            padding: 8px 12px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .language-switch a:hover {
            background-color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .test-actions {
            margin: 20px 0;
            padding: 20px;
            background-color: #e9ecef;
            border-radius: 4px;
        }
        .test-actions button {
            margin: 5px;
            padding: 10px 15px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .test-actions button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <h1>Language & Session Integration Test</h1>
    
    <div class="test-actions">
        <h3>Test Actions</h3>
        <button onclick="location.reload()">Refresh Test</button>
        <button onclick="clearSession()">Clear Session</button>
        <button onclick="window.open('<?php echo $_SERVER['PHP_SELF']; ?>?debug=1', '_blank')">Debug Mode</button>
    </div>
    
    <div class="language-switch">
        <h3>Language Switch Test</h3>
        <p>Click on a language to test language switching:</p>
        <?php
        if (isset($supportedLanguages) && !empty($supportedLanguages)) {
            foreach (array_slice($supportedLanguages, 0, 10) as $lang) {
                $langName = $languageManager->getLanguageName($lang);
                echo "<a href='?switch_to=" . urlencode($lang) . "'>" . htmlspecialchars($langName) . " (" . htmlspecialchars($lang) . ")</a> ";
            }
        }
        ?>
    </div>
    
    <h2>Test Results Summary</h2>
    
    <?php if (!empty($successes)): ?>
        <h3>Successes (<?php echo count($successes); ?>)</h3>
        <?php foreach ($successes as $success): ?>
            <div class="success">✓ <?php echo htmlspecialchars($success); ?></div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if (!empty($errors)): ?>
        <h3>Errors (<?php echo count($errors); ?>)</h3>
        <?php foreach ($errors as $error): ?>
            <div class="error">✗ <?php echo htmlspecialchars($error); ?></div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if (empty($errors)): ?>
        <div class="success">
            <h3>🎉 All tests passed! Language and Session systems are working correctly.</h3>
        </div>
    <?php else: ?>
        <div class="error">
            <h3>⚠️ Some tests failed. Please review the errors above.</h3>
        </div>
    <?php endif; ?>
    
    <h2>Session Debug Information</h2>
    <?php
    if (isset($sessionManager)) {
        echo "<h3>Session Manager Debug</h3>";
        $sessionManager->displaySessionDebug();
        
        echo "<h3>Session Statistics</h3>";
        $sessionManager->displaySessionStats();
        
        echo "<h3>Session Table</h3>";
        $sessionManager->displaySessionTable();
    }
    ?>
    
    <script>
        function clearSession() {
            if (confirm('Are you sure you want to clear the session?')) {
                fetch('<?php echo $_SERVER['PHP_SELF']; ?>?action=clear_session', {
                    method: 'POST'
                }).then(() => {
                    location.reload();
                });
            }
        }
        
        // Auto-refresh every 30 seconds if in debug mode
        <?php if (isset($_GET['debug'])): ?>
        setTimeout(() => {
            location.reload();
        }, 30000);
        <?php endif; ?>
    </script>
</body>
</html>

<?php
// Handle AJAX requests
if (isset($_GET['action']) && $_GET['action'] === 'clear_session') {
    if (isset($sessionManager)) {
        $sessionManager->clearSession();
        echo json_encode(['status' => 'success', 'message' => 'Session cleared']);
    }
    exit;
}
?>
