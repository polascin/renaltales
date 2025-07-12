<?php

/**
 * Database Health Monitor
 * 
 * Monitors database connection health and performance metrics
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

class DatabaseHealthMonitor {
    
    private $dbConfig;
    private $healthChecks = [];
    private $alerts = [];
    private $metrics = [];
    private $isRunning = false;
    
    // Health check thresholds
    private $thresholds = [
        'max_response_time' => 5000, // 5 seconds in milliseconds
        'max_failed_connections' => 3,
        'max_pool_utilization' => 80, // 80%
        'max_query_time' => 10000, // 10 seconds
        'min_available_connections' => 2
    ];
    
    /**
     * Constructor
     * 
     * @param DatabaseConfig $dbConfig
     */
    public function __construct(DatabaseConfig $dbConfig) {
        $this->dbConfig = $dbConfig;
        $this->loadConfiguration();
        $this->initializeMetrics();
    }
    
    /**
     * Load monitoring configuration
     */
    private function loadConfiguration() {
        $this->thresholds['max_response_time'] = (int)($_ENV['DB_MONITOR_MAX_RESPONSE_TIME'] ?? 5000);
        $this->thresholds['max_failed_connections'] = (int)($_ENV['DB_MONITOR_MAX_FAILED_CONNECTIONS'] ?? 3);
        $this->thresholds['max_pool_utilization'] = (int)($_ENV['DB_MONITOR_MAX_POOL_UTIL'] ?? 80);
        $this->thresholds['max_query_time'] = (int)($_ENV['DB_MONITOR_MAX_QUERY_TIME'] ?? 10000);
        $this->thresholds['min_available_connections'] = (int)($_ENV['DB_MONITOR_MIN_CONNECTIONS'] ?? 2);
    }
    
    /**
     * Initialize metrics tracking
     */
    private function initializeMetrics() {
        $connections = $this->dbConfig->getAllConnections();
        
        foreach (array_keys($connections) as $connectionName) {
            $this->metrics[$connectionName] = [
                'total_queries' => 0,
                'successful_queries' => 0,
                'failed_queries' => 0,
                'total_response_time' => 0,
                'avg_response_time' => 0,
                'max_response_time' => 0,
                'min_response_time' => PHP_INT_MAX,
                'last_health_check' => null,
                'consecutive_failures' => 0,
                'uptime_percentage' => 100,
                'connection_errors' => [],
                'performance_alerts' => []
            ];
        }
    }
    
    /**
     * Start health monitoring
     */
    public function startMonitoring() {
        if ($this->isRunning) {
            return;
        }
        
        $this->isRunning = true;
        $this->log('Health monitoring started');
        
        // Perform initial health check
        $this->performHealthCheck();
        
        // Set up periodic health checks (would be handled by cron in production)
        $this->schedulePeriodicChecks();
    }
    
    /**
     * Stop health monitoring
     */
    public function stopMonitoring() {
        $this->isRunning = false;
        $this->log('Health monitoring stopped');
    }
    
    /**
     * Perform comprehensive health check
     * 
     * @return array Health check results
     */
    public function performHealthCheck() {
        $results = [];
        $connections = $this->dbConfig->getAllConnections();
        
        foreach (array_keys($connections) as $connectionName) {
            $results[$connectionName] = $this->checkConnectionHealth($connectionName);
        }
        
        $this->analyzeResults($results);
        return $results;
    }
    
    /**
     * Check health of specific connection
     * 
     * @param string $connectionName
     * @return array Health check result
     */
    private function checkConnectionHealth($connectionName) {
        $startTime = microtime(true);
        $result = [
            'connection' => $connectionName,
            'timestamp' => date('Y-m-d H:i:s'),
            'healthy' => false,
            'response_time' => 0,
            'error' => null,
            'metrics' => []
        ];
        
        try {
            // Test basic connectivity
            $connectionTest = $this->dbConfig->testConnection($connectionName);
            $endTime = microtime(true);
            
            $responseTime = round(($endTime - $startTime) * 1000, 2);
            $result['response_time'] = $responseTime;
            $result['healthy'] = $connectionTest['connected'];
            $result['error'] = $connectionTest['error'];
            
            if ($connectionTest['connected']) {
                // Additional health metrics
                $result['metrics'] = $this->gatherConnectionMetrics($connectionName);
                $this->updateMetrics($connectionName, $responseTime, true);
                $this->metrics[$connectionName]['consecutive_failures'] = 0;
            } else {
                $this->updateMetrics($connectionName, $responseTime, false);
                $this->metrics[$connectionName]['consecutive_failures']++;
            }
            
            // Check thresholds
            $this->checkThresholds($connectionName, $result);
            
        } catch(Exception $e) 
    error_log('Exception in DatabaseHealthMonitor.php: ' . $e->getMessage());
            $result['error'] = $e->getMessage();
            $this->updateMetrics($connectionName, 0, false);
            $this->metrics[$connectionName]['consecutive_failures']++;
        
        
        $this->healthChecks[$connectionName][] = $result;
        $this->metrics[$connectionName]['last_health_check'] = $result['timestamp'];
        
        // Keep only last 100 health checks
        if (count($this->healthChecks[$connectionName]) > 100) {
            array_shift($this->healthChecks[$connectionName]);
        }
        
        return $result;
    }
    
    /**
     * Gather additional connection metrics
     * 
     * @param string $connectionName
     * @return array
     */
    private function gatherConnectionMetrics($connectionName) {
        $poolStats = $this->dbConfig->getPoolStats($connectionName);
        
        return [
            'pool_stats' => $poolStats,
            'pool_utilization' => $this->calculatePoolUtilization($poolStats),
            'connection_efficiency' => $this->calculateConnectionEfficiency($poolStats)
        ];
    }
    
    /**
     * Calculate pool utilization percentage
     * 
     * @param array $poolStats
     * @return float
     */
    private function calculatePoolUtilization($poolStats) {
        $maxConnections = $this->dbConfig->getPoolConfig()['max_connections'] ?? 10;
        $activeConnections = $poolStats['active_connections'] ?? 0;
        
        return ($activeConnections / $maxConnections) * 100;
    }
    
    /**
     * Calculate connection efficiency
     * 
     * @param array $poolStats
     * @return float
     */
    private function calculateConnectionEfficiency($poolStats) {
        $hits = $poolStats['pool_hits'] ?? 0;
        $misses = $poolStats['pool_misses'] ?? 0;
        $total = $hits + $misses;
        
        return $total > 0 ? ($hits / $total) * 100 : 0;
    }
    
    /**
     * Update connection metrics
     * 
     * @param string $connectionName
     * @param float $responseTime
     * @param bool $success
     */
    private function updateMetrics($connectionName, $responseTime, $success) {
        $metrics = &$this->metrics[$connectionName];
        
        $metrics['total_queries']++;
        $metrics['total_response_time'] += $responseTime;
        $metrics['avg_response_time'] = $metrics['total_response_time'] / $metrics['total_queries'];
        
        if ($responseTime > $metrics['max_response_time']) {
            $metrics['max_response_time'] = $responseTime;
        }
        
        if ($responseTime < $metrics['min_response_time'] && $responseTime > 0) {
            $metrics['min_response_time'] = $responseTime;
        }
        
        if ($success) {
            $metrics['successful_queries']++;
        } else {
            $metrics['failed_queries']++;
        }
        
        // Calculate uptime percentage
        $metrics['uptime_percentage'] = ($metrics['successful_queries'] / $metrics['total_queries']) * 100;
    }
    
    /**
     * Check performance thresholds and generate alerts
     * 
     * @param string $connectionName
     * @param array $result
     */
    private function checkThresholds($connectionName, $result) {
        $alerts = [];
        
        // Response time threshold
        if ($result['response_time'] > $this->thresholds['max_response_time']) {
            $alerts[] = [
                'type' => 'high_response_time',
                'message' => "Response time ({$result['response_time']}ms) exceeds threshold ({$this->thresholds['max_response_time']}ms)",
                'severity' => 'warning'
            ];
        }
        
        // Consecutive failures threshold
        if ($this->metrics[$connectionName]['consecutive_failures'] >= $this->thresholds['max_failed_connections']) {
            $alerts[] = [
                'type' => 'consecutive_failures',
                'message' => "Connection has failed {$this->metrics[$connectionName]['consecutive_failures']} consecutive times",
                'severity' => 'critical'
            ];
        }
        
        // Pool utilization threshold
        if (isset($result['metrics']['pool_utilization'])) {
            $utilization = $result['metrics']['pool_utilization'];
            if ($utilization > $this->thresholds['max_pool_utilization']) {
                $alerts[] = [
                    'type' => 'high_pool_utilization',
                    'message' => "Pool utilization ({$utilization}%) exceeds threshold ({$this->thresholds['max_pool_utilization']}%)",
                    'severity' => 'warning'
                ];
            }
        }
        
        // Store alerts
        if (!empty($alerts)) {
            $this->alerts[$connectionName][] = [
                'timestamp' => date('Y-m-d H:i:s'),
                'alerts' => $alerts
            ];
            
            // Trigger alert notifications
            $this->triggerAlerts($connectionName, $alerts);
        }
    }
    
    /**
     * Trigger alert notifications
     * 
     * @param string $connectionName
     * @param array $alerts
     */
    private function triggerAlerts($connectionName, $alerts) {
        foreach ($alerts as $alert) {
            $this->log("ALERT [{$alert['severity']}] Connection: $connectionName - {$alert['message']}");
            
            // In a real implementation, you might send emails, Slack notifications, etc.
            $this->sendAlertNotification($connectionName, $alert);
        }
    }
    
    /**
     * Send alert notification (placeholder)
     * 
     * @param string $connectionName
     * @param array $alert
     */
    private function sendAlertNotification($connectionName, $alert) {
        // Placeholder for alert notification system
        // Could integrate with email, Slack, SMS, etc.
        
        if ($_ENV['DB_MONITOR_ENABLE_EMAIL_ALERTS'] === 'true') {
            // Send email alert
            $this->sendEmailAlert($connectionName, $alert);
        }
    }
    
    /**
     * Send email alert (placeholder)
     * 
     * @param string $connectionName
     * @param array $alert
     */
    private function sendEmailAlert($connectionName, $alert) {
        // Integration with email system would go here
        $this->log("Email alert would be sent for connection: $connectionName");
    }
    
    /**
     * Get health metrics for connection
     * 
     * @param string $connectionName
     * @return array
     */
    public function getMetrics($connectionName = null) {
        if ($connectionName !== null) {
            return $this->metrics[$connectionName] ?? [];
        }
        
        return $this->metrics;
    }
    
    /**
     * Get recent health checks
     * 
     * @param string $connectionName
     * @param int $limit
     * @return array
     */
    public function getHealthHistory($connectionName, $limit = 10) {
        $checks = $this->healthChecks[$connectionName] ?? [];
        return array_slice($checks, -$limit);
    }
    
    /**
     * Get alerts for connection
     * 
     * @param string $connectionName
     * @return array
     */
    public function getAlerts($connectionName = null) {
        if ($connectionName !== null) {
            return $this->alerts[$connectionName] ?? [];
        }
        
        return $this->alerts;
    }
    
    /**
     * Schedule periodic health checks
     */
    private function schedulePeriodicChecks() {
        // In a real implementation, this would set up cron jobs or background processes
        $this->log('Periodic health checks scheduled');
    }
    
    /**
     * Analyze health check results
     * 
     * @param array $results
     */
    private function analyzeResults($results) {
        $healthyConnections = 0;
        $totalConnections = count($results);
        
        foreach ($results as $result) {
            if ($result['healthy']) {
                $healthyConnections++;
            }
        }
        
        $healthPercentage = ($healthyConnections / $totalConnections) * 100;
        
        $this->log("Health Check Complete: {$healthyConnections}/{$totalConnections} connections healthy ({$healthPercentage}%)");
    }
    
    /**
     * Log monitoring events
     * 
     * @param string $message
     */
    private function log($message) {
        $logEntry = '[' . date('Y-m-d H:i:s') . '] DB Health Monitor: ' . $message . PHP_EOL;
        
        // Write to monitoring log
        $logFile = dirname(__DIR__) . '/storage/logs/db_health.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        
        // Also log to main application log if available
        if (isset($GLOBALS['logger'])) {
            $GLOBALS['logger']->info('DB Health Monitor: ' . $message);
        }
    }
    
    /**
     * Get comprehensive health report
     * 
     * @return array
     */
    public function getHealthReport() {
        $report = [
            'timestamp' => date('Y-m-d H:i:s'),
            'monitoring_status' => $this->isRunning ? 'running' : 'stopped',
            'connections' => [],
            'overall_health' => 'healthy',
            'summary' => []
        ];
        
        $totalConnections = 0;
        $healthyConnections = 0;
        
        foreach ($this->metrics as $connectionName => $metrics) {
            $totalConnections++;
            $connectionHealth = $metrics['uptime_percentage'] > 95 ? 'healthy' : 'unhealthy';
            
            if ($connectionHealth === 'healthy') {
                $healthyConnections++;
            }
            
            $report['connections'][$connectionName] = [
                'health_status' => $connectionHealth,
                'uptime_percentage' => $metrics['uptime_percentage'],
                'avg_response_time' => $metrics['avg_response_time'],
                'total_queries' => $metrics['total_queries'],
                'consecutive_failures' => $metrics['consecutive_failures'],
                'last_check' => $metrics['last_health_check']
            ];
        }
        
        $healthPercentage = $totalConnections > 0 ? ($healthyConnections / $totalConnections) * 100 : 0;
        $report['overall_health'] = $healthPercentage >= 80 ? 'healthy' : ($healthPercentage >= 50 ? 'degraded' : 'critical');
        
        $report['summary'] = [
            'total_connections' => $totalConnections,
            'healthy_connections' => $healthyConnections,
            'health_percentage' => $healthPercentage,
            'total_alerts' => array_sum(array_map('count', $this->alerts))
        ];
        
        return $report;
    }
}
