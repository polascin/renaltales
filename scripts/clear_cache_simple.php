<?php

/**
 * Simple Cache Clear Script for Renal Tales Application
 *
 * This script uses the modern CacheManager class for cache operations
 *
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

// Include the CacheManager
require_once __DIR__ . '/../core/CacheManager.php';

echo "=== Renal Tales Simple Cache Clear ===\n";
echo "Started at: " . date('Y-m-d H:i:s') . "\n\n";

try {
    $cacheManager = new CacheManager();

    echo "Clearing all cache using CacheManager...\n";
    $results = $cacheManager->clearAll();

    // Display results
    echo "1. Cache files cleared: " . $results['file_cache']['count'] . "\n";
    if (!empty($results['file_cache']['errors'])) {
        echo "   Errors: " . implode(', ', $results['file_cache']['errors']) . "\n";
    }

    echo "2. Temporary files cleared: " . $results['temp_files']['count'] . "\n";
    if (!empty($results['temp_files']['errors'])) {
        echo "   Errors: " . implode(', ', $results['temp_files']['errors']) . "\n";
    }

    echo "3. Old session files cleared: " . $results['sessions']['count'] . "\n";
    if (!empty($results['sessions']['errors'])) {
        echo "   Errors: " . implode(', ', $results['sessions']['errors']) . "\n";
    }

    echo "4. APCu cache: " . $results['apcu_cache']['message'] . "\n";
    echo "5. OPcache: " . $results['opcache']['message'] . "\n";

    $totalFiles = $results['file_cache']['count'] + $results['temp_files']['count'] + $results['sessions']['count'];

    echo "\n" . str_repeat("=", 50) . "\n";
    echo "Cache clear completed successfully!\n";
    echo "Total files deleted: {$totalFiles}\n";
    echo "Finished at: " . date('Y-m-d H:i:s') . "\n";
    echo str_repeat("=", 50) . "\n";

} catch (Exception $e) {
    echo "\n❌ Error during cache clear: " . $e->getMessage() . "\n";
    exit(1);
}
