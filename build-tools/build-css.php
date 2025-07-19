<?php

declare(strict_types=1);

/**
 * CSS Build Optimization Script
 *
 * Handles CSS compilation, optimization, and critical CSS generation
 * for the RenalTales application
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/src/Helpers/CSSOptimizer.php';

use RenalTales\Helpers\CSSOptimizer;

class CSSBuilder
{
    private string $rootDir;
    private string $publicDir;
    private string $distDir;
    private string $criticalDir;
    private bool $isProduction;

    public function __construct()
    {
        $this->rootDir = dirname(__DIR__);
        $this->publicDir = $this->rootDir . '/public/assets/css';
        $this->distDir = $this->publicDir . '/dist';
        $this->criticalDir = $this->publicDir . '/critical';
        $this->isProduction = (bool) ($_ENV['NODE_ENV'] === 'production');

        // Ensure directories exist
        $this->ensureDirectories();
    }

    /**
     * Run the complete CSS build process
     */
    public function build(): void
    {
        echo "🎨 Starting CSS build process...\n";

        try {
            // Clear previous builds
            $this->cleanup();

            // Compile main CSS
            $this->compileMainCSS();

            // Generate critical CSS
            $this->generateCriticalCSS();

            // Optimize for production
            if ($this->isProduction) {
                $this->optimizeForProduction();
            }

            // Generate development assets
            $this->generateDevelopmentAssets();

            // Create build timestamp
            $this->createTimestamp();

            echo "✅ CSS build completed successfully!\n";
            $this->printStats();

        } catch (Exception $e) {
            echo "❌ CSS build failed: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    /**
     * Ensure required directories exist
     */
    private function ensureDirectories(): void
    {
        $directories = [$this->distDir, $this->criticalDir];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }

    /**
     * Clean up previous builds
     */
    private function cleanup(): void
    {
        echo "🧹 Cleaning up previous builds...\n";

        $patterns = [
            $this->distDir . '/*.css',
            $this->distDir . '/*.map',
            $this->distDir . '/.timestamp'
        ];

        foreach ($patterns as $pattern) {
            $files = glob($pattern);
            if ($files) {
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }
        }
    }

    /**
     * Compile main CSS file
     */
    private function compileMainCSS(): void
    {
        echo "📦 Compiling main CSS...\n";

        $mainCSSPath = $this->rootDir . '/main.css';
        if (!file_exists($mainCSSPath)) {
            throw new Exception("Main CSS file not found: $mainCSSPath");
        }

        // Read main CSS and process @import statements
        $css = $this->processImports($mainCSSPath);

        // Add performance optimizations
        $performanceCSSPath = $this->publicDir . '/performance.css';
        if (file_exists($performanceCSSPath)) {
            $css .= "\n\n" . file_get_contents($performanceCSSPath);
        }

        // Write compiled CSS
        $compiledPath = $this->distDir . '/compiled.css';
        file_put_contents($compiledPath, $css);

        echo "  ✓ Compiled CSS written to: " . basename($compiledPath) . "\n";
    }

    /**
     * Process @import statements recursively
     */
    private function processImports(string $cssFile, array $processed = []): string
    {
        if (in_array($cssFile, $processed)) {
            return "/* Circular import detected: $cssFile */\n";
        }

        $processed[] = $cssFile;

        if (!file_exists($cssFile)) {
            return "/* File not found: $cssFile */\n";
        }

        $css = file_get_contents($cssFile);
        $basePath = dirname($cssFile);

        // Process @import statements
        $css = preg_replace_callback(
            '/@import\s+url\([\'"]?([^\'")]+)[\'"]?\)\s*;?/',
            function ($matches) use ($basePath, $processed) {
                $importPath = $matches[1];

                // Handle relative paths
                if (!preg_match('/^https?:\/\//', $importPath)) {
                    $fullPath = $basePath . '/' . $importPath;
                    if (file_exists($fullPath)) {
                        return $this->processImports($fullPath, $processed);
                    }
                }

                return $matches[0]; // Keep original if external or not found
            },
            $css
        );

        return $css;
    }

    /**
     * Generate critical CSS for different pages
     */
    private function generateCriticalCSS(): void
    {
        echo "🎯 Generating critical CSS...\n";

        $pages = [
            'home' => $this->rootDir . '/src/Views/HomeView.php',
            'default' => $this->rootDir . '/src/Views/AbstractView.php'
        ];

        foreach ($pages as $pageName => $viewFile) {
            if (file_exists($viewFile)) {
                $criticalCSS = $this->extractCriticalCSS($viewFile);
                $criticalFile = $this->criticalDir . '/' . $pageName . '.css';

                // Use existing critical CSS if available, or generate basic one
                if (file_exists($criticalFile)) {
                    echo "  ✓ Using existing critical CSS for: $pageName\n";
                } else {
                    file_put_contents($criticalFile, $criticalCSS);
                    echo "  ✓ Generated critical CSS for: $pageName\n";
                }
            }
        }
    }

    /**
     * Extract critical CSS based on view file analysis
     */
    private function extractCriticalCSS(string $viewFile): string
    {
        // Simple critical CSS extraction based on common patterns
        $compiledCSS = file_get_contents($this->distDir . '/compiled.css');

        $criticalSelectors = [
            'html', 'body', '*', '*::before', '*::after',
            '.container', '', '.hero-section',
            '.nav-menu', '', '', '',
            '.site-title', '.site-tagline', '.hero-title', '.hero-intro',
            'h1', 'h2', 'h3', 'p', 'a'
        ];

        $criticalCSS = "/* Critical CSS - Generated automatically */\n\n";

        // Extract CSS rules for critical selectors
        foreach ($criticalSelectors as $selector) {
            $pattern = '/\\' . preg_quote($selector, '/') . '\s*\{[^}]+\}/';
            preg_match_all($pattern, $compiledCSS, $matches);

            if (!empty($matches[0])) {
                foreach ($matches[0] as $rule) {
                    $criticalCSS .= $rule . "\n\n";
                }
            }
        }

        return $criticalCSS;
    }

    /**
     * Optimize CSS for production
     */
    private function optimizeForProduction(): void
    {
        echo "🚀 Optimizing for production...\n";

        $compiledCSS = file_get_contents($this->distDir . '/compiled.css');

        // Minify CSS
        $minifiedCSS = $this->minifyCSS($compiledCSS);

        // Write minified version
        $minifiedPath = $this->distDir . '/style.min.css';
        file_put_contents($minifiedPath, $minifiedCSS);

        echo "  ✓ Minified CSS: " . basename($minifiedPath) . "\n";

        // Calculate compression ratio
        $originalSize = strlen($compiledCSS);
        $minifiedSize = strlen($minifiedCSS);
        $compressionRatio = (($originalSize - $minifiedSize) / $originalSize) * 100;

        echo "  📊 Compression: " . round($compressionRatio, 1) . "% smaller\n";
    }

    /**
     * Simple CSS minification
     */
    private function minifyCSS(string $css): string
    {
        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);

        // Remove unnecessary whitespace
        $css = preg_replace('/\s+/', ' ', $css);

        // Remove whitespace around specific characters
        $css = preg_replace('/\s*([{}:;,>+~])\s*/', '$1', $css);

        // Remove trailing semicolons
        $css = preg_replace('/;}/', '}', $css);

        return trim($css);
    }

    /**
     * Generate development assets with source maps
     */
    private function generateDevelopmentAssets(): void
    {
        if ($this->isProduction) {
            return;
        }

        echo "🛠️  Generating development assets...\n";

        $compiledCSS = file_get_contents($this->distDir . '/compiled.css');

        // Add source map reference
        $devCSS = $compiledCSS . "\n/*# sourceMappingURL=style.dev.css.map */";

        // Write development version
        $devPath = $this->distDir . '/style.dev.css';
        file_put_contents($devPath, $devCSS);

        // Create simple source map
        $sourceMap = [
            'version' => 3,
            'sources' => ['../../../main.css'],
            'names' => [],
            'mappings' => '',
            'file' => 'style.dev.css'
        ];

        $sourceMapPath = $this->distDir . '/style.dev.css.map';
        file_put_contents($sourceMapPath, json_encode($sourceMap, JSON_PRETTY_PRINT));

        echo "  ✓ Development CSS with source maps: " . basename($devPath) . "\n";
    }

    /**
     * Create build timestamp
     */
    private function createTimestamp(): void
    {
        $timestamp = time();
        $timestampPath = $this->distDir . '/.timestamp';
        file_put_contents($timestampPath, $timestamp);

        echo "  ⏰ Build timestamp: " . date('Y-m-d H:i:s', $timestamp) . "\n";
    }

    /**
     * Print build statistics
     */
    private function printStats(): void
    {
        echo "\n📈 Build Statistics:\n";

        $files = glob($this->distDir . '/*.css');
        $totalSize = 0;

        foreach ($files as $file) {
            if (is_file($file)) {
                $size = filesize($file);
                $totalSize += $size;
                echo "  " . basename($file) . ": " . $this->formatBytes($size) . "\n";
            }
        }

        echo "  Total CSS: " . $this->formatBytes($totalSize) . "\n";

        // Critical CSS stats
        $criticalFiles = glob($this->criticalDir . '/*.css');
        if ($criticalFiles) {
            echo "  Critical CSS files: " . count($criticalFiles) . "\n";
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        } elseif ($bytes < 1048576) {
            return round($bytes / 1024, 1) . ' KB';
        } else {
            return round($bytes / 1048576, 1) . ' MB';
        }
    }
}

// Run the build if this file is executed directly
if (basename($_SERVER['PHP_SELF']) === 'build-css.php') {
    $builder = new CSSBuilder();
    $builder->build();
}
