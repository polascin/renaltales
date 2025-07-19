<?php

namespace RenalTales\Scripts\Optimize;

/**
 * Performance Monitor for Renal Tales Application
 *
 * This script analyzes database performance and provides optimization recommendations
 *
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

declare(strict_types=1);

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../src/Core/CacheManager.php';

class PerformanceMonitor
{
    private CacheManager $cache;
    private array $slowQueries = [];
    private array $recommendations = [];

    public function __construct()
    {
        $this->cache = new CacheManager();
    }

    /**
     * Run complete performance analysis
     */
    public function runAnalysis(): void
    {
        echo "🔍 Starting Performance Analysis...\n\n";

        $this->analyzeSlowQueries();
        $this->analyzeMissingIndexes();
        $this->analyzeCachePerformance();
        $this->analyzeTableStats();
        $this->generateRecommendations();

        echo "\n✅ Performance analysis complete!\n";
    }

    /**
     * Analyze slow queries from MySQL slow query log
     */
    private function analyzeSlowQueries(): void
    {
        echo "📊 Analyzing slow queries...\n";
        echo "  ℹ️  Database functionality removed - focusing on language processing\n";
        echo "  ℹ️  This analysis is no longer available\n";
    }

    /**
     * Analyze common queries for potential performance issues
     */
    private function analyzeCommonQueries(): void
    {
        $commonQueries = [
            "SELECT * FROM stories WHERE published = 1 ORDER BY created_at DESC LIMIT 10",
            "SELECT * FROM stories s JOIN story_categories sc ON s.id = sc.story_id WHERE sc.category_id = 1",
            "SELECT * FROM users WHERE email = 'test@example.com'",
            "SELECT COUNT(*) FROM stories WHERE published = 1",
            "SELECT * FROM translations WHERE language_code = 'sk'",
        ];

        foreach ($commonQueries as $query) {
            try {
                $start = microtime(true);
                $explanation = $this->db->explainQuery($query);
                $duration = microtime(true) - $start;

                if ($duration > 0.1) { // Queries taking more than 100ms
                    $this->slowQueries[] = [
                        'query' => $query,
                        'duration' => $duration,
                        'explanation' => $explanation
                    ];
                }

                echo "  - Query executed in " . round($duration * 1000, 2) . "ms\n";

            } catch (Exception $e) {
                echo "  ⚠️  Error analyzing query: " . $e->getMessage() . "\n";
            }
        }
    }

    /**
     * Analyze missing indexes
     */
    private function analyzeMissingIndexes(): void
    {
        echo "\n🔍 Analyzing indexes...\n";
        echo "  ℹ️  Database functionality removed - focusing on language processing\n";
        echo "  ℹ️  This analysis is no longer available\n";
    }

    /**
     * Recommend indexes for tables
     */
    private function recommendIndexes(string $table, array $existingIndexes): void
    {
        $recommendations = [];

        switch ($table) {
            case 'stories':
                if (!in_array('idx_published', $existingIndexes)) {
                    $recommendations[] = "CREATE INDEX idx_published ON stories (published)";
                }
                if (!in_array('idx_created_at', $existingIndexes)) {
                    $recommendations[] = "CREATE INDEX idx_created_at ON stories (created_at)";
                }
                if (!in_array('idx_title', $existingIndexes)) {
                    $recommendations[] = "CREATE INDEX idx_title ON stories (title)";
                }
                break;

            case 'users':
                if (!in_array('idx_email', $existingIndexes)) {
                    $recommendations[] = "CREATE UNIQUE INDEX idx_email ON users (email)";
                }
                if (!in_array('idx_username', $existingIndexes)) {
                    $recommendations[] = "CREATE UNIQUE INDEX idx_username ON users (username)";
                }
                break;

            case 'translations':
                if (!in_array('idx_language_code', $existingIndexes)) {
                    $recommendations[] = "CREATE INDEX idx_language_code ON translations (language_code)";
                }
                if (!in_array('idx_translation_key', $existingIndexes)) {
                    $recommendations[] = "CREATE INDEX idx_translation_key ON translations (translation_key)";
                }
                break;
        }

        foreach ($recommendations as $recommendation) {
            echo "    💡 Recommendation: {$recommendation}\n";
            $this->recommendations[] = $recommendation;
        }
    }

    /**
     * Analyze cache performance
     */
    private function analyzeCachePerformance(): void
    {
        echo "\n💾 Analyzing cache performance...\n";

        try {
            $stats = $this->cache->getPerformanceStats();

            echo "  - Query cache size: {$stats['query_cache_size']}\n";
            echo "  - Total hits: {$stats['total_hits']}\n";
            echo "  - Total misses: {$stats['total_misses']}\n";
            echo "  - Hit rate: {$stats['hit_rate']}%\n";

            if ($stats['hit_rate'] < 50) {
                $this->recommendations[] = "Cache hit rate is low ({$stats['hit_rate']}%). Consider increasing cache TTL or reviewing cache strategy.";
            }

        } catch (Exception $e) {
            echo "  ⚠️  Error analyzing cache: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Analyze table statistics
     */
    private function analyzeTableStats(): void
    {
        echo "\n📈 Analyzing table statistics...\n";
        echo "  ℹ️  Database functionality removed - focusing on language processing\n";
        echo "  ℹ️  This analysis is no longer available\n";
    }

    /**
     * Generate optimization recommendations
     */
    private function generateRecommendations(): void
    {
        echo "\n💡 Optimization Recommendations:\n";

        if (empty($this->recommendations)) {
            echo "  ✅ No critical performance issues found!\n";
            return;
        }

        foreach ($this->recommendations as $i => $recommendation) {
            echo "  " . ($i + 1) . ". {$recommendation}\n";
        }

        echo "\n📝 Generating optimization script...\n";
        $this->generateOptimizationScript();
    }

    /**
     * Generate SQL optimization script
     */
    private function generateOptimizationScript(): void
    {
        $script = "-- Performance Optimization Script\n";
        $script .= "-- Generated on " . date('Y-m-d H:i:s') . "\n\n";

        $script .= "-- Recommended Indexes\n";
        foreach ($this->recommendations as $recommendation) {
            if (strpos($recommendation, 'CREATE INDEX') !== false || strpos($recommendation, 'CREATE UNIQUE INDEX') !== false) {
                $script .= $recommendation . ";\n";
            }
        }

        $script .= "\n-- Query Optimization\n";
        $script .= "-- Consider these MySQL configuration optimizations:\n";
        $script .= "-- SET GLOBAL innodb_buffer_pool_size = '1G';\n";
        $script .= "-- SET GLOBAL query_cache_size = '256M';\n";
        $script .= "-- SET GLOBAL query_cache_type = 1;\n";

        $filename = __DIR__ . '/optimization_' . date('Y-m-d_H-i-s') . '.sql';
        file_put_contents($filename, $script);

        echo "  📄 Optimization script saved to: {$filename}\n";
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Test cache performance
     */
    public function testCachePerformance(): void
    {
        echo "\n🧪 Testing cache performance...\n";
        echo "  ℹ️  Database functionality removed - focusing on language processing\n";
        echo "  ℹ️  This analysis is no longer available\n";
    }
}

// Run the performance monitor
if (php_sapi_name() === 'cli') {
    $monitor = new PerformanceMonitor();
    $monitor->runAnalysis();
    $monitor->testCachePerformance();
} else {
    echo "This script should be run from the command line.";
}
