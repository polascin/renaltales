<?php

/**
 * Advanced Cache Clear Script for Renal Tales Application
 * 
 * This script uses the modern CacheManager class with detailed reporting
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

// Include the CacheManager
require_once __DIR__ . '/../core/CacheManager.php';

echo "=== Renal Tales Advanced Cache Clear Script ===\n";
echo "Started at: " . date('Y-m-d H:i:s') . "\n\n";

try {
    $cacheManager = new CacheManager();
    
    // Display cache statistics before clearing
    echo "Cache Statistics Before Clearing:\n";
    $stats = $cacheManager->getStats();
    
    echo "- File cache: " . $stats['file_cache']['count'] . " files, " . 
         number_format($stats['file_cache']['total_size'] / 1024, 2) . " KB\n";
    echo "- APCu available: " . ($stats['apcu_available'] ? 'Yes' : 'No') . "\n";
    echo "- OPcache available: " . ($stats['opcache_available'] ? 'Yes' : 'No') . "\n";
    
    if ($stats['apcu_available'] && isset($stats['apcu_info']['num_entries'])) {
        echo "- APCu entries: " . $stats['apcu_info']['num_entries'] . "\n";
    }
    
    if ($stats['opcache_available'] && isset($stats['opcache_info']['opcache_statistics']['num_cached_scripts'])) {
        echo "- OPcache scripts: " . $stats['opcache_info']['opcache_statistics']['num_cached_scripts'] . "\n";
    }
    
    echo "\nClearing cache...\n";
    $results = $cacheManager->clearAll();
    
    // Detailed results display
    echo "\nCache Clear Results:\n";
    echo "==================\n";
    
    echo "File Cache:\n";
    echo "- Files cleared: " . $results['file_cache']['count'] . "\n";
    if (!empty($results['file_cache']['errors'])) {
        echo "- Errors: " . implode(', ', $results['file_cache']['errors']) . "\n";
    }
    
    echo "\nTemporary Files:\n";
    echo "- Files cleared: " . $results['temp_files']['count'] . "\n";
    if (!empty($results['temp_files']['errors'])) {
        echo "- Errors: " . implode(', ', $results['temp_files']['errors']) . "\n";
    }
    
    echo "\nSession Files:\n";
    echo "- Old sessions cleared: " . $results['sessions']['count'] . "\n";
    if (!empty($results['sessions']['errors'])) {
        echo "- Errors: " . implode(', ', $results['sessions']['errors']) . "\n";
    }
    
    echo "\nAPCu Cache:\n";
    echo "- Status: " . $results['apcu_cache']['message'] . "\n";
    
    echo "\nOPcache:\n";
    echo "- Status: " . $results['opcache']['message'] . "\n";
    
    $totalFiles = $results['file_cache']['count'] + $results['temp_files']['count'] + $results['sessions']['count'];
    
    echo "\n=== Cache Clear Completed Successfully ===\n";
    echo "Total files deleted: {$totalFiles}\n";
    echo "Finished at: " . date('Y-m-d H:i:s') . "\n";
    
} catch (Exception $e) {
    echo "Error during cache clear: " . $e->getMessage() . "\n";
    exit(1);
}

