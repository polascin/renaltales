<?php

/**
 * Cache Clear Script for Renal Tales Application
 * 
 * This script clears all cache files from the application
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

echo "=== Renal Tales Cache Clear Script ===\n";
echo "Started at: " . date('Y-m-d H:i:s') . "\n\n";

try {
    // Clear cache files
    echo "Clearing cache files...\n";
    $cachePath = __DIR__ . '/../storage/cache/';
    $deletedCache = 0;
    
    if (is_dir($cachePath)) {
        $cacheFiles = glob($cachePath . '*');
        
        foreach ($cacheFiles as $file) {
            if (is_file($file) && basename($file) !== '.gitkeep') {
                if (unlink($file)) {
                    $deletedCache++;
                    echo "   Deleted: " . basename($file) . "\n";
                }
            }
        }
    }
    
    echo "\nTotal deleted cache files: {$deletedCache}\n";
    
    // Clear temporary files as well
    echo "\nClearing temporary files...\n";
    $tempPath = __DIR__ . '/../storage/temp/';
    $deletedTemp = 0;
    
    if (is_dir($tempPath)) {
        $tempFiles = glob($tempPath . '*');
        
        foreach ($tempFiles as $file) {
            if (is_file($file) && basename($file) !== '.gitkeep') {
                if (unlink($file)) {
                    $deletedTemp++;
                    echo "   Deleted: " . basename($file) . "\n";
                }
            }
        }
    }
    
    echo "\nTotal deleted temporary files: {$deletedTemp}\n";
    
    // Clear OPcache if available
    if (function_exists('opcache_reset')) {
        echo "\nClearing OPcache...\n";
        if (opcache_reset()) {
            echo "   OPcache cleared successfully\n";
        } else {
            echo "   Failed to clear OPcache\n";
        }
    } else {
        echo "\nOPcache not available\n";
    }
    
    // Clear user data cache if available
    echo "\nChecking for user cache systems...\n";
    
    $userCacheCleared = false;
    
    // Check for APC extension (legacy)
    if (extension_loaded('apc') && function_exists('apc_clear_cache')) {
        $apcEnabled = ini_get('apc.enabled');
        $apcCliEnabled = ini_get('apc.enable_cli');
        $isCli = (php_sapi_name() === 'cli');
        
        if ($apcEnabled && (!$isCli || $apcCliEnabled)) {
            echo "   Attempting to clear APC user cache...\n";
            try {
                $result = @apc_clear_cache('user');
                if ($result) {
                    echo "   APC user cache cleared successfully\n";
                    $userCacheCleared = true;
                } else {
                    echo "   APC clear cache returned false\n";
                }
            } catch (Throwable $e) {
                echo "   APC error: " . $e->getMessage() . "\n";
            }
        } else {
            echo "   APC extension found but not enabled for current context\n";
            echo "   - APC enabled: " . ($apcEnabled ? 'yes' : 'no') . "\n";
            echo "   - CLI mode: " . ($isCli ? 'yes' : 'no') . "\n";
            echo "   - APC CLI enabled: " . ($apcCliEnabled ? 'yes' : 'no') . "\n";
        }
    }
    // Check for APCu extension (modern replacement)
    elseif (extension_loaded('apcu') && function_exists('apcu_clear_cache')) {
        $apcuEnabled = ini_get('apc.enabled');
        echo "   Attempting to clear APCu cache...\n";
        try {
            $result = @apcu_clear_cache();
            if ($result) {
                echo "   APCu cache cleared successfully\n";
                $userCacheCleared = true;
            } else {
                echo "   APCu clear cache returned false\n";
            }
        } catch (Throwable $e) {
            echo "   APCu error: " . $e->getMessage() . "\n";
        }
    } else {
        echo "   No user cache extensions (APC/APCu) found\n";
    }
    
    if (!$userCacheCleared) {
        echo "   User cache systems not available or not cleared\n";
    }
    
    echo "\n=== Cache Clear Completed Successfully ===\n";
    echo "Finished at: " . date('Y-m-d H:i:s') . "\n";
    
} catch (Exception $e) {
    echo "Error during cache clear: " . $e->getMessage() . "\n";
    exit(1);
}

?>
