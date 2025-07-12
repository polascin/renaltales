<?php

/**
 * Database Configuration Manager
 * 
 * Centralized database configuration management for Renal Tales
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

class DatabaseConfig {
    
    private static $instance = null;
    private $config = [];
    private $environment;
    private $connectionPool = [];
    private $poolStats = [];
    private $healthMonitor = null;
    
    // Connection pool configuration
    private $poolConfig = [
        'max_connections' => 10,
        'min_connections' => 2,
        'connection_timeout' => 30,
        'idle_timeout' => 300, // 5 minutes
        'max_lifetime' => 3600, // 1 hour
        'health_check_interval' => 60 // 1 minute
    ];
    
    /**
     * Constructor - Private for singleton pattern
     */
    private function __construct() {
        $this->loadEnvironment();
        $this->loadConfiguration();
        $this->initializeConnectionPool();
        $this->initializeHealthMonitoring();
    }
    
    /**
     * Get DatabaseConfig instance (singleton pattern)
     * 
     * @return DatabaseConfig
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Load environment variables
     */
    private function loadEnvironment() {
        $this->environment = $_ENV['APP_ENV'] ?? 'development';
    }
    
    /**
     * Load database configuration
     */
    private function loadConfiguration() {
        // Load base configuration
        $configFile = dirname(__DIR__) . '/config/database.php';
        if (file_exists($configFile)) {
            $this->config = require $configFile;
        }
        
        // Override with environment-specific settings if needed
        $this->applyEnvironmentOverrides();
    }
    
    /**
     * Apply environment-specific configuration overrides
     */
    private function applyEnvironmentOverrides() {
        // Production environment security checks
        if ($this->environment === 'production') {
            // Ensure no default passwords are used
            foreach ($this->config['connections'] as $name => &$connection) {
                if (empty($connection['password']) && $name !== 'testing') {
                    throw new Exception("Database password cannot be empty in production environment");
                }
            }
        }
        
        // Testing environment setup
        if ($this->environment === 'testing') {
            $this->config['default'] = 'testing';
        }
    }
    
    /**
     * Initialize connection pool
     */
    private function initializeConnectionPool() {
        // Load pool configuration from environment
        $this->poolConfig['max_connections'] = (int)($_ENV['DB_POOL_MAX_CONNECTIONS'] ?? 10);
        $this->poolConfig['min_connections'] = (int)($_ENV['DB_POOL_MIN_CONNECTIONS'] ?? 2);
        $this->poolConfig['connection_timeout'] = (int)($_ENV['DB_POOL_TIMEOUT'] ?? 30);
        $this->poolConfig['idle_timeout'] = (int)($_ENV['DB_POOL_IDLE_TIMEOUT'] ?? 300);
        $this->poolConfig['max_lifetime'] = (int)($_ENV['DB_POOL_MAX_LIFETIME'] ?? 3600);
        
        // Initialize pool statistics
        foreach ($this->config['connections'] ?? [] as $name => $connection) {
            $this->poolStats[$name] = [
                'active_connections' => 0,
                'idle_connections' => 0,
                'total_created' => 0,
                'total_destroyed' => 0,
                'pool_hits' => 0,
                'pool_misses' => 0,
                'health_status' => 'unknown',
                'last_health_check' => null
            ];
            $this->connectionPool[$name] = [];
        }
    }
    
    /**
     * Initialize health monitoring
     */
    private function initializeHealthMonitoring() {
        if (file_exists(__DIR__ . '/DatabaseHealthMonitor.php')) {
            require_once __DIR__ . '/DatabaseHealthMonitor.php';
            if (class_exists('DatabaseHealthMonitor')) {
                $this->healthMonitor = new DatabaseHealthMonitor($this);
            }
        }
    }
    
    /**
     * Get connection configuration
     * 
     * @param string|null $connection Connection name, uses default if null
     * @return array Connection configuration
     */
    public function getConnection($connection = null) {
        if ($connection === null) {
            $connection = $this->config['default'];
        }
        
        if (!isset($this->config['connections'][$connection])) {
            throw new Exception("Database connection '$connection' not found");
        }
        
        return $this->config['connections'][$connection];
    }
    
    /**
     * Get default connection name
     * 
     * @return string
     */
    public function getDefaultConnection() {
        return $this->config['default'];
    }
    
    /**
     * Get all connections
     * 
     * @return array
     */
    public function getAllConnections() {
        return $this->config['connections'];
    }
    
    /**
     * Get Redis configuration
     * 
     * @param string $connection Redis connection name
     * @return array
     */
    public function getRedisConnection($connection = 'default') {
        if (!isset($this->config['redis'][$connection])) {
            throw new Exception("Redis connection '$connection' not found");
        }
        
        return $this->config['redis'][$connection];
    }
    
    /**
     * Test database connection
     * 
     * @param string|null $connection Connection name
     * @return array Connection test results
     */
    public function testConnection($connection = null) {
        $config = $this->getConnection($connection);
        
        $status = [
            'connection' => $connection ?? $this->getDefaultConnection(),
            'connected' => false,
            'host' => $config['host'],
            'database' => $config['database'],
            'username' => $config['username'],
            'charset' => $config['charset'],
            'error' => null,
            'version' => null,
            'latency' => null
        ];
        
        try {
            $startTime = microtime(true);
            
            $dsn = sprintf(
                "%s:host=%s;port=%s;dbname=%s;charset=%s",
                $config['driver'],
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );
            
            $pdo = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $config['options'] ?? []
            );
            
            // Test with a simple query
            $stmt = $pdo->query('SELECT VERSION() as version');
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $endTime = microtime(true);
            
            $status['connected'] = true;
            $status['version'] = $result['version'] ?? 'Unknown';
            $status['latency'] = round(($endTime - $startTime) * 1000, 2) . 'ms';
            
        } catch(PDOException $e) 
    error_log('Exception in DatabaseConfig.php: ' . $e->getMessage());
            $status['error'] = $e->getMessage();
        
        
        return $status;
    }
    
    /**
     * Get environment-specific configuration info
     * 
     * @return array
     */
    public function getEnvironmentInfo() {
        return [
            'environment' => $this->environment,
            'default_connection' => $this->getDefaultConnection(),
            'available_connections' => array_keys($this->config['connections']),
            'config_file_exists' => file_exists(dirname(__DIR__) . '/config/database.php'),
            'env_variables_loaded' => !empty($_ENV['DB_HOST'])
        ];
    }
    
    /**
     * Get connection from pool (with connection pooling)
     * 
     * @param string|null $connectionName Connection name
     * @return PDO Database connection
     */
    public function getPooledConnection($connectionName = null) {
        $connectionName = $connectionName ?? $this->getDefaultConnection();
        
        // Try to get connection from pool
        $connection = $this->getFromPool($connectionName);
        
        if ($connection !== null) {
            $this->poolStats[$connectionName]['pool_hits']++;
            return $connection;
        }
        
        // Create new connection if pool is empty
        $this->poolStats[$connectionName]['pool_misses']++;
        return $this->createNewConnection($connectionName);
    }
    
    /**
     * Get connection from pool
     * 
     * @param string $connectionName
     * @return PDO|null
     */
    private function getFromPool($connectionName) {
        if (!isset($this->connectionPool[$connectionName])) {
            return null;
        }
        
        $pool = &$this->connectionPool[$connectionName];
        
        // Remove expired connections
        $this->cleanupExpiredConnections($connectionName);
        
        // Get connection from pool
        if (!empty($pool)) {
            $connectionData = array_pop($pool);
            $connectionData['last_used'] = time();
            $this->poolStats[$connectionName]['idle_connections']--;
            $this->poolStats[$connectionName]['active_connections']++;
            
            return $connectionData['connection'];
        }
        
        return null;
    }
    
    /**
     * Create new database connection
     * 
     * @param string $connectionName
     * @return PDO
     */
    private function createNewConnection($connectionName) {
        $config = $this->getConnection($connectionName);
        
        $dsn = sprintf(
            "%s:host=%s;port=%s;dbname=%s;charset=%s",
            $config['driver'],
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );
        
        // Default PDO options
        $defaultOptions = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => false // Disable persistent connections for pooling
        ];
        
        // Merge with config options
        $options = array_merge($defaultOptions, $config['options'] ?? []);
        
        $connection = new PDO(
            $dsn,
            $config['username'],
            $config['password'],
            $options
        );
        
        $this->poolStats[$connectionName]['total_created']++;
        $this->poolStats[$connectionName]['active_connections']++;
        
        return $connection;
    }
    
    /**
     * Return connection to pool
     * 
     * @param PDO $connection
     * @param string $connectionName
     */
    public function returnToPool(PDO $connection, $connectionName = null) {
        $connectionName = $connectionName ?? $this->getDefaultConnection();
        
        // Check if pool is full
        if (count($this->connectionPool[$connectionName]) >= $this->poolConfig['max_connections']) {
            $this->poolStats[$connectionName]['total_destroyed']++;
            $connection = null; // Let garbage collector handle it
            return;
        }
        
        // Add to pool
        $this->connectionPool[$connectionName][] = [
            'connection' => $connection,
            'created_at' => time(),
            'last_used' => time()
        ];
        
        $this->poolStats[$connectionName]['active_connections']--;
        $this->poolStats[$connectionName]['idle_connections']++;
    }
    
    /**
     * Clean up expired connections from pool
     * 
     * @param string $connectionName
     */
    private function cleanupExpiredConnections($connectionName) {
        $pool = &$this->connectionPool[$connectionName];
        $currentTime = time();
        
        foreach ($pool as $index => $connectionData) {
            $age = $currentTime - $connectionData['created_at'];
            $idleTime = $currentTime - $connectionData['last_used'];
            
            if ($age > $this->poolConfig['max_lifetime'] || 
                $idleTime > $this->poolConfig['idle_timeout']) {
                
                unset($pool[$index]);
                $this->poolStats[$connectionName]['idle_connections']--;
                $this->poolStats[$connectionName]['total_destroyed']++;
            }
        }
        
        // Reindex array
        $pool = array_values($pool);
    }
    
    /**
     * Get connection pool statistics
     * 
     * @param string|null $connectionName
     * @return array
     */
    public function getPoolStats($connectionName = null) {
        if ($connectionName !== null) {
            return $this->poolStats[$connectionName] ?? [];
        }
        
        return $this->poolStats;
    }
    
    /**
     * Get connection pool configuration
     * 
     * @return array
     */
    public function getPoolConfig() {
        return $this->poolConfig;
    }
    
    /**
     * Get health monitor instance
     * 
     * @return DatabaseHealthMonitor|null
     */
    public function getHealthMonitor() {
        return $this->healthMonitor;
    }
    
    /**
     * Perform health check on all connections
     * 
     * @return array Health check results
     */
    public function performHealthCheck() {
        $results = [];
        
        foreach ($this->config['connections'] as $name => $config) {
            $results[$name] = $this->testConnection($name);
            $this->poolStats[$name]['health_status'] = $results[$name]['connected'] ? 'healthy' : 'unhealthy';
            $this->poolStats[$name]['last_health_check'] = date('Y-m-d H:i:s');
        }
        
        return $results;
    }
}
