<?php

// Turn on all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "=== TESTING HOMECONTROLLER DIRECTLY ===\n";

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

    echo "5. Creating HomeController...\n";
    $controller = new \RenalTales\Controllers\HomeController();
    echo "   ✓ HomeController created\n";

    echo "6. Calling index method...\n";
    $response = $controller->index($request);
    echo "   ✓ Index method called, status: " . $response->getStatusCode() . "\n";

    echo "7. Getting response body...\n";
    $body = $response->getBody()->getContents();
    echo "   ✓ Response body length: " . strlen($body) . " characters\n";

    if (strlen($body) > 0) {
        echo "8. Response preview (first 500 chars):\n";
        echo substr($body, 0, 500) . "\n...\n";
    }

    echo "\n=== SUCCESS: HomeController works fine ===\n";

} catch (\Throwable $e) {
    echo "\n=== ERROR IN HOMECONTROLLER ===\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack Trace:\n";
    echo $e->getTraceAsString() . "\n";
}
