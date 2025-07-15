<?php

declare(strict_types=1);

namespace RenalTales\Services;

use RenalTales\Core\CacheManager;
use RenalTales\Core\AsyncManager;
use RenalTales\Core\Logger;
use React\Promise\PromiseInterface;

/**
 * Performance Service
 *
 * Monitors and optimizes application performance through caching,
 * metrics collection, and performance analysis.
 *
 * @package RenalTales\Services
 * @version 2025.3.1.dev
 * @author Ľubomír Polaščín
 */
class PerformanceService
{
    /**
     * @var CacheManager Cache manager
     */
    private CacheManager $cache;

    /**
     * @var AsyncManager|null Async manager
     */
    private ?AsyncManager $asyncManager = null;

    /**
     * @var Logger|null Logger instance
     */
    private ?Logger $logger = null;

    /**
     * @var array<string, float> Performance metrics
     */
    private array $metrics = [];

    /**
     * @var array<string, int> Hit counters
     */
    private array $hitCounters = [];

    /**
     * @var array<string, float> Timers
     */
    private array $timers = [];

    /**
     * Constructor
     *
     * @param CacheManager $cache Cache manager
     * @param AsyncManager|null $asyncManager Async manager
     * @param Logger|null $logger Logger instance
     */
    public function __construct(
        CacheManager $cache,
        ?AsyncManager $asyncManager = null,
        ?Logger $logger = null
    ) {
        $this->cache = $cache;
        $this->asyncManager = $asyncManager;
        $this->logger = $logger;
    }

    /**
     * Start performance timer
     *
     * @param string $name Timer name
     * @return void
     */
    public function startTimer(string $name): void
    {
        $this->timers[$name] = microtime(true);
    }

    /**
     * Stop performance timer
     *
     * @param string $name Timer name
     * @return float Elapsed time in seconds
     */
    public function stopTimer(string $name): float
    {
        if (!isset($this->timers[$name])) {
            return 0.0;
        }

        $elapsed = microtime(true) - $this->timers[$name];
        unset($this->timers[$name]);

        // Store metric
        $this->metrics[$name] = $elapsed;
        $this->log("Timer '{$name}' completed in {$elapsed}s");

        return $elapsed;
    }

    /**
     * Record performance metric
     *
     * @param string $name Metric name
     * @param float $value Metric value
     * @return void
     */
    public function recordMetric(string $name, float $value): void
    {
        $this->metrics[$name] = $value;
        
        // Cache metric for persistence
        $this->cache->set("metric_{$name}", $value, 3600);
    }

    /**
     * Increment hit counter
     *
     * @param string $name Counter name
     * @return int New counter value
     */
    public function incrementHitCounter(string $name): int
    {
        if (!isset($this->hitCounters[$name])) {
            $this->hitCounters[$name] = 0;
        }

        $this->hitCounters[$name]++;
        
        // Update cached counter
        $this->cache->increment("counter_{$name}");

        return $this->hitCounters[$name];
    }

    /**
     * Get performance metrics
     *
     * @return array<string, mixed> Performance metrics
     */
    public function getMetrics(): array
    {
        return [
            'timers' => $this->metrics,
            'counters' => $this->hitCounters,
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'cache_stats' => $this->cache->getStats(),
        ];
    }

    /**
     * Get cached metrics
     *
     * @return array<string, mixed> Cached metrics
     */
    public function getCachedMetrics(): array
    {
        $cacheKeys = [
            'cache_hits',
            'cache_misses',
            'database_queries',
            'response_times',
        ];

        $metrics = [];
        foreach ($cacheKeys as $key) {
            $value = $this->cache->get("metric_{$key}");
            if ($value !== null) {
                $metrics[$key] = $value;
            }
        }

        return $metrics;
    }

    /**
     * Optimize database queries
     *
     * @param string $query SQL query
     * @param array<mixed> $params Query parameters
     * @return PromiseInterface<mixed>|mixed
     */
    public function optimizeQuery(string $query, array $params = [])
    {
        $this->startTimer('query_execution');

        // Check if query can be cached
        if ($this->isQueryCacheable($query)) {
            $cacheKey = 'query_' . md5($query . serialize($params));
            
            $cached = $this->cache->get($cacheKey);
            if ($cached !== null) {
                $this->incrementHitCounter('cache_hits');
                $this->stopTimer('query_execution');
                return $cached;
            }
        }

        // Execute query asynchronously if possible
        if ($this->asyncManager) {
            return $this->asyncManager->query($query, $params)
                ->then(function ($result) use ($query, $params) {
                    $this->stopTimer('query_execution');
                    $this->incrementHitCounter('database_queries');
                    
                    // Cache result if query is cacheable
                    if ($this->isQueryCacheable($query)) {
                        $cacheKey = 'query_' . md5($query . serialize($params));
                        $this->cache->set($cacheKey, $result, 1800); // Cache for 30 minutes
                    }
                    
                    return $result;
                });
        }

        // Synchronous fallback
        $this->stopTimer('query_execution');
        $this->incrementHitCounter('database_queries');
        
        // In a real implementation, this would execute the query
        // For now, we'll return a placeholder
        return [];
    }

    /**
     * Preload critical data
     *
     * @return PromiseInterface<void>
     */
    public function preloadCriticalData(): PromiseInterface
    {
        $this->startTimer('preload_data');

        $tasks = [
            'preload_languages',
            'preload_translations',
            'preload_config',
        ];

        if ($this->asyncManager) {
            $promises = [];
            foreach ($tasks as $task) {
                $promises[] = $this->asyncManager->executeTask($task);
            }

            return \React\Promise\all($promises)
                ->then(function () {
                    $this->stopTimer('preload_data');
                    $this->log('Critical data preloaded successfully');
                });
        }

        // Synchronous fallback
        $this->stopTimer('preload_data');
        return \React\Promise\resolve();
    }

