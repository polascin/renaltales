<?php

// Turn on all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Capture all output
ob_start();

echo "=== WEB DEBUG TEST ===\n";

try {
    echo "1. Loading constants...\n";
    require_once __DIR__ . '/config/constants.php';
    echo "   ✓ Constants loaded\n";

    echo "2. Loading autoloader...\n";
    require_once __DIR__ . '/vendor/autoload.php';
    echo "   ✓ Autoloader loaded\n";

    echo "3. Loading bootstrap...\n";
    require_once __DIR__ . '/bootstrap.php';
    echo "   ✓ Bootstrap loaded\n";

    echo "4. Creating PSR-7 request...\n";
    $request = new \RenalTales\Http\ServerRequest(
        'GET',
        '/',
        [],
        '',
        [],
        [],
        [],
        [],
        []
    );
    echo "   ✓ Request created\n";

    echo "5. Creating Router...\n";
    $router = new \RenalTales\Core\Router();
    echo "   ✓ Router created\n";

    echo "6. Handling request...\n";
    $response = $router->handle($request);
    echo "   ✓ Request handled, status: " . $response->getStatusCode() . "\n";

    echo "7. Getting response body...\n";
    $body = $response->getBody()->getContents();
    echo "   ✓ Response body length: " . strlen($body) . " characters\n";

    if (strlen($body) > 0) {
        echo "8. Response preview (first 200 chars):\n";
        echo substr($body, 0, 200) . "\n...\n";
    }

    echo "\n=== SUCCESS: No errors found ===\n";

} catch (\Throwable $e) {
    echo "\n=== ERROR FOUND ===\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack Trace:\n";
    echo $e->getTraceAsString() . "\n";
    
    // Show previous exceptions
    $prev = $e->getPrevious();
    while ($prev) {
        echo "\nPrevious Exception:\n";
        echo "Message: " . $prev->getMessage() . "\n";
        echo "File: " . $prev->getFile() . ":" . $prev->getLine() . "\n";
        $prev = $prev->getPrevious();
    }
}

// Get the debug output
$debug_output = ob_get_clean();

// Return as HTML if accessed via browser
if (isset($_SERVER['HTTP_HOST'])) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<pre>' . htmlspecialchars($debug_output) . '</pre>';
} else {
    echo $debug_output;
}
