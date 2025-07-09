<?php

/**
 * RenalTales Production Optimizer
 * 
 * Comprehensive production optimization script that handles:
 * - Asset minification and compression
 * - Cache management
 * - Database optimization
 * - Image optimization
 * - Performance improvements
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

require_once dirname(__DIR__, 2) . '/bootstrap.php';

class ProductionOptimizer
{
    private $config;
    private $logger;
    private $baseDir;
    private $optimizations = [];
    
    public function __construct()
    {
        $this->config = require dirname(__DIR__, 2) . '/config/app.php';
        $this->logger = new Logger('optimizer');
        $this->baseDir = dirname(__DIR__, 2);
    }
    
    /**
     * Run all optimizations
     */
    public function runAllOptimizations(): array
    {
        $this->logger->info('Starting production optimization');
        
        $results = [
            'assets' => $this->optimizeAssets(),
            'cache' => $this->optimizeCache(),
            'database' => $this->optimizeDatabase(),
            'images' => $this->optimizeImages(),
            'config' => $this->optimizeConfiguration(),
            'cleanup' => $this->cleanup(),
        ];
        
        $this->logger->info('Production optimization completed');
        return $results;
    }
    
    /**
     * Optimize assets (CSS, JS minification and compression)
     */
    public function optimizeAssets(): array
    {
        $this->logger->info('Starting asset optimization');
        
        try {
            $results = [
                'css' => $this->optimizeCSS(),
                'js' => $this->optimizeJS(),
                'images' => $this->optimizeAssetImages(),
                'fonts' => $this->optimizeFonts(),
            ];
            
            $this->logger->info('Asset optimization completed');
            return ['status' => 'success', 'results' => $results];
            
        } catch (Exception $e) {
            $this->logger->error('Asset optimization failed', ['error' => $e->getMessage()]);
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Optimize CSS files
     */
    private function optimizeCSS(): array
    {
        $cssDir = $this->baseDir . '/public/assets/css';
        $optimizedDir = $cssDir . '/optimized';
        
        if (!is_dir($optimizedDir)) {
            mkdir($optimizedDir, 0755, true);
        }
        
        $cssFiles = glob($cssDir . '/*.css');
        $results = [];
        
        foreach ($cssFiles as $file) {
            $filename = basename($file);
            $optimizedFile = $optimizedDir . '/' . $filename;
            
            $originalContent = file_get_contents($file);
            $minifiedContent = $this->minifyCSS($originalContent);
            
            file_put_contents($optimizedFile, $minifiedContent);
            
            $originalSize = filesize($file);
            $optimizedSize = filesize($optimizedFile);
            $savings = $originalSize - $optimizedSize;
            
            $results[$filename] = [
                'original_size' => $originalSize,
                'optimized_size' => $optimizedSize,
                'savings' => $savings,
                'savings_percent' => round(($savings / $originalSize) * 100, 2),
            ];
        }
        
        // Create combined CSS file
        $combinedCSS = '';
        foreach ($cssFiles as $file) {
            $combinedCSS .= file_get_contents($file) . "\n";
        }
        
        $combinedMinified = $this->minifyCSS($combinedCSS);
        file_put_contents($optimizedDir . '/combined.css', $combinedMinified);
        
        return $results;
    }
    
    /**
     * Optimize JavaScript files
     */
    private function optimizeJS(): array
    {
        $jsDir = $this->baseDir . '/public/assets/js';
        $optimizedDir = $jsDir . '/optimized';
        
        if (!is_dir($optimizedDir)) {
            mkdir($optimizedDir, 0755, true);
        }
        
        $jsFiles = glob($jsDir . '/*.js');
        $results = [];
        
        foreach ($jsFiles as $file) {
            $filename = basename($file);
            $optimizedFile = $optimizedDir . '/' . $filename;
            
            $originalContent = file_get_contents($file);
            $minifiedContent = $this->minifyJS($originalContent);
            
            file_put_contents($optimizedFile, $minifiedContent);
            
            $originalSize = filesize($file);
            $optimizedSize = filesize($optimizedFile);
            $savings = $originalSize - $optimizedSize;
            
            $results[$filename] = [
                'original_size' => $originalSize,
                'optimized_size' => $optimizedSize,
                'savings' => $savings,
                'savings_percent' => round(($savings / $originalSize) * 100, 2),
            ];
        }
        
        // Create combined JS file
        $combinedJS = '';
        foreach ($jsFiles as $file) {
            $combinedJS .= file_get_contents($file) . "\n";
        }
        
        $combinedMinified = $this->minifyJS($combinedJS);
        file_put_contents($optimizedDir . '/combined.js', $combinedMinified);
        
        return $results;
    }
    
    /**
     * Optimize asset images
     */
    private function optimizeAssetImages(): array
    {
        $imageDir = $this->baseDir . '/public/assets/images';
        $optimizedDir = $imageDir . '/optimized';
        
        if (!is_dir($optimizedDir)) {
            mkdir($optimizedDir, 0755, true);
        }
        
        $imageFiles = glob($imageDir . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
        $results = [];
        
        foreach ($imageFiles as $file) {
            $filename = basename($file);
            $optimizedFile = $optimizedDir . '/' . $filename;
            
            $originalSize = filesize($file);
            $optimized = $this->optimizeImage($file, $optimizedFile);
            
            if ($optimized) {
                $optimizedSize = filesize($optimizedFile);
                $savings = $originalSize - $optimizedSize;
                
                $results[$filename] = [
                    'original_size' => $originalSize,
                    'optimized_size' => $optimizedSize,
                    'savings' => $savings,
                    'savings_percent' => round(($savings / $originalSize) * 100, 2),
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Optimize fonts
     */
    private function optimizeFonts(): array
    {
        $fontsDir = $this->baseDir . '/public/assets/fonts';
        $results = [];
        
        if (is_dir($fontsDir)) {
            $fontFiles = glob($fontsDir . '/*.{woff,woff2,ttf,otf}', GLOB_BRACE);
            
            foreach ($fontFiles as $file) {
                $filename = basename($file);
                $size = filesize($file);
                
                $results[$filename] = [
                    'size' => $size,
                    'format' => pathinfo($file, PATHINFO_EXTENSION),
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Optimize cache system
     */
    public function optimizeCache(): array
    {
        $this->logger->info('Starting cache optimization');
        
        try {
            $results = [
                'clear_old' => $this->clearOldCache(),
                'warm_cache' => $this->warmCache(),
                'optimize_structure' => $this->optimizeCacheStructure(),
            ];
            
            $this->logger->info('Cache optimization completed');
            return ['status' => 'success', 'results' => $results];
            
        } catch (Exception $e) {
            $this->logger->error('Cache optimization failed', ['error' => $e->getMessage()]);
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Clear old cache files
     */
    private function clearOldCache(): array
    {
        $cacheDir = $this->baseDir . '/storage/cache';
        $deletedFiles = 0;
        $deletedSize = 0;
        
        if (is_dir($cacheDir)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($cacheDir, RecursiveDirectoryIterator::SKIP_DOTS)
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $age = time() - $file->getMTime();
                    
                    // Delete files older than 7 days
                    if ($age > 604800) {
                        $size = $file->getSize();
                        unlink($file->getRealPath());
                        $deletedFiles++;
                        $deletedSize += $size;
                    }
                }
            }
        }
        
        return [
            'deleted_files' => $deletedFiles,
            'deleted_size' => $deletedSize,
            'deleted_size_human' => $this->formatBytes($deletedSize),
        ];
    }
    
    /**
     * Warm cache with frequently accessed data
     */
    private function warmCache(): array
    {
        $cachedItems = 0;
        
        // Cache language files
        $langDir = $this->baseDir . '/resources/lang';
        if (is_dir($langDir)) {
            $langFiles = glob($langDir . '/*.php');
            foreach ($langFiles as $file) {
                $lang = basename($file, '.php');
                $cacheFile = $this->baseDir . '/storage/cache/lang_' . $lang . '.cache';
                
                if (!file_exists($cacheFile) || filemtime($file) > filemtime($cacheFile)) {
                    $content = include $file;
                    file_put_contents($cacheFile, serialize($content));
                    $cachedItems++;
                }
            }
        }
        
        // Cache configuration files
        $configDir = $this->baseDir . '/config';
        if (is_dir($configDir)) {
            $configFiles = glob($configDir . '/*.php');
            foreach ($configFiles as $file) {
                $config = basename($file, '.php');
                $cacheFile = $this->baseDir . '/storage/cache/config_' . $config . '.cache';
                
                if (!file_exists($cacheFile) || filemtime($file) > filemtime($cacheFile)) {
                    $content = include $file;
                    file_put_contents($cacheFile, serialize($content));
                    $cachedItems++;
                }
            }
        }
        
        return [
            'cached_items' => $cachedItems,
        ];
    }
    
    /**
     * Optimize cache directory structure
     */
    private function optimizeCacheStructure(): array
    {
        $cacheDir = $this->baseDir . '/storage/cache';
        $subdirs = ['views', 'routes', 'config', 'lang', 'assets'];
        
        foreach ($subdirs as $subdir) {
            $path = $cacheDir . '/' . $subdir;
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
        
        return [
            'subdirectories_created' => count($subdirs),
        ];
    }
    
    /**
     * Optimize database
     */
    public function optimizeDatabase(): array
    {
        $this->logger->info('Starting database optimization');
        
        try {
            $dbConfig = $this->config['database']['connections']['mysql'];
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $dbConfig['host'],
                $dbConfig['port'],
                $dbConfig['database'],
                $dbConfig['charset']
            );
            
            $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
            
            $results = [
                'tables_optimized' => $this->optimizeTables($pdo),
                'indexes_analyzed' => $this->analyzeIndexes($pdo),
                'cleanup_performed' => $this->cleanupDatabase($pdo),
            ];
            
            $this->logger->info('Database optimization completed');
            return ['status' => 'success', 'results' => $results];
            
        } catch (Exception $e) {
            $this->logger->error('Database optimization failed', ['error' => $e->getMessage()]);
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Optimize database tables
     */
    private function optimizeTables(PDO $pdo): array
    {
        $stmt = $pdo->query('SHOW TABLES');
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $results = [];
        
        foreach ($tables as $table) {
            $optimizeStmt = $pdo->prepare("OPTIMIZE TABLE `$table`");
            $optimizeStmt->execute();
            
            $analyzeStmt = $pdo->prepare("ANALYZE TABLE `$table`");
            $analyzeStmt->execute();
            
            $results[$table] = 'optimized';
        }
        
        return $results;
    }
    
    /**
     * Analyze database indexes
     */
    private function analyzeIndexes(PDO $pdo): array
    {
        $database = $this->config['database']['connections']['mysql']['database'];
        
        $stmt = $pdo->prepare("
            SELECT 
                TABLE_NAME,
                INDEX_NAME,
                NON_UNIQUE,
                SEQ_IN_INDEX,
                COLUMN_NAME,
                CARDINALITY
            FROM INFORMATION_SCHEMA.STATISTICS 
            WHERE TABLE_SCHEMA = ? 
            ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX
        ");
        
        $stmt->execute([$database]);
        $indexes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $results = [];
        foreach ($indexes as $index) {
            $table = $index['TABLE_NAME'];
            $indexName = $index['INDEX_NAME'];
            
            if (!isset($results[$table])) {
                $results[$table] = [];
            }
            
            if (!isset($results[$table][$indexName])) {
                $results[$table][$indexName] = [];
            }
            
            $results[$table][$indexName][] = $index['COLUMN_NAME'];
        }
        
        return $results;
    }
    
    /**
     * Cleanup database
     */
    private function cleanupDatabase(PDO $pdo): array
    {
        $results = [];
        
        // Clean up old sessions
        $stmt = $pdo->prepare("DELETE FROM sessions WHERE last_activity < ?");
        $stmt->execute([time() - 86400]); // 24 hours ago
        $results['old_sessions'] = $stmt->rowCount();
        
        // Clean up old password reset tokens
        $stmt = $pdo->prepare("DELETE FROM password_resets WHERE created_at < ?");
        $stmt->execute([date('Y-m-d H:i:s', time() - 3600)]); // 1 hour ago
        $results['old_password_resets'] = $stmt->rowCount();
        
        // Clean up old logs
        $stmt = $pdo->prepare("DELETE FROM logs WHERE created_at < ?");
        $stmt->execute([date('Y-m-d H:i:s', time() - 2592000)]); // 30 days ago
        $results['old_logs'] = $stmt->rowCount();
        
        return $results;
    }
    
    /**
     * Optimize images
     */
    public function optimizeImages(): array
    {
        $this->logger->info('Starting image optimization');
        
        try {
            $uploadDir = $this->baseDir . '/storage/uploads';
            $optimizedDir = $uploadDir . '/optimized';
            
            if (!is_dir($optimizedDir)) {
                mkdir($optimizedDir, 0755, true);
            }
            
            $imageFiles = glob($uploadDir . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
            $results = [];
            
            foreach ($imageFiles as $file) {
                $filename = basename($file);
                $optimizedFile = $optimizedDir . '/' . $filename;
                
                $originalSize = filesize($file);
                $optimized = $this->optimizeImage($file, $optimizedFile);
                
                if ($optimized) {
                    $optimizedSize = filesize($optimizedFile);
                    $savings = $originalSize - $optimizedSize;
                    
                    $results[$filename] = [
                        'original_size' => $originalSize,
                        'optimized_size' => $optimizedSize,
                        'savings' => $savings,
                        'savings_percent' => round(($savings / $originalSize) * 100, 2),
                    ];
                }
            }
            
            $this->logger->info('Image optimization completed');
            return ['status' => 'success', 'results' => $results];
            
        } catch (Exception $e) {
            $this->logger->error('Image optimization failed', ['error' => $e->getMessage()]);
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Optimize configuration
     */
    public function optimizeConfiguration(): array
    {
        $this->logger->info('Starting configuration optimization');
        
        try {
            $results = [
                'opcache' => $this->optimizeOpcache(),
                'php_settings' => $this->optimizePHPSettings(),
                'app_config' => $this->optimizeAppConfig(),
            ];
            
            $this->logger->info('Configuration optimization completed');
            return ['status' => 'success', 'results' => $results];
            
        } catch (Exception $e) {
            $this->logger->error('Configuration optimization failed', ['error' => $e->getMessage()]);
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Optimize OPcache settings
     */
    private function optimizeOpcache(): array
    {
        $results = [];
        
        if (extension_loaded('opcache')) {
            $status = opcache_get_status();
            $config = opcache_get_configuration();
            
            $results['enabled'] = $status['opcache_enabled'];
            $results['memory_usage'] = $status['memory_usage'];
            $results['hit_rate'] = round($status['opcache_statistics']['opcache_hit_rate'], 2);
            $results['cached_scripts'] = $status['opcache_statistics']['num_cached_scripts'];
            
            // Reset OPcache for optimization
            if (function_exists('opcache_reset')) {
                opcache_reset();
                $results['reset'] = true;
            }
        } else {
            $results['error'] = 'OPcache extension not loaded';
        }
        
        return $results;
    }
    
    /**
     * Optimize PHP settings
     */
    private function optimizePHPSettings(): array
    {
        $results = [];
        
        // Memory limit
        $memoryLimit = ini_get('memory_limit');
        $results['memory_limit'] = $memoryLimit;
        
        // Max execution time
        $maxExecutionTime = ini_get('max_execution_time');
        $results['max_execution_time'] = $maxExecutionTime;
        
        // Upload settings
        $results['upload_max_filesize'] = ini_get('upload_max_filesize');
        $results['post_max_size'] = ini_get('post_max_size');
        
        // Session settings
        $results['session_gc_maxlifetime'] = ini_get('session.gc_maxlifetime');
        $results['session_save_handler'] = ini_get('session.save_handler');
        
        return $results;
    }
    
    /**
     * Optimize application configuration
     */
    private function optimizeAppConfig(): array
    {
        $results = [];
        
        // Environment check
        $results['environment'] = $this->config['app']['environment'];
        $results['debug'] = $this->config['app']['debug'];
        
        // Cache settings
        $results['cache_driver'] = $this->config['cache']['default'];
        
        // Database settings
        $results['database_driver'] = $this->config['database']['default'];
        
        // Logging settings
        $results['log_level'] = $this->config['logging']['channels']['file']['level'];
        
        return $results;
    }
    
    /**
     * Cleanup temporary files and optimize storage
     */
    public function cleanup(): array
    {
        $this->logger->info('Starting cleanup');
        
        try {
            $results = [
                'temp_files' => $this->cleanupTempFiles(),
                'logs' => $this->cleanupLogs(),
                'sessions' => $this->cleanupSessions(),
                'storage' => $this->optimizeStorage(),
            ];
            
            $this->logger->info('Cleanup completed');
            return ['status' => 'success', 'results' => $results];
            
        } catch (Exception $e) {
            $this->logger->error('Cleanup failed', ['error' => $e->getMessage()]);
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Clean up temporary files
     */
    private function cleanupTempFiles(): array
    {
        $tempDir = $this->baseDir . '/storage/temp';
        $deletedFiles = 0;
        $deletedSize = 0;
        
        if (is_dir($tempDir)) {
            $files = glob($tempDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    $age = time() - filemtime($file);
                    
                    // Delete files older than 1 hour
                    if ($age > 3600) {
                        $size = filesize($file);
                        unlink($file);
                        $deletedFiles++;
                        $deletedSize += $size;
                    }
                }
            }
        }
        
        return [
            'deleted_files' => $deletedFiles,
            'deleted_size' => $deletedSize,
            'deleted_size_human' => $this->formatBytes($deletedSize),
        ];
    }
    
    /**
     * Clean up old log files
     */
    private function cleanupLogs(): array
    {
        $logDir = $this->baseDir . '/storage/logs';
        $deletedFiles = 0;
        $deletedSize = 0;
        
        if (is_dir($logDir)) {
            $files = glob($logDir . '/*.log');
            foreach ($files as $file) {
                $age = time() - filemtime($file);
                
                // Delete log files older than 30 days
                if ($age > 2592000) {
                    $size = filesize($file);
                    unlink($file);
                    $deletedFiles++;
                    $deletedSize += $size;
                }
            }
        }
        
        return [
            'deleted_files' => $deletedFiles,
            'deleted_size' => $deletedSize,
            'deleted_size_human' => $this->formatBytes($deletedSize),
        ];
    }
    
    /**
     * Clean up old session files
     */
    private function cleanupSessions(): array
    {
        $sessionDir = $this->baseDir . '/storage/sessions';
        $deletedFiles = 0;
        $deletedSize = 0;
        
        if (is_dir($sessionDir)) {
            $files = glob($sessionDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    $age = time() - filemtime($file);
                    
                    // Delete session files older than 24 hours
                    if ($age > 86400) {
                        $size = filesize($file);
                        unlink($file);
                        $deletedFiles++;
                        $deletedSize += $size;
                    }
                }
            }
        }
        
        return [
            'deleted_files' => $deletedFiles,
            'deleted_size' => $deletedSize,
            'deleted_size_human' => $this->formatBytes($deletedSize),
        ];
    }
    
    /**
     * Optimize storage structure
     */
    private function optimizeStorage(): array
    {
        $storageDir = $this->baseDir . '/storage';
        $subdirs = ['cache', 'logs', 'sessions', 'uploads', 'temp', 'backups'];
        
        foreach ($subdirs as $subdir) {
            $path = $storageDir . '/' . $subdir;
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
        
        return [
            'directories_created' => count($subdirs),
        ];
    }
    
    /**
     * Utility methods
     */
    private function minifyCSS(string $css): string
    {
        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // Remove whitespace
        $css = str_replace(["\r\n", "\r", "\n", "\t"], '', $css);
        
        // Remove extra spaces
        $css = preg_replace('/\s+/', ' ', $css);
        
        // Remove spaces around certain characters
        $css = str_replace([' {', '{ ', ' }', '} ', ' :', ': ', ' ;', '; ', ' ,', ', '], ['{', '{', '}', '}', ':', ':', ';', ';', ',', ','], $css);
        
        return trim($css);
    }
    
    private function minifyJS(string $js): string
    {
        // Remove single-line comments
        $js = preg_replace('/\/\/.*$/m', '', $js);
        
        // Remove multi-line comments
        $js = preg_replace('/\/\*[\s\S]*?\*\//', '', $js);
        
        // Remove whitespace
        $js = str_replace(["\r\n", "\r", "\n", "\t"], '', $js);
        
        // Remove extra spaces
        $js = preg_replace('/\s+/', ' ', $js);
        
        return trim($js);
    }
    
    private function optimizeImage(string $source, string $destination): bool
    {
        $info = getimagesize($source);
        if (!$info) {
            return false;
        }
        
        $mime = $info['mime'];
        
        switch ($mime) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($source);
                return imagejpeg($image, $destination, 85);
                
            case 'image/png':
                $image = imagecreatefrompng($source);
                imagesavealpha($image, true);
                return imagepng($image, $destination, 6);
                
            case 'image/gif':
                $image = imagecreatefromgif($source);
                return imagegif($image, $destination);
                
            default:
                return copy($source, $destination);
        }
    }
    
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

// CLI Usage
if (php_sapi_name() === 'cli') {
    $optimizer = new ProductionOptimizer();
    
    $command = $argv[1] ?? 'help';
    
    switch ($command) {
        case 'all':
            echo "Running all optimizations...\n";
            $results = $optimizer->runAllOptimizations();
            echo json_encode($results, JSON_PRETTY_PRINT) . "\n";
            break;
            
        case 'assets':
            echo "Optimizing assets...\n";
            $results = $optimizer->optimizeAssets();
            echo json_encode($results, JSON_PRETTY_PRINT) . "\n";
            break;
            
        case 'cache':
            echo "Optimizing cache...\n";
            $results = $optimizer->optimizeCache();
            echo json_encode($results, JSON_PRETTY_PRINT) . "\n";
            break;
            
        case 'database':
            echo "Optimizing database...\n";
            $results = $optimizer->optimizeDatabase();
            echo json_encode($results, JSON_PRETTY_PRINT) . "\n";
            break;
            
        case 'images':
            echo "Optimizing images...\n";
            $results = $optimizer->optimizeImages();
            echo json_encode($results, JSON_PRETTY_PRINT) . "\n";
            break;
            
        case 'cleanup':
            echo "Running cleanup...\n";
            $results = $optimizer->cleanup();
            echo json_encode($results, JSON_PRETTY_PRINT) . "\n";
            break;
            
        case 'help':
        default:
            echo "RenalTales Production Optimizer\n";
            echo "Usage: php production-optimizer.php <command>\n\n";
            echo "Commands:\n";
            echo "  all        - Run all optimizations\n";
            echo "  assets     - Optimize CSS/JS assets\n";
            echo "  cache      - Optimize cache system\n";
            echo "  database   - Optimize database\n";
            echo "  images     - Optimize images\n";
            echo "  cleanup    - Clean up temporary files\n";
            echo "  help       - Show this help message\n";
            break;
    }
}
