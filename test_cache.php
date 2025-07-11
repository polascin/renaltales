<?php

/**
 * Test script for the new CacheManager
 */

require_once __DIR__ . '/core/CacheManager.php';

try {
    echo "Testing CacheManager...\n";
    
    $cache = new CacheManager();
    
    // Test storing and retrieving data
    echo "1. Testing cache set/get...\n";
    $testData = ['test' => 'value', 'number' => 42];
    $success = $cache->set('test_key', $testData, 300);
    echo "   Set result: " . ($success ? 'Success' : 'Failed') . "\n";
    
    $retrieved = $cache->get('test_key');
    echo "   Retrieved: " . (json_encode($retrieved) === json_encode($testData) ? 'Match' : 'No match') . "\n";
    
    // Test cache statistics
    echo "2. Testing cache statistics...\n";
    $stats = $cache->getStats();
    echo "   File cache count: " . $stats['file_cache']['count'] . "\n";
    echo "   APCu available: " . ($stats['apcu_available'] ? 'Yes' : 'No') . "\n";
    echo "   OPcache available: " . ($stats['opcache_available'] ? 'Yes' : 'No') . "\n";
    
    // Clean up test
    $cache->delete('test_key');
    echo "3. Test cleanup completed\n";
    
    echo "\nCacheManager test completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
