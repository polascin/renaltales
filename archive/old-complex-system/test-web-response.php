<?php
/**
 * Test web server response and CSS file accessibility
 */

echo "=== Web Response Test ===\n\n";

// Test URLs to check
$testUrls = [
    'http://localhost:8000/',
    'http://localhost:8000/assets/css/main.css',
    'http://localhost:8000/assets/css/themes.css',
    'http://localhost:8000/assets/css/responsive.css'
];

foreach ($testUrls as $url) {
    echo "Testing: $url\n";
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'ignore_errors' => true
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response !== false) {
        $httpCode = isset($http_response_header[0]) ? $http_response_header[0] : 'Unknown';
        echo "✓ Response: $httpCode\n";
        
        if (strpos($url, '.css') !== false) {
            // Check CSS content
            if (strpos($response, ':root') !== false || strpos($response, 'body') !== false) {
                echo "✓ Valid CSS content detected\n";
            } else {
                echo "⚠ CSS content may be incomplete\n";
            }
        }
    } else {
        echo "✗ Failed to connect or get response\n";
    }
    echo "\n";
}

// Test if CSS files are accessible directly
echo "--- Direct File Access Test ---\n";
$cssFiles = [
    'main.css',
    'public/assets/css/themes.css',
    'public/assets/css/basic.css'
];

foreach ($cssFiles as $file) {
    $fullPath = __DIR__ . '/' . $file;
    if (file_exists($fullPath)) {
        $content = file_get_contents($fullPath);
        $size = strlen($content);
        echo "✓ $file accessible ($size bytes)\n";
        
        // Check for CSS custom properties
        if (strpos($content, '--') !== false) {
            echo "  → Contains CSS custom properties\n";
        }
        
        // Check for media queries for responsive design
        if (strpos($content, '@media') !== false) {
            echo "  → Contains media queries (responsive)\n";
        }
    } else {
        echo "✗ $file not found\n";
    }
}

echo "\n--- Browser Console Error Check ---\n";
echo "To check for 404 errors, open browser developer tools and navigate to:\n";
echo "- http://localhost:8000/\n";
echo "- Check Network tab for any failed CSS requests\n";
echo "- Look for 404 errors or failed resource loads\n";

echo "\n=== Test Complete ===\n";
?>
