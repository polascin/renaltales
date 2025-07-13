<?php

namespace RenalTales\Public;

/**
 * RenalTales Health Check Endpoint
 * 
 * Provides comprehensive health monitoring for the application
 * including database, cache, storage, and system checks
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

require_once dirname(__DIR__) . '/bootstrap.php';

use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use PDO;

class HealthCheckService
{
    private $config;
    private $startTime;
    private $checks = [];
    
    public function __construct()
    {
        $this->config = require dirname(__DIR__) . '/config/app.php';
        $this->startTime = microtime(true);
    }
    
    /**
     * Run all health checks
     */
    public function runHealthChecks(): array
    {
        $this->checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'mail' => $this->checkMail(),
            'memory' => $this->checkMemory(),
            'disk' => $this->checkDisk(),
            'permissions' => $this->checkPermissions(),
            'dependencies' => $this->checkDependencies(),
        ];
        
        $overallStatus = $this->getOverallStatus();
        
        return [
            'status' => $overallStatus,
            'timestamp' => date('c'),
            'response_time' => round((microtime(true) - $this->startTime) * 1000, 2),
            'version' => $this->config['app']['version'],
            'environment' => $this->config['app']['environment'],
            'checks' => $this->checks,
            'system' => $this->getSystemInfo(),
        ];
    }
    
    /**
     * Check database connectivity
     */
    private function checkDatabase(): array
    {
        $start = microtime(true);
        
        try {
            $dbConfig = $this->config['database']['connections']['mysql'];
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $dbConfig['host'],
                $dbConfig['port'],
                $dbConfig['database'],
                $dbConfig['charset']
            );
            
            $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);
            
            // Test connection with a simple query
            $stmt = $pdo->query('SELECT 1');
            $result = $stmt->fetchColumn();
            
            if ($result !== 1) {
                throw new Exception('Database query returned unexpected result');
            }
            
            // Check table count
            $stmt = $pdo->query('SHOW TABLES');
            $tableCount = $stmt->rowCount();
            
            return [
                'status' => 'healthy',
                'response_time' => round((microtime(true) - $start) * 1000, 2),
                'database' => $dbConfig['database'],
                'host' => $dbConfig['host'],
                'table_count' => $tableCount,
            ];
            
        } catch(Exception $e) 
    error_log('Exception in health.php: ' . $e->getMessage());
            return [
                'status' => 'unhealthy',
                'response_time' => round((microtime(true) - $start) * 1000, 2),
                'error' => $e->getMessage(),
            ];
        
    }
    
    /**
     * Check cache system
     */
    private function checkCache(): array
    {
        $start = microtime(true);
        
        try {
            $cacheDriver = $this->config['cache']['default'];
            $testKey = 'health_check_' . time();
            $testValue = 'test_value_' . rand(1000, 9999);
            
            if ($cacheDriver === 'file') {
                $cachePath = $this->config['cache']['stores']['file']['path'];
                
                if (!is_dir($cachePath)) {
                    mkdir($cachePath, 0755, true);
                }
                
                if (!is_writable($cachePath)) {
                    throw new Exception('Cache directory is not writable');
                }
                
                // Test file cache
                $testFile = $cachePath . '/' . $testKey;
                file_put_contents($testFile, $testValue);
                
                if (file_get_contents($testFile) !== $testValue) {
                    throw new Exception('Cache write/read test failed');
                }
                
                unlink($testFile);
                
                return [
                    'status' => 'healthy',
                    'response_time' => round((microtime(true) - $start) * 1000, 2),
                    'driver' => 'file',
                    'path' => $cachePath,
                ];
                
            } elseif ($cacheDriver === 'redis') {
                // Test Redis connection
                $redisConfig = $this->config['cache']['redis']['cache'];
                
                $redis = new Redis();
                $connected = $redis->connect($redisConfig['host'], $redisConfig['port']);
                
                if (!$connected) {
                    throw new Exception('Failed to connect to Redis');
                }
                
                if (!empty($redisConfig['password'])) {
                    $redis->auth($redisConfig['password']);
                }
                
                $redis->select($redisConfig['database']);
                
                // Test Redis operations
                $redis->set($testKey, $testValue, 60);
                $retrieved = $redis->get($testKey);
                
                if ($retrieved !== $testValue) {
                    throw new Exception('Redis write/read test failed');
                }
                
                $redis->del($testKey);
                $redis->close();
                
                return [
                    'status' => 'healthy',
                    'response_time' => round((microtime(true) - $start) * 1000, 2),
                    'driver' => 'redis',
                    'host' => $redisConfig['host'],
                    'port' => $redisConfig['port'],
                ];
            }
            
            return [
                'status' => 'healthy',
                'response_time' => round((microtime(true) - $start) * 1000, 2),
                'driver' => $cacheDriver,
            ];
            
        } catch(Exception $e) 
    error_log('Exception in health.php: ' . $e->getMessage());
            return [
                'status' => 'unhealthy',
                'response_time' => round((microtime(true) - $start) * 1000, 2),
                'error' => $e->getMessage(),
            ];
        
    }
    
    /**
     * Check storage directories
     */
    private function checkStorage(): array
    {
        $start = microtime(true);
        
        try {
            $baseDir = dirname(__DIR__);
            $storageDirectories = [
                'storage/logs',
                'storage/cache',
                'storage/sessions',
                'storage/uploads',
                'storage/temp',
            ];
            
            $results = [];
            
            foreach ($storageDirectories as $dir) {
                $fullPath = $baseDir . '/' . $dir;
                
                if (!is_dir($fullPath)) {
                    mkdir($fullPath, 0755, true);
                }
                
                $writable = is_writable($fullPath);
                $size = $this->getDirectorySize($fullPath);
                
                $results[$dir] = [
                    'exists' => is_dir($fullPath),
                    'writable' => $writable,
                    'size' => $size,
                    'size_human' => $this->formatBytes($size),
                ];
                
                if (!$writable) {
                    throw new Exception("Directory $dir is not writable");
                }
            }
            
            return [
                'status' => 'healthy',
                'response_time' => round((microtime(true) - $start) * 1000, 2),
                'directories' => $results,
            ];
            
        } catch(Exception $e) 
    error_log('Exception in health.php: ' . $e->getMessage());
            return [
                'status' => 'unhealthy',
                'response_time' => round((microtime(true) - $start) * 1000, 2),
                'error' => $e->getMessage(),
            ];
        
    }
    
    /**
     * Check mail system
     */
    private function checkMail(): array
    {
        $start = microtime(true);
        
        try {
            $mailConfig = $this->config['mail'];
            $mailer = $mailConfig['default'];
            
            if ($mailer === 'smtp') {
                $smtpConfig = $mailConfig['mailers']['smtp'];
                
                // Test SMTP connection
                $socket = @fsockopen($smtpConfig['host'], $smtpConfig['port'], $errno, $errstr, 5);
                
                if (!$socket) {
                    throw new Exception("SMTP connection failed: $errstr ($errno)");
                }
                
                fclose($socket);
                
                return [
                    'status' => 'healthy',
                    'response_time' => round((microtime(true) - $start) * 1000, 2),
                    'mailer' => 'smtp',
                    'host' => $smtpConfig['host'],
                    'port' => $smtpConfig['port'],
                ];
                
            } elseif ($mailer === 'log') {
                $logPath = dirname(__DIR__) . '/storage/logs/mail.log';
                $logDir = dirname($logPath);
                
                if (!is_dir($logDir)) {
                    mkdir($logDir, 0755, true);
                }
                
                if (!is_writable($logDir)) {
                    throw new Exception('Mail log directory is not writable');
                }
                
                return [
                    'status' => 'healthy',
                    'response_time' => round((microtime(true) - $start) * 1000, 2),
                    'mailer' => 'log',
                    'log_path' => $logPath,
                ];
            }
            
            return [
                'status' => 'healthy',
                'response_time' => round((microtime(true) - $start) * 1000, 2),
                'mailer' => $mailer,
            ];
            
        } catch(Exception $e) 
    error_log('Exception in health.php: ' . $e->getMessage());
            return [
                'status' => 'unhealthy',
                'response_time' => round((microtime(true) - $start) * 1000, 2),
                'error' => $e->getMessage(),
            ];
        
    }
    
    /**
     * Check memory usage
     */
    private function checkMemory(): array
    {
        $start = microtime(true);
        
        $memoryLimit = ini_get('memory_limit');
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        
        $limitBytes = $this->parseMemoryLimit($memoryLimit);
        $usagePercent = $limitBytes > 0 ? round(($memoryUsage / $limitBytes) * 100, 2) : 0;
        
        $status = 'healthy';
        if ($usagePercent > 90) {
            $status = 'unhealthy';
        } elseif ($usagePercent > 80) {
            $status = 'warning';
        }
        
        return [
            'status' => $status,
            'response_time' => round((microtime(true) - $start) * 1000, 2),
            'limit' => $memoryLimit,
            'usage' => $this->formatBytes($memoryUsage),
            'peak' => $this->formatBytes($memoryPeak),
            'usage_percent' => $usagePercent,
        ];
    }
    
    /**
     * Check disk usage
     */
    private function checkDisk(): array
    {
        $start = microtime(true);
        
        $path = dirname(__DIR__);
        $totalSpace = disk_total_space($path);
        $freeSpace = disk_free_space($path);
        $usedSpace = $totalSpace - $freeSpace;
        $usagePercent = round(($usedSpace / $totalSpace) * 100, 2);
        
        $status = 'healthy';
        if ($usagePercent > 95) {
            $status = 'unhealthy';
        } elseif ($usagePercent > 90) {
            $status = 'warning';
        }
        
        return [
            'status' => $status,
            'response_time' => round((microtime(true) - $start) * 1000, 2),
            'total' => $this->formatBytes($totalSpace),
            'free' => $this->formatBytes($freeSpace),
            'used' => $this->formatBytes($usedSpace),
            'usage_percent' => $usagePercent,
        ];
    }
    
    /**
     * Check file permissions
     */
    private function checkPermissions(): array
    {
        $start = microtime(true);
        
        $baseDir = dirname(__DIR__);
        $criticalPaths = [
            'storage' => $baseDir . '/storage',
            'config' => $baseDir . '/config',
            'resources/lang' => $baseDir . '/resources/lang',
            'public/assets' => $baseDir . '/public/assets',
        ];
        
        $issues = [];
        
        foreach ($criticalPaths as $name => $path) {
            if (!is_dir($path)) {
                $issues[] = "$name directory does not exist";
                continue;
            }
            
            if (!is_readable($path)) {
                $issues[] = "$name directory is not readable";
            }
            
            if (strpos($name, 'storage') !== false && !is_writable($path)) {
                $issues[] = "$name directory is not writable";
            }
        }
        
        return [
            'status' => empty($issues) ? 'healthy' : 'unhealthy',
            'response_time' => round((microtime(true) - $start) * 1000, 2),
            'issues' => $issues,
        ];
    }
    
    /**
     * Check PHP dependencies
     */
    private function checkDependencies(): array
    {
        $start = microtime(true);
        
        $requiredExtensions = [
            'json', 'mbstring', 'openssl', 'pdo', 'pdo_mysql',
            'tokenizer', 'xml', 'ctype', 'curl', 'gd', 'intl', 'zip', 'fileinfo'
        ];
        
        $missing = [];
        foreach ($requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                $missing[] = $ext;
            }
        }
        
        $optionalExtensions = ['redis', 'opcache', 'imagick'];
        $optionalMissing = [];
        foreach ($optionalExtensions as $ext) {
            if (!extension_loaded($ext)) {
                $optionalMissing[] = $ext;
            }
        }
        
        return [
            'status' => empty($missing) ? 'healthy' : 'unhealthy',
            'response_time' => round((microtime(true) - $start) * 1000, 2),
            'php_version' => PHP_VERSION,
            'missing_required' => $missing,
            'missing_optional' => $optionalMissing,
        ];
    }
    
    /**
     * Get overall status
     */
    private function getOverallStatus(): string
    {
        $statuses = array_column($this->checks, 'status');
        
        if (in_array('unhealthy', $statuses)) {
            return 'unhealthy';
        }
        
        if (in_array('warning', $statuses)) {
            return 'warning';
        }
        
        return 'healthy';
    }
    
    /**
     * Get system information
     */
    private function getSystemInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'server_name' => $_SERVER['SERVER_NAME'] ?? 'Unknown',
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'load_average' => function_exists('sys_getloadavg') ? sys_getloadavg() : null,
            'uptime' => function_exists('uptime') ? uptime() : null,
        ];
    }
    
    /**
     * Utility methods
     */
    private function getDirectorySize(string $directory): int
    {
        $size = 0;
        if (is_dir($directory)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
            );
            
            foreach ($iterator as $file) {
                $size += $file->getSize();
            }
        }
        return $size;
    }
    
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    private function parseMemoryLimit(string $limit): int
    {
        $limit = trim($limit);
        $unit = strtolower(substr($limit, -1));
        $value = (int) substr($limit, 0, -1);
        
        switch ($unit) {
            case 'g':
                return $value * 1024 * 1024 * 1024;
            case 'm':
                return $value * 1024 * 1024;
            case 'k':
                return $value * 1024;
            default:
                return (int) $limit;
        }
    }
}

// Handle the request
try {
    $healthCheck = new HealthCheckService();
    $result = $healthCheck->runHealthChecks();
    
    // Set appropriate HTTP status code
    $httpStatus = 200;
    if ($result['status'] === 'unhealthy') {
        $httpStatus = 503; // Service Unavailable
    } elseif ($result['status'] === 'warning') {
        $httpStatus = 200; // OK but with warnings
    }
    
    http_response_code($httpStatus);
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    
} catch(Exception $e) 
    error_log('Exception in health.php: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    
    echo json_encode([
        'status' => 'unhealthy',
        'timestamp' => date('c'),
        'error' => 'Health check failed: ' . $e->getMessage(),
    ], JSON_PRETTY_PRINT);

