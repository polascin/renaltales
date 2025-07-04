<?php
// Test script to verify PHP can write to storage directories
$directories = [
    'storage/logs',
    'storage/cache', 
    'storage/sessions',
    'public/uploads'
];

echo "Testing write permissions for PHP...\n\n";

foreach ($directories as $dir) {
    $testFile = $dir . '/test_write.txt';
    
    if (is_writable($dir)) {
        // Try to create a test file
        if (file_put_contents($testFile, 'Test write access')) {
            echo "✓ {$dir} - WRITABLE (test file created)\n";
            // Clean up test file
            unlink($testFile);
        } else {
            echo "✗ {$dir} - ERROR: Could not create test file\n";
        }
    } else {
        echo "✗ {$dir} - ERROR: Directory is not writable\n";
    }
}

echo "\nPermission test completed.\n";
?>
