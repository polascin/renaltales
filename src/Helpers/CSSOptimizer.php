<?php

declare(strict_types=1);

namespace RenalTales\Helpers;

/**
 * CSS Optimizer Helper
 *
 * Advanced CSS loading optimization with:
 * - Critical CSS inlining
 * - Lazy loading of non-critical styles
 * - CSS containment
 * - Resource hints
 * - Performance monitoring
 *
 * @package RenalTales\Helpers
 * @author Ľubomír Polaščín
 */
class CSSOptimizer
{
    private static bool $isProduction = false;
    private static array $criticalCSS = [];
    private static array $loadedStyles = [];
    private static bool $initialized = false;

    /**
     * Initialize the CSS optimizer
     */
    public static function initialize(): void
    {
        if (self::$initialized) {
            return;
        }

        self::$isProduction = (bool) ($_ENV['NODE_ENV'] === 'production' || defined('PRODUCTION') && PRODUCTION);
        self::$initialized = true;
    }

    /**
     * Get critical CSS for a specific page
     */
    public static function getCriticalCSS(string $page = 'default'): string
    {
        self::initialize();

        if (isset(self::$criticalCSS[$page])) {
            return self::$criticalCSS[$page];
        }

        $criticalFile = self::getCriticalCSSPath($page);
        if (file_exists($criticalFile)) {
            self::$criticalCSS[$page] = file_get_contents($criticalFile);
            return self::$criticalCSS[$page];
        }

        return '';
    }

    /**
     * Generate CSS links with optimization
     */
    public static function generateCSSLinks(array $stylesheets, string $page = 'default'): string
    {
        self::initialize();

        $html = '';
        $timestamp = self::getTimestamp();

        // Add preload hints for critical resources
        $html .= self::generatePreloadHints($stylesheets, $timestamp);

        // Inline critical CSS
        $criticalCSS = self::getCriticalCSS($page);
        if (!empty($criticalCSS)) {
            $html .= '<style id="critical-css">' . $criticalCSS . '</style>' . PHP_EOL;
        }

        // Generate optimized CSS links
        foreach ($stylesheets as $stylesheet) {
            $html .= self::generateOptimizedLink($stylesheet, $timestamp);
        }

        // Add lazy loading script
        $html .= self::generateLazyLoadScript();

        return $html;
    }

    /**
     * Generate preload hints for critical resources
     */
    private static function generatePreloadHints(array $stylesheets, string $timestamp): string
    {
        $html = '';

        // Preload critical stylesheets
        $criticalSheets = ['reset.css', 'variables.css', 'core.css'];

        foreach ($criticalSheets as $sheet) {
            if (in_array($sheet, $stylesheets)) {
                $html .= sprintf(
                    '<link rel="preload" href="/assets/css/%s?v=%s" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">%s',
                    $sheet,
                    $timestamp,
                    PHP_EOL
                );
            }
        }

        // Preconnect to external resources
        $html .= '<link rel="preconnect" href="https://fonts.googleapis.com">' . PHP_EOL;
        $html .= '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . PHP_EOL;

        return $html;
    }

    /**
     * Generate optimized CSS link
     */
    private static function generateOptimizedLink(string $stylesheet, string $timestamp): string
    {
        $optimizedPath = self::getOptimizedPath($stylesheet);
        $isCritical = self::isCritical($stylesheet);

        // Use optimized version in production
        if (self::$isProduction && file_exists($optimizedPath)) {
            $path = str_replace(getcwd(), '', $optimizedPath);
        } else {
            $path = "/assets/css/{$stylesheet}";
        }

        $attributes = [
            'rel' => 'stylesheet',
            'href' => "{$path}?v={$timestamp}"
        ];

        // Add CSS containment for better performance
        if (!$isCritical) {
            $attributes['media'] = 'print';
            $attributes['onload'] = "this.media='all'";
        }

        // Add integrity check in production
        if (self::$isProduction) {
            $integrity = self::getIntegrity($optimizedPath);
            if ($integrity) {
                $attributes['integrity'] = $integrity;
                $attributes['crossorigin'] = 'anonymous';
            }
        }

        return self::buildLinkTag($attributes);
    }

    /**
     * Check if stylesheet is critical
     */
    private static function isCritical(string $stylesheet): bool
    {
        $criticalSheets = ['reset.css', 'variables.css', 'core.css'];
        return in_array($stylesheet, $criticalSheets);
    }

    /**
     * Get optimized file path
     */
    private static function getOptimizedPath(string $stylesheet): string
    {
        $baseName = pathinfo($stylesheet, PATHINFO_FILENAME);
        return getcwd() . "/public/assets/css/dist/{$baseName}.min.css";
    }

    /**
     * Get critical CSS file path
     */
    private static function getCriticalCSSPath(string $page): string
    {
        return getcwd() . "/public/assets/css/critical/{$page}.css";
    }

