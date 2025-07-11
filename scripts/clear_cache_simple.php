<?php

/**
 * Simple Cache Clear Script for Renal Tales Application
 * 
 * This script clears file-based cache without checking for PHP extensions
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

echo "=== Renal Tales Simple Cache Clear ===\n";
echo "Started at: " . date('Y-m-d H:i:s') . "\n\n";

try {
    $totalDeleted = 0;
    
    // Clear cache files
    echo "1. Clearing cache files...\n";
    $cachePath = __DIR__ . '/../storage/cache/';
    $deletedCache = 0;
    
    if (is_dir($cachePath)) {
        $cacheFiles = glob($cachePath . '*');
        
        foreach ($cacheFiles as $file) {
            if (is_file($file) && basename($file) !== '.gitkeep') {
                if (unlink($file)) {
                    $deletedCache++;
                    echo "   ✓ Deleted: " . basename($file) . "\n";
                } else {
                    echo "   ✗ Failed to delete: " . basename($file) . "\n";
                }
            }
        }
    }
    
    echo "   Total cache files deleted: {$deletedCache}\n\n";
    $totalDeleted += $deletedCache;
    
    // Clear temporary files
    echo "2. Clearing temporary files...\n";
    $tempPath = __DIR__ . '/../storage/temp/';
    $deletedTemp = 0;
    
    if (is_dir($tempPath)) {
        $tempFiles = glob($tempPath . '*');
        
        foreach ($tempFiles as $file) {
            if (is_file($file) && basename($file) !== '.gitkeep') {
                if (unlink($file)) {
                    $deletedTemp++;
                    echo "   ✓ Deleted: " . basename($file) . "\n";
                } else {
                    echo "   ✗ Failed to delete: " . basename($file) . "\n";
                }
            }
        }
    }
    
    echo "   Total temporary files deleted: {$deletedTemp}\n\n";
    $totalDeleted += $deletedTemp;
    
    // Clear old session files (older than 24 hours)
    echo "3. Clearing old session files...\n";
    $sessionPath = __DIR__ . '/../storage/sessions/';
    $deletedSessions = 0;
    
    if (is_dir($sessionPath)) {
        $sessionFiles = glob($sessionPath . 'sess_*');
        $cutoffTime = time() - (24 * 60 * 60); // 24 hours ago
        
        foreach ($sessionFiles as $file) {
            if (is_file($file) && filemtime($file) < $cutoffTime) {
                if (unlink($file)) {
                    $deletedSessions++;
                    echo "   ✓ Deleted old session: " . basename($file) . "\n";
                } else {
                    echo "   ✗ Failed to delete session: " . basename($file) . "\n";
                }
            }
        }
    }
    
    echo "   Total old session files deleted: {$deletedSessions}\n\n";
    $totalDeleted += $deletedSessions;
    
    // Clear OPcache if available (safe check)
    echo "4. Checking OPcache...\n";
    if (function_exists('opcache_reset') && opcache_get_status(false) !== false) {
        if (opcache_reset()) {
            echo "   ✓ OPcache cleared successfully\n";
        } else {
            echo "   ✗ Failed to clear OPcache\n";
        }
    } else {
        echo "   - OPcache not available or not enabled\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "Cache clear completed successfully!\n";
    echo "Total files deleted: {$totalDeleted}\n";
    echo "Finished at: " . date('Y-m-d H:i:s') . "\n";
    echo str_repeat("=", 50) . "\n";
    
} catch (Exception $e) {
    echo "\n❌ Error during cache clear: " . $e->getMessage() . "\n";
    exit(1);
}

?>
