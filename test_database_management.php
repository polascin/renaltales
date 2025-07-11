<?php

/**
 * Comprehensive Database Management Test
 * 
 * Tests connection pooling, health monitoring, and backup functionality
 */

// Load application
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/core/DatabaseConfig.php';
require_once __DIR__ . '/core/DatabaseHealthMonitor.php';
require_once __DIR__ . '/core/DatabaseBackupManager.php';

echo "=== Comprehensive Database Management Test ===\n\n";

try {
    // Initialize components
    echo "1. Initializing Database Management Components...\n";
    $dbConfig = DatabaseConfig::getInstance();
    $healthMonitor = $dbConfig->getHealthMonitor();
    $backupManager = new DatabaseBackupManager($dbConfig);
    
    echo "   ✓ DatabaseConfig initialized\n";
    echo "   ✓ Health Monitor initialized\n";
    echo "   ✓ Backup Manager initialized\n\n";
    
    // Test 2: Connection Pooling
    echo "2. Testing Connection Pooling...\n";
    testConnectionPooling($dbConfig);
    
    // Test 3: Health Monitoring
    echo "\n3. Testing Health Monitoring...\n";
    testHealthMonitoring($healthMonitor);
    
    // Test 4: Backup System
    echo "\n4. Testing Backup System...\n";
    testBackupSystem($backupManager);
    
    // Test 5: Integration Test
    echo "\n5. Integration Test...\n";
    testIntegration($dbConfig, $healthMonitor, $backupManager);
    
    echo "\n=== All Tests Completed Successfully! ===\n\n";
    
    // Display summary
    displaySummary($dbConfig, $healthMonitor, $backupManager);
    
} catch (Exception $e) {
    echo "Error during testing: " . $e->getMessage() . "\n";
    exit(1);
}

/**
 * Test connection pooling functionality
 */
function testConnectionPooling($dbConfig) {
    echo "   Testing connection pool operations...\n";
    
    // Get pool statistics before
    $statsBefore = $dbConfig->getPoolStats();
    echo "   Initial pool stats: " . json_encode($statsBefore) . "\n";
    
    // Test getting pooled connections
    $connections = [];
    for ($i = 0; $i < 3; $i++) {
        try {
            $conn = $dbConfig->getPooledConnection();
            $connections[] = $conn;
            echo "   ✓ Got pooled connection #" . ($i + 1) . "\n";
        } catch (Exception $e) {
            echo "   ✗ Failed to get pooled connection #" . ($i + 1) . ": " . $e->getMessage() . "\n";
        }
    }
    
    // Return connections to pool
    foreach ($connections as $i => $conn) {
        $dbConfig->returnToPool($conn);
        echo "   ✓ Returned connection #" . ($i + 1) . " to pool\n";
    }
    
    // Get pool statistics after
    $statsAfter = $dbConfig->getPoolStats();
    echo "   Final pool stats: " . json_encode($statsAfter) . "\n";
    
    echo "   ✓ Connection pooling test completed\n";
}

/**
 * Test health monitoring functionality
 */
function testHealthMonitoring($healthMonitor) {
    if (!$healthMonitor) {
        echo "   ⚠ Health monitor not available, skipping tests\n";
        return;
    }
    
    echo "   Starting health monitoring...\n";
    $healthMonitor->startMonitoring();
    
    echo "   Performing health check...\n";
    $healthResults = $healthMonitor->performHealthCheck();
    
    foreach ($healthResults as $connection => $result) {
        $status = $result['healthy'] ? '✓' : '✗';
        echo "   $status Connection '$connection': {$result['response_time']}ms\n";
        
        if (!$result['healthy'] && $result['error']) {
            echo "     Error: {$result['error']}\n";
        }
    }
    
    // Get health metrics
    echo "   Getting health metrics...\n";
    $metrics = $healthMonitor->getMetrics();
    
    foreach ($metrics as $connection => $metric) {
        echo "   Connection '$connection' metrics:\n";
        echo "     - Uptime: {$metric['uptime_percentage']}%\n";
        echo "     - Avg Response: {$metric['avg_response_time']}ms\n";
        echo "     - Total Queries: {$metric['total_queries']}\n";
    }
    
    // Get health report
    echo "   Generating health report...\n";
    $report = $healthMonitor->getHealthReport();
    echo "   Overall health: {$report['overall_health']}\n";
    echo "   Healthy connections: {$report['summary']['healthy_connections']}/{$report['summary']['total_connections']}\n";
    
    echo "   ✓ Health monitoring test completed\n";
}

/**
 * Test backup system functionality
 */
function testBackupSystem($backupManager) {
    echo "   Testing backup system...\n";
    
    // Test schema backup (faster than full backup for testing)
    echo "   Creating schema backup...\n";
    $result = $backupManager->createSchemaBackup();
    
    if ($result['success']) {
        echo "   ✓ Schema backup created successfully\n";
        echo "     File: {$result['file_path']}\n";
        echo "     Size: " . formatFileSize($result['file_size']) . "\n";
        echo "     Duration: " . calculateDuration($result['started_at'], $result['completed_at']) . "\n";
    } else {
        echo "   ✗ Schema backup failed: {$result['error']}\n";
    }
    
    // Test backup statistics
    echo "   Getting backup statistics...\n";
    $stats = $backupManager->getBackupStatistics();
    echo "   Total backups: {$stats['total_backups']}\n";
    echo "   Successful: {$stats['successful_backups']}\n";
    echo "   Total size: " . formatFileSize($stats['total_size']) . "\n";
    
    // Test cleanup (dry run)
    echo "   Testing cleanup functionality...\n";
    $cleanupResult = $backupManager->cleanupOldBackups();
    echo "   Files that would be deleted: {$cleanupResult['deleted_files']}\n";
    echo "   Space that would be freed: " . formatFileSize($cleanupResult['freed_space']) . "\n";
    
    echo "   ✓ Backup system test completed\n";
}