    /**
     * Optimize cache usage
     *
     * @return array<string, mixed> Cache optimization results
     */
    public function optimizeCache(): array
    {
        $stats = $this->cache->getStats();
        $optimizations = [];

        // Check cache hit ratio
        if (isset($stats['redis_info']['keyspace_hits'], $stats['redis_info']['keyspace_misses'])) {
            $hits = $stats['redis_info']['keyspace_hits'];
            $misses = $stats['redis_info']['keyspace_misses'];
            $total = $hits + $misses;
            
            if ($total > 0) {
                $hitRatio = $hits / $total;
                $optimizations['hit_ratio'] = $hitRatio;
                
                if ($hitRatio < 0.8) {
                    $optimizations['recommendations'][] = 'Consider increasing cache TTL or warming up cache';
                }
            }
        }

        // Check memory usage
        if (isset($stats['redis_info']['used_memory'], $stats['redis_info']['maxmemory'])) {
            $used = $stats['redis_info']['used_memory'];
            $max = $stats['redis_info']['maxmemory'];
            
            if ($max > 0) {
                $memoryUsage = $used / $max;
                $optimizations['memory_usage'] = $memoryUsage;
                
                if ($memoryUsage > 0.8) {
                    $optimizations['recommendations'][] = 'Consider increasing Redis memory limit or implementing LRU eviction';
                }
            }
        }

        return $optimizations;
    }

    /**
     * Monitor response times
     *
     * @param string $endpoint Endpoint name
     * @param float $responseTime Response time in seconds
     * @return void
     */
    public function monitorResponseTime(string $endpoint, float $responseTime): void
    {
        $metricName = "response_time_{$endpoint}";
        $this->recordMetric($metricName, $responseTime);

        // Check for slow responses
        if ($responseTime > 1.0) {
            $this->log("Slow response detected for {$endpoint}: {$responseTime}s", 'warning');
            
            // Record slow response
            $this->incrementHitCounter("slow_responses_{$endpoint}");
        }

        // Update average response time
        $this->updateAverageResponseTime($endpoint, $responseTime);
    }

    /**
     * Get performance recommendations
     *
     * @return array<string> Performance recommendations
     */
    public function getRecommendations(): array
    {
        $recommendations = [];
        $metrics = $this->getMetrics();

        // Check memory usage
        $memoryUsage = $metrics['memory_usage'] / (1024 * 1024); // Convert to MB
        if ($memoryUsage > 128) {
            $recommendations[] = 'High memory usage detected. Consider optimizing data structures or enabling object pooling.';
        }

        // Check cache performance
        $cacheStats = $this->optimizeCache();
        if (isset($cacheStats['recommendations'])) {
            $recommendations = array_merge($recommendations, $cacheStats['recommendations']);
        }

        // Check for slow queries
        $slowQueries = $this->hitCounters['slow_queries'] ?? 0;
        if ($slowQueries > 10) {
            $recommendations[] = 'Multiple slow queries detected. Consider adding database indexes or query optimization.';
        }

        return $recommendations;
    }

    /**
     * Generate performance report
     *
     * @return array<string, mixed> Performance report
     */
    public function generateReport(): array
    {
        return [
            'timestamp' => date('Y-m-d H:i:s'),
            'metrics' => $this->getMetrics(),
            'cached_metrics' => $this->getCachedMetrics(),
            'cache_optimization' => $this->optimizeCache(),
            'recommendations' => $this->getRecommendations(),
            'system_info' => [
                'php_version' => PHP_VERSION,
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'opcache_enabled' => extension_loaded('opcache') && opcache_get_status()['opcache_enabled'],
            ],
        ];
    }

    /**
     * Clear performance metrics
     *
     * @return void
     */
    public function clearMetrics(): void
    {
        $this->metrics = [];
        $this->hitCounters = [];
        $this->timers = [];
        
        // Clear cached metrics
        $this->cache->deleteMultiple([
            'metric_cache_hits',
            'metric_cache_misses',
            'metric_database_queries',
            'metric_response_times',
        ]);
    }

    /**
     * Check if query is cacheable
     *
     * @param string $query SQL query
     * @return bool True if cacheable, false otherwise
     */
    private function isQueryCacheable(string $query): bool
    {
        $query = strtolower(trim($query));
        
        // Only cache SELECT queries
        if (strpos($query, 'select') !== 0) {
            return false;
        }

        // Don't cache queries with certain keywords
        $uncacheableKeywords = ['now()', 'current_timestamp', 'rand()', 'uuid()'];
        foreach ($uncacheableKeywords as $keyword) {
            if (strpos($query, $keyword) !== false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Update average response time
     *
     * @param string $endpoint Endpoint name
     * @param float $responseTime Response time in seconds
     * @return void
     */
    private function updateAverageResponseTime(string $endpoint, float $responseTime): void
    {
        $avgKey = "avg_response_time_{$endpoint}";
        $countKey = "response_count_{$endpoint}";
        
        $currentAvg = $this->cache->get($avgKey) ?? 0.0;
        $currentCount = $this->cache->get($countKey) ?? 0;
        
        $newCount = $currentCount + 1;
        $newAvg = (($currentAvg * $currentCount) + $responseTime) / $newCount;
        
        $this->cache->set($avgKey, $newAvg, 3600);
        $this->cache->set($countKey, $newCount, 3600);
    }

    /**
     * Log a message
     *
     * @param string $message Log message
     * @param string $level Log level
     * @return void
     */
    private function log(string $message, string $level = 'info'): void
    {
        if ($this->logger) {
            $this->logger->log($level, $message);
        }
    }
}
