<?php

/**
 * Cleanup Script for Renal Tales Application
 * 
 * This script performs routine cleanup tasks to maintain the application
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

// Include required files
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/PasswordResetManager.php';
require_once __DIR__ . '/../core/EmailVerificationManager.php';

echo "=== Renal Tales Cleanup Script ===\n";
echo "Started at: " . date('Y-m-d H:i:s') . "\n\n";

try {
    // 1. Clean up expired password reset tokens
    echo "1. Cleaning up expired password reset tokens...\n";
    $resetManager = new PasswordResetManager();
    $deletedResets = $resetManager->cleanupExpiredTokens();
    echo "   Deleted: {$deletedResets} expired password reset tokens\n\n";
    
    // 2. Clean up expired email verification tokens
    echo "2. Cleaning up expired email verification tokens...\n";
    $verificationManager = new EmailVerificationManager();
    $deletedVerifications = $verificationManager->cleanupExpiredTokens();
    echo "   Deleted: {$deletedVerifications} expired email verification tokens\n\n";
    
    // 3. Clean up old session files (older than 1 day)
    echo "3. Cleaning up old session files...\n";
    $sessionPath = __DIR__ . '/../storage/sessions/';
    $sessionFiles = glob($sessionPath . 'sess_*');
    $deletedSessions = 0;
    $cutoffTime = time() - (24 * 60 * 60); // 24 hours ago
    
    foreach ($sessionFiles as $file) {
        if (filemtime($file) < $cutoffTime) {
            if (unlink($file)) {
                $deletedSessions++;
            }
        }
    }
    echo "   Deleted: {$deletedSessions} old session files\n\n";
    
    // 4. Clean up log files (keep last 30 days)
    echo "4. Cleaning up old log files...\n";
    $logPath = __DIR__ . '/../storage/logs/';
    $logFiles = glob($logPath . '*.log');
    $deletedLogs = 0;
    $logCutoffTime = time() - (30 * 24 * 60 * 60); // 30 days ago
    
    foreach ($logFiles as $file) {
        if (filemtime($file) < $logCutoffTime) {
            if (unlink($file)) {
                $deletedLogs++;
            }
        }
    }
    echo "   Deleted: {$deletedLogs} old log files\n\n";
    
    // 5. Clear cache files
    echo "5. Clearing cache files...\n";
    $cachePath = __DIR__ . '/../storage/cache/';
    $cacheFiles = glob($cachePath . '*');
    $deletedCache = 0;
    
    foreach ($cacheFiles as $file) {
        if (is_file($file) && basename($file) !== '.gitkeep') {
            if (unlink($file)) {
                $deletedCache++;
            }
        }
    }
    echo "   Deleted: {$deletedCache} cache files\n\n";
    
    // 6. Clear temporary files
    echo "6. Clearing temporary files...\n";
    $tempPath = __DIR__ . '/../storage/temp/';
    $tempFiles = glob($tempPath . '*');
    $deletedTemp = 0;
    
    foreach ($tempFiles as $file) {
        if (is_file($file) && basename($file) !== '.gitkeep') {
            if (unlink($file)) {
                $deletedTemp++;
            }
        }
    }
    echo "   Deleted: {$deletedTemp} temporary files\n\n";
    
    echo "=== Cleanup Completed Successfully ===\n";
    echo "Finished at: " . date('Y-m-d H:i:s') . "\n";
    
} catch (Exception $e) {
    echo "Error during cleanup: " . $e->getMessage() . "\n";
    exit(1);
}

?>