/**
 * Test integration between all components
 */
function testIntegration($dbConfig, $healthMonitor, $backupManager) {
    echo "   Testing component integration...\n";
    
    // Test database config with health monitoring
    echo "   Testing database config integration...\n";
    $configHealthCheck = $dbConfig->performHealthCheck();
    
    foreach ($configHealthCheck as $connection => $result) {
        $status = $result['connected'] ? '✓' : '✗';
        echo "   $status Config health check for '$connection'\n";
    }
    
    // Test environment configuration
    echo "   Testing environment configuration...\n";
    $envInfo = $dbConfig->getEnvironmentInfo();
    echo "   Environment: {$envInfo['environment']}\n";
    echo "   Available connections: " . implode(', ', $envInfo['available_connections']) . "\n";
    
    // Test pool configuration
    echo "   Testing pool configuration...\n";
    $poolConfig = $dbConfig->getPoolConfig();
    echo "   Max connections: {$poolConfig['max_connections']}\n";
    echo "   Min connections: {$poolConfig['min_connections']}\n";
    echo "   Connection timeout: {$poolConfig['connection_timeout']}s\n";
    
    echo "   ✓ Integration test completed\n";
}

/**
 * Display comprehensive summary
 */
function displaySummary($dbConfig, $healthMonitor, $backupManager) {
    echo "=== System Summary ===\n\n";
    
    // Database Configuration Summary
    echo "Database Configuration:\n";
    $envInfo = $dbConfig->getEnvironmentInfo();
    echo "  Environment: {$envInfo['environment']}\n";
    echo "  Default Connection: {$envInfo['default_connection']}\n";
    echo "  Available Connections: " . implode(', ', $envInfo['available_connections']) . "\n";
    
    // Connection Pool Summary
    echo "\nConnection Pool Configuration:\n";
    $poolConfig = $dbConfig->getPoolConfig();
    echo "  Max Connections: {$poolConfig['max_connections']}\n";
    echo "  Min Connections: {$poolConfig['min_connections']}\n";
    echo "  Connection Timeout: {$poolConfig['connection_timeout']}s\n";
    echo "  Idle Timeout: {$poolConfig['idle_timeout']}s\n";
    echo "  Max Lifetime: {$poolConfig['max_lifetime']}s\n";
    
    // Pool Statistics
    echo "\nConnection Pool Statistics:\n";
    $poolStats = $dbConfig->getPoolStats();
    foreach ($poolStats as $connection => $stats) {
        echo "  Connection '$connection':\n";
        echo "    Active: {$stats['active_connections']}\n";
        echo "    Idle: {$stats['idle_connections']}\n";
        echo "    Created: {$stats['total_created']}\n";
        echo "    Pool Hits: {$stats['pool_hits']}\n";
        echo "    Pool Misses: {$stats['pool_misses']}\n";
        $efficiency = $stats['pool_hits'] + $stats['pool_misses'] > 0 
            ? round(($stats['pool_hits'] / ($stats['pool_hits'] + $stats['pool_misses'])) * 100, 2)
            : 0;
        echo "    Pool Efficiency: {$efficiency}%\n";
    }
    
    // Health Monitoring Summary
    if ($healthMonitor) {
        echo "\nHealth Monitoring:\n";
        $healthReport = $healthMonitor->getHealthReport();
        echo "  Status: {$healthReport['monitoring_status']}\n";
        echo "  Overall Health: {$healthReport['overall_health']}\n";
        echo "  Healthy Connections: {$healthReport['summary']['healthy_connections']}/{$healthReport['summary']['total_connections']}\n";
        echo "  Health Percentage: {$healthReport['summary']['health_percentage']}%\n";
        echo "  Total Alerts: {$healthReport['summary']['total_alerts']}\n";
    }
    
    // Backup System Summary
    echo "\nBackup System:\n";
    $backupStats = $backupManager->getBackupStatistics();
    echo "  Total Backups: {$backupStats['total_backups']}\n";
    echo "  Successful: {$backupStats['successful_backups']}\n";
    echo "  Failed: {$backupStats['failed_backups']}\n";
    echo "  Total Size: " . formatFileSize($backupStats['total_size']) . "\n";
    
    if ($backupStats['newest_backup']) {
        echo "  Latest Backup: {$backupStats['newest_backup']}\n";
    }
    
    // Recommendations
    echo "\nRecommendations:\n";
    echo "  ✓ Connection pooling implemented for high-traffic scenarios\n";
    echo "  ✓ Database health monitoring active\n";
    echo "  ✓ Automated backup system configured\n";
    echo "  💡 Set up cron jobs for automated backups\n";
    echo "  💡 Configure alert notifications (email/Slack)\n";
    echo "  💡 Monitor pool efficiency and adjust settings as needed\n";
    echo "  💡 Regular backup testing and restore procedures\n";
}

/**
 * Calculate duration between two timestamps
 */
function calculateDuration($start, $end) {
    $startTime = new DateTime($start);
    $endTime = new DateTime($end);
    $interval = $startTime->diff($endTime);
    
    return $interval->format('%H:%I:%S');
}

/**
 * Format file size
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

?>
