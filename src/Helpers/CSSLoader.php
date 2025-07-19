<?php

namespace RenalTales\Helpers;

/**
 * CSS Loader Helper
 *
 * Provides methods for loading CSS files with cache busting timestamps
 * and managing consolidated CSS assets.
 */
class CSSLoader
{
    /**
     * Base assets directory path
     */
    private const ASSETS_BASE_PATH = '/assets/css/';

    /**
     * Physical path to assets directory
     */
    private const ASSETS_PHYSICAL_PATH = 'G:\Môj disk\www\renaltales\assets\css\\';

    /**
     * Get CSS file URL with timestamp for cache busting
     *
     * @param string $filename CSS filename (without path)
     * @return string Full URL with timestamp
     */
    public static function getCSSUrl(string $filename): string
    {
        $filePath = self::ASSETS_PHYSICAL_PATH . $filename;
        $timestamp = file_exists($filePath) ? filemtime($filePath) : time();

        return self::ASSETS_BASE_PATH . $filename . '?v=' . $timestamp;
    }

    /**
     * Get main CSS URL with timestamp
     *
     * @return string Main CSS URL with timestamp
     */
    public static function getMainCSSUrl(): string
    {
        return self::getCSSUrl('main.css');
    }

    /**
     * Generate CSS link tag with timestamp
     *
     * @param string $filename CSS filename
     * @param array $attributes Additional attributes for the link tag
     * @return string HTML link tag
     */
    public static function generateCSSLink(string $filename, array $attributes = []): string
    {
        $url = self::getCSSUrl($filename);
        $attrs = '';

        foreach ($attributes as $key => $value) {
            $attrs .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
        }

        return '<link rel="stylesheet" href="' . htmlspecialchars($url) . '"' . $attrs . '>';
    }

    /**
     * Generate main CSS link tag
     *
     * @param array $attributes Additional attributes for the link tag
     * @return string HTML link tag for main CSS
     */
    public static function generateMainCSSLink(array $attributes = []): string
    {
        return self::generateCSSLink('main.css', $attributes);
    }

    /**
     * Get all CSS files with timestamps
     *
     * @return array Array of CSS files with their URLs and timestamps
     */
    public static function getAllCSSFiles(): array
    {
        $files = [];
        $cssDir = self::ASSETS_PHYSICAL_PATH;

        if (is_dir($cssDir)) {
            $iterator = new \DirectoryIterator($cssDir);

            foreach ($iterator as $fileInfo) {
                if ($fileInfo->isFile() && $fileInfo->getExtension() === 'css') {
                    $filename = $fileInfo->getFilename();
                    $files[] = [
                        'filename' => $filename,
                        'url' => self::getCSSUrl($filename),
                        'timestamp' => $fileInfo->getMTime(),
                        'size' => $fileInfo->getSize()
                    ];
                }
            }
        }

        return $files;
    }

    /**
     * Check if main CSS file exists
     *
     * @return bool True if main CSS exists
     */
    public static function mainCSSExists(): bool
    {
        return file_exists(self::ASSETS_PHYSICAL_PATH . 'main.css');
    }

    /**
     * Get main CSS file modification time
     *
     * @return int|false Timestamp or false if file doesn't exist
     */
    public static function getMainCSSTimestamp()
    {
        $filePath = self::ASSETS_PHYSICAL_PATH . 'main.css';
        return file_exists($filePath) ? filemtime($filePath) : false;
    }

    /**
     * Generate critical CSS inline styles
     *
     * @param array $criticalStyles Array of critical CSS rules
     * @return string Inline style tag with critical CSS
     */
    public static function generateCriticalCSS(array $criticalStyles = []): string
    {
        $defaultCritical = [
            'body' => [
                'font-family' => 'var(--font-family-sans)',
                'line-height' => 'var(--line-height-relaxed)',
                'color' => 'var(--color-text)',
                'background-color' => 'var(--color-background)',
                'margin' => '0',
                'padding' => '0'
            ],
            '*' => [
                'box-sizing' => 'border-box'
            ]
        ];

        $styles = array_merge($defaultCritical, $criticalStyles);
        $css = '';

        foreach ($styles as $selector => $rules) {
            $css .= $selector . '{';
            foreach ($rules as $property => $value) {
                $css .= $property . ':' . $value . ';';
            }
            $css .= '}';
        }

        return '<style>' . $css . '</style>';
    }

    /**
     * Generate preload link for CSS
     *
     * @param string $filename CSS filename
     * @return string Preload link tag
     */
    public static function generateCSSPreload(string $filename): string
    {
        $url = self::getCSSUrl($filename);
        return '<link rel="preload" href="' . htmlspecialchars($url) . '" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">';
    }

    /**
     * Generate font preload links
     *
     * @return string Font preload links
     */
    public static function generateFontPreloads(): string
    {
        $fonts = [
            'https://fonts.googleapis.com/css2?family=Eagle+Lake&family=Funnel+Display&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Pacifico&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100..900;1,100..900&family=Source+Code+Pro:ital,wght@0,200..900;1,200..900&family=Winky+Sans:ital,wght@0,300..900;1,300..900&display=swap'
        ];

        $preloads = '';
        foreach ($fonts as $font) {
            $preloads .= '<link rel="preload" href="' . htmlspecialchars($font) . '" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">' . "\n";
        }

        return $preloads;
    }
}