    /**
     * Generate cache-busting timestamp
     */
    private static function getTimestamp(): string
    {
        if (self::$isProduction) {
            // Use build timestamp in production
            $buildFile = getcwd() . '/public/assets/css/dist/.timestamp';
            if (file_exists($buildFile)) {
                return trim(file_get_contents($buildFile));
            }
        }

        return (string) time();
    }

    /**
     * Generate integrity hash for file
     */
    private static function getIntegrity(string $filePath): ?string
    {
        if (!file_exists($filePath)) {
            return null;
        }

        $content = file_get_contents($filePath);
        return 'sha384-' . base64_encode(hash('sha384', $content, true));
    }

    /**
     * Build HTML link tag from attributes
     */
    private static function buildLinkTag(array $attributes): string
    {
        $attrStrings = [];
        foreach ($attributes as $key => $value) {
            $attrStrings[] = sprintf('%s="%s"', $key, htmlspecialchars($value, ENT_QUOTES));
        }

        return sprintf('<link %s>%s', implode(' ', $attrStrings), PHP_EOL);
    }

    /**
     * Generate lazy loading script
     */
    private static function generateLazyLoadScript(): string
    {
        return <<<'HTML'
<script>
(function() {
    // Lazy load non-critical CSS
    function loadCSS(href, before, media, callback) {
        var ss = window.document.createElement("link");
        var ref = before || window.document.getElementsByTagName("script")[0];
        var sheets = window.document.styleSheets;
        
        ss.rel = "stylesheet";
        ss.href = href;
        ss.media = "only x";
        
        function ready(cb) {
            if (document.body) {
                return cb();
            }
            setTimeout(function() {
                ready(cb);
            });
        }
        
        ready(function() {
            ref.parentNode.insertBefore(ss, ref);
        });
        
        function onload() {
            if (ss.addEventListener) {
                ss.removeEventListener("load", onload);
            }
            ss.media = media || "all";
            callback && callback();
        }
        
        if (ss.addEventListener) {
            ss.addEventListener("load", onload);
        } else if (ss.attachEvent) {
            ss.attachEvent("onload", onload);
        }
        
        return ss;
    }
    
    // Load non-critical CSS after page load
    window.addEventListener('load', function() {
        // Load additional stylesheets
        var additionalCSS = [
            '/assets/css/animations.css',
            '/assets/css/print.css'
        ];
        
        additionalCSS.forEach(function(href) {
            loadCSS(href);
        });
    });
    
    // Fallback for browsers without JavaScript
    var noscriptCSS = document.createElement('noscript');
    noscriptCSS.innerHTML = '<link rel="stylesheet" href="/assets/css/fallback.css">';
    document.head.appendChild(noscriptCSS);
})();
</script>
HTML;
    }

    /**
     * Generate CSS containment styles
     */
    public static function generateContainmentCSS(): string
    {
        return <<<'CSS'
<style>
/* CSS Containment for better performance */
.component-isolated {
    contain: layout style paint;
}

.list-container {
    contain: layout style;
}

-grid {
    contain: layout;
}

.modal-content {
    contain: layout style paint;
}

.sidebar {
    contain: layout style;
}

.main-content {
    contain: layout;
}

/* Optimize paint operations */
.hero-section {
    will-change: transform;
}

 {
    will-change: auto;
}

/* Optimize animations */
.fade-transition {
    will-change: opacity;
}

.slide-transition {
    will-change: transform;
}

/* Reset will-change after animations */
.animation-complete {
    will-change: auto;
}
</style>
CSS;
    }

    /**
     * Generate performance monitoring script
     */
    public static function generatePerformanceMonitoring(): string
    {
        if (!self::$isProduction) {
            return '';
        }

        return <<<'HTML'
<script>
(function() {
    // Monitor CSS loading performance
    if ('performance' in window) {
        window.addEventListener('load', function() {
            setTimeout(function() {
                var resources = performance.getEntriesByType('resource');
                var cssResources = resources.filter(function(resource) {
                    return resource.name.indexOf('.css') !== -1;
                });
                
                if (cssResources.length > 0) {
                    var totalCSSTime = cssResources.reduce(function(sum, resource) {
                        return sum + resource.duration;
                    }, 0);
                    
                    // Log to analytics or monitoring service
                    console.log('CSS Load Time:', totalCSSTime + 'ms');
                }
            }, 1000);
        });
    }
})();
</script>
HTML;
    }

    /**
     * Clear CSS cache
     */
    public static function clearCache(): void
    {
        self::$criticalCSS = [];
        self::$loadedStyles = [];

        // Clear compiled CSS files
        $distDir = getcwd() . '/public/assets/css/dist';
        if (is_dir($distDir)) {
            $files = glob($distDir . '/*.css');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }

    /**
     * Get CSS loading statistics
     */
    public static function getStats(): array
    {
        $stats = [
            'production_mode' => self::$isProduction,
            'critical_css_loaded' => count(self::$criticalCSS),
            'stylesheets_loaded' => count(self::$loadedStyles),
            'cache_size' => 0
        ];

        // Calculate cache size
        foreach (self::$criticalCSS as $css) {
            $stats['cache_size'] += strlen($css);
        }

        return $stats;
    }
}
