<?php

declare(strict_types=1);

namespace RenalTales\Core;

require_once __DIR__ . '/Contracts/LanguageInterface.php';

use Core\Contracts\LanguageInterface;
use Exception;

/**
 * Language Manager
 * 
 * Enhanced language detection and management with improved architecture
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0test
 */
class LanguageManager implements LanguageInterface {
    
    /**
     * Configuration constants
     */
    private const DEFAULT_LANGUAGE = 'en';
    private const COOKIE_LIFETIME = 2592000; // 30 days
    private const MAX_HEADER_LENGTH = 1000;
    private const CACHE_LIFETIME = 3600; // 1 hour
    
    /**
     * RTL languages
     */
    private const RTL_LANGUAGES = ['ar', 'fa', 'he', 'ur', 'ps', 'sd', 'ug', 'dv'];
    
    /**
     * Language priority order (optimized for application)
     */
    private array $languagePriority = [
        // Core priority languages
        'en', 'en-us', 'en-gb', 'en-ca', 'en-au', 'en-nz', 'en-za',
        'sk', 'cs', 'de', 'pl', 'hu', 'uk', 'ru', 'fr', 'es', 'it', 'pt', 'nl',
        'da', 'no', 'sv', 'fi', 'lt', 'lv', 'et', 'sl', 'hr', 'bg', 'ro',
        
        // Major world languages
        'zh', 'hi', 'ar', 'bn', 'ja', 'ko', 'vi', 'th', 'tr', 'fa', 'he',
        
        // Other European languages
        'el', 'sr', 'mk', 'sq', 'be', 'is', 'mt', 'ga', 'cy', 'eu', 'ca', 'gl',
        'lb', 'rm', 'fo', 'kl', 'se', 'gd',
        
        // Additional languages by speaker count
        'id', 'ms', 'tl', 'ta', 'te', 'mr', 'gu', 'kn', 'ml', 'pa', 'ne',
        'si', 'my', 'km', 'lo', 'ka', 'hy', 'az', 'kk', 'ky', 'uz', 'tg',
        'mn', 'sw', 'am', 'ha', 'yo', 'ig', 'zu', 'af', 'xh'
    ];
    
    /**
     * Language to flag mapping
     */
    private array $languageToFlag = [
        'en' => 'gb', 'en-us' => 'us', 'en-gb' => 'gb', 'en-ca' => 'ca',
        'en-au' => 'au', 'en-nz' => 'nz', 'en-za' => 'za',
        'sk' => 'sk', 'cs' => 'cz', 'de' => 'de', 'pl' => 'pl',
        'hu' => 'hu', 'uk' => 'ua', 'ru' => 'ru', 'fr' => 'fr',
        'es' => 'es', 'it' => 'it', 'pt' => 'pt', 'nl' => 'nl',
        'zh' => 'cn', 'ja' => 'jp', 'ko' => 'kr', 'ar' => 'sa',
        'hi' => 'in', 'th' => 'th', 'vi' => 'vn', 'fa' => 'ir',
        'he' => 'il', 'tr' => 'tr'
    ];
    
    /**
     * Language names
     */
    private array $languageNames = [
        'en' => 'English',
        'en-us' => 'English (US)',
        'en-gb' => 'English (UK)',
        'en-ca' => 'English (Canada)',
        'en-au' => 'English (Australia)',
        'en-nz' => 'English (New Zealand)',
        'en-za' => 'English (South Africa)',
        'sk' => 'Slovenčina',
        'cs' => 'Čeština',
        'de' => 'Deutsch',
        'pl' => 'Polski',
        'hu' => 'Magyar',
        'uk' => 'Українська',
        'ru' => 'Русский',
        'fr' => 'Français',
        'es' => 'Español',
        'it' => 'Italiano',
        'pt' => 'Português',
        'nl' => 'Nederlands',
        'zh' => '中文',
        'ja' => '日本語',
        'ko' => '한국어',
        'ar' => 'العربية',
        'hi' => 'हिन्दी',
        'th' => 'ไทย',
        'vi' => 'Tiếng Việt',
        'fa' => 'فارسی',
        'he' => 'עברית',
        'tr' => 'Türkçe'
    ];
    
    /**
     * Country to language mapping
     */
    private array $countryToLanguage = [
        'US' => 'en-us', 'GB' => 'en-gb', 'CA' => 'en-ca', 'AU' => 'en-au',
        'NZ' => 'en-nz', 'ZA' => 'en-za', 'SK' => 'sk', 'CZ' => 'cs',
        'DE' => 'de', 'AT' => 'de', 'CH' => 'de', 'PL' => 'pl',
        'HU' => 'hu', 'UA' => 'uk', 'RU' => 'ru', 'FR' => 'fr',
        'ES' => 'es', 'IT' => 'it', 'PT' => 'pt', 'NL' => 'nl',
        'CN' => 'zh', 'JP' => 'ja', 'KR' => 'ko', 'SA' => 'ar',
        'IN' => 'hi', 'TH' => 'th', 'VN' => 'vi', 'IR' => 'fa',
        'IL' => 'he', 'TR' => 'tr'
    ];
    
    /**
     * Supported languages (loaded from files)
     */
    private array $supportedLanguages = [];
    
    /**
     * Cache for various operations
     */
    private array $cache = [];
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->loadSupportedLanguages();
    }
    
    /**
     * Detect user's preferred language
     */
    public function detectLanguage(): string {
if ($this->isDebugMode()) {
            error_log('LanguageManager: Starting language detection');
        }
        try {
            // 1. Check URL parameter (highest priority)
            if (isset($_GET['lang'])) {
                $urlLang = $this->sanitizeLanguage($_GET['lang']);
                if ($urlLang && $this->isSupported($urlLang)) {
                    $this->setLanguage($urlLang);
                    return $urlLang;
                }
            }
            
            // 2. Check session
            $sessionLang = $this->getSessionLanguage();
            if ($sessionLang && $this->isSupported($sessionLang)) {
                return $sessionLang;
            }
            
            // 3. Check cookie
            $cookieLang = $this->getCookieLanguage();
            if ($cookieLang && $this->isSupported($cookieLang)) {
if ($this->isDebugMode()) {
                error_log('LanguageManager: Cookie language ' . $cookieLang . ' found and set in session');
            }
            $this->setSessionLanguage($cookieLang);
                return $cookieLang;
            }
            
            // 4. Check browser headers
            $browserLang = $this->detectFromBrowser();
            if ($browserLang) {
                $this->setLanguage($browserLang);
                return $browserLang;
            }
            
            // 5. Check geolocation
            $geoLang = $this->detectFromGeolocation();
            if ($geoLang && $this->isSupported($geoLang)) {
                $this->setLanguage($geoLang);
                return $geoLang;
            }
            
            // 6. Default fallback
            $this->setLanguage(self::DEFAULT_LANGUAGE);
            return self::DEFAULT_LANGUAGE;
            
        } catch(Exception $e) 
if ($this->isDebugMode()) {
                error_log('LanguageManager: Detection failed: ' . $e->getMessage());
            
            return self::DEFAULT_LANGUAGE;
        }
    }
    
    /**
     * Get current active language
     */
    public function getCurrentLanguage(): string {
        try {
            // Check session first
            $sessionLang = $this->getSessionLanguage();
            if ($sessionLang && $this->isSupported($sessionLang)) {
                return $sessionLang;
            }
            
            // Check cookie
            $cookieLang = $this->getCookieLanguage();
            if ($cookieLang && $this->isSupported($cookieLang)) {
                return $cookieLang;
            }
            
            // Detect new
            return $this->detectLanguage();
            
        } catch(Exception $e) 
            error_log('LanguageManager: Failed to get current language: ' . $e->getMessage());
            return self::DEFAULT_LANGUAGE;
        
    }
    
    /**
     * Set language preference
     */
    public function setLanguage(string $language): void {
        $language = $this->sanitizeLanguage($language);
        if (!$language || !$this->isSupported($language)) {
            return;
        }
        
        try {
            // Set in session
            $this->setSessionLanguage($language);
            
            // Set secure cookie
            if (!headers_sent()) {
                $cookieOptions = [
                    'expires' => time() + self::COOKIE_LIFETIME,
                    'path' => '/',
                    'domain' => '',
                    'secure' => $this->isHttps(),
                    'httponly' => true,
                    'samesite' => 'Strict'
                ];
                
                setcookie('language', $language, $cookieOptions);
                if ($this->isDebugMode()) {
                    error_log('LanguageManager: Cookie language set: ' . $language);
                }
            }
            
        } catch(Exception $e) 
            error_log('LanguageManager: Failed to set language: ' . $e->getMessage());
        
    }
    
    /**
     * Set current language (alias for setLanguage)
     */
    public function setCurrentLanguage(string $language): void {
        $this->setLanguage($language);
    }
    
    /**
     * Check if language is supported
     */
    public function isSupported(string $language): bool {
        $language = $this->sanitizeLanguage($language);
        return $language && in_array($language, $this->supportedLanguages, true);
    }
    
    /**
     * Get list of supported languages
     */
    public function getSupportedLanguages(): array {
        return $this->supportedLanguages;
    }
    
    /**
     * Get display name for language
     */
    public function getLanguageName(string $language): string {
        return $this->languageNames[$language] ?? $language;
    }
    
    /**
     * Get native name for language
     */
    public function getNativeName(string $language): string {
        // For now, same as display name - can be extended later
        return $this->getLanguageName($language);
    }
    
    /**
     * Check if language uses RTL writing
     */
    public function isRTL(string $language): bool {
        return in_array($language, self::RTL_LANGUAGES, true);
    }
    
    /**
     * Get text direction for language
     */
    public function getDirection(string $language): string {
        return $this->isRTL($language) ? 'rtl' : 'ltr';
    }
    
    /**
     * Get flag code for language
     */
    public function getFlagCode(string $language): string {
        return $this->languageToFlag[$language] ?? strtolower(substr($language, 0, 2));
    }
    
    /**
     * Get flag path for language
     */
    public function getFlagPath(string $language, string $basePath = 'assets/flags/', string $extension = '.webp'): string {
        $flagCode = $this->getFlagCode($language);
        return $basePath . $flagCode . $extension;
    }
    
    /**
     * Get best available flag path with fallback support
     */
    public function getBestFlagPath(string $language, string $basePath = 'assets/flags/', ?string $documentRoot = null): string {
        $cacheKey = "flag_path_{$language}_{$basePath}";
        
        // Check cache first
        if (isset($this->cache[$cacheKey]) && 
            time() - $this->cache[$cacheKey]['time'] < self::CACHE_LIFETIME) {
            if ($this->isDebugMode()) {
                error_log('LanguageManager: Flag path cache HIT for ' . $language);
            }
            return $this->cache[$cacheKey]['data'];
        }
        
        if ($this->isDebugMode()) {
            error_log('LanguageManager: Flag path cache MISS for ' . $language);
        }
        
        $flagCode = $this->getFlagCode($language);
        $extensions = ['.webp', '.png', '.jpg', '.gif'];
        $actualDocumentRoot = $documentRoot ?? $_SERVER['DOCUMENT_ROOT'] ?? getcwd();
        
        // Add public prefix if needed
        $fullBasePath = $actualDocumentRoot;
        if (!str_ends_with($fullBasePath, '/')) {
            $fullBasePath .= '/';
        }
        if (!str_starts_with($basePath, 'public/')) {
            $fullBasePath .= 'public/';
        }
        $fullBasePath .= $basePath;
        
        // Try each extension in order of preference
        foreach ($extensions as $ext) {
            $filePath = $fullBasePath . $flagCode . $ext;
            $webPath = $basePath . $flagCode . $ext;
            
            if (file_exists($filePath)) {
                // Cache successful result
                $this->cache[$cacheKey] = [
                    'data' => $webPath,
                    'time' => time()
                ];
                
                $this->logFlagAccess($language, $flagCode, $webPath, true);
                return $webPath;
            }
        }
        
        // Fallback to UN flag
        $fallbackCode = 'un';
        foreach ($extensions as $ext) {
            $filePath = $fullBasePath . $fallbackCode . $ext;
            $webPath = $basePath . $fallbackCode . $ext;
            
            if (file_exists($filePath)) {
                $this->logFlagAccess($language, $flagCode, $webPath, false, 'UN fallback');
                
                // Cache fallback result
                $this->cache[$cacheKey] = [
                    'data' => $webPath,
                    'time' => time()
                ];
                
                return $webPath;
            }
        }
        
        // Final fallback - return expected path even if file doesn't exist
        $fallbackPath = $basePath . $flagCode . '.webp';
        $this->logFlagAccess($language, $flagCode, $fallbackPath, false, 'File not found');
        
        return $fallbackPath;
    }
    
    /**
     * Log flag access for debugging and monitoring
     */
    private function logFlagAccess(string $language, string $flagCode, string $path, bool $found, string $note = ''): void {
        // Only log if we're in debug mode or on localhost
        if ($this->isDebugMode()) {
            $status = $found ? 'FOUND' : 'MISSING';
            $message = "Flag {$status}: {$language} -> {$flagCode} -> {$path}";
            if ($note) {
                $message .= " ({$note})";
            }
            error_log("LanguageManager: {$message}");
        }
    }
    
    /**
     * Check if we're in debug mode
     */
    private function isDebugMode(): bool {
        return (
            isset($_SERVER['SERVER_ADDR']) && 
            ($_SERVER['SERVER_ADDR'] === '127.0.0.1' || $_SERVER['SERVER_ADDR'] === '::1')
        ) || (
            isset($_SERVER['HTTP_HOST']) && 
            (str_contains($_SERVER['HTTP_HOST'], 'localhost') || str_contains($_SERVER['HTTP_HOST'], '127.0.0.1'))
        );
    }
    
    /**
     * Load supported languages from files
     */
    private function loadSupportedLanguages(): void {
        $cacheKey = 'supported_languages';
        
        // Check cache first
        if (isset($this->cache[$cacheKey]) && 
            time() - $this->cache[$cacheKey]['time'] < self::CACHE_LIFETIME) {
            $this->supportedLanguages = $this->cache[$cacheKey]['data'];
            return;
        }
        
        try {
            if (!defined('LANGUAGE_PATH')) {
                $this->supportedLanguages = ['en'];
                return;
            }
            
            $languageFiles = glob(LANGUAGE_PATH . '*.php');
            if ($languageFiles === false) {
                $this->supportedLanguages = ['en'];
                return;
            }
            
            $languages = [];
            foreach ($languageFiles as $file) {
                $langCode = basename($file, '.php');
                if ($this->isValidLanguageFormat($langCode)) {
                    $languages[] = $langCode;
                }
            }
            
            // Sort by priority
            $this->supportedLanguages = $this->sortLanguagesByPriority($languages);
            
            // Cache the result
            $this->cache[$cacheKey] = [
                'data' => $this->supportedLanguages,
                'time' => time()
            ];
            
        } catch(Exception $e) 
            error_log('LanguageManager: Failed to load supported languages: ' . $e->getMessage());
            $this->supportedLanguages = ['en'];
        
    }
    
    /**
     * Sort languages by priority
     */
    private function sortLanguagesByPriority(array $languages): array {
        $prioritized = [];
        $remaining = [];
        
        // First, add languages in priority order
        foreach ($this->languagePriority as $lang) {
            if (in_array($lang, $languages, true)) {
                $prioritized[] = $lang;
            }
        }
        
        // Add remaining languages alphabetically
        foreach ($languages as $lang) {
            if (!in_array($lang, $prioritized, true)) {
                $remaining[] = $lang;
            }
        }
        
        sort($remaining);
        return array_merge($prioritized, $remaining);
    }
    
    /**
     * Validate language code format
     */
    private function isValidLanguageFormat(string $lang): bool {
        return preg_match('/^[a-z]{2,3}(-[a-z]{2,4})?$/i', $lang) === 1;
    }
    
    /**
     * Sanitize language input
     */
    private function sanitizeLanguage(?string $lang): ?string {
        if (!is_string($lang)) {
            return null;
        }
        
        $lang = trim(strtolower($lang));
        return $this->isValidLanguageFormat($lang) ? $lang : null;
    }
    
    /**
     * Get language from session
     */
    private function getSessionLanguage(): ?string {
        if (!$this->isSessionAvailable()) {
            if ($this->isDebugMode()) {
                error_log('LanguageManager: Session not available for language read');
            }
            return null;
        }
        
        $sessionLang = $this->sanitizeLanguage($_SESSION['language'] ?? null);
        if ($this->isDebugMode()) {
            error_log('LanguageManager: Session language read: ' . ($sessionLang ?: 'null'));
        }
        return $sessionLang;
    }
    
    /**
     * Get language from cookie
     */
    private function getCookieLanguage(): ?string {
        $cookieLang = $this->sanitizeLanguage($_COOKIE['language'] ?? null);
        if ($this->isDebugMode()) {
            error_log('LanguageManager: Cookie language read: ' . ($cookieLang ?: 'null'));
        }
        return $cookieLang;
    }
    
    /**
     * Set language in session
     */
    private function setSessionLanguage(string $lang): bool {
        if (!$this->isSessionAvailable()) {
            return false;
        }
        
        try {
            $_SESSION['language'] = $lang;
            if ($this->isDebugMode()) {
                error_log('LanguageManager: Session language set: ' . $lang);
            }
            return true;
        } catch(Exception $e) 
            error_log('LanguageManager: Failed to set session language: ' . $e->getMessage());
            return false;
        
    }
    
    /**
     * Check if session is available
     */
    private function isSessionAvailable(): bool {
        return session_status() === PHP_SESSION_ACTIVE;
    }
    
    /**
     * Check if HTTPS is being used
     */
    private function isHttps(): bool {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
               (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ||
               (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
               (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on');
    }
    
    /**
     * Detect language from browser headers
     */
    private function detectFromBrowser(): ?string {
        $acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        
        if (empty($acceptLanguage) || strlen($acceptLanguage) > self::MAX_HEADER_LENGTH) {
            return null;
        }
        
        try {
            if (!preg_match_all('/([a-z]{1,8}(?:-[a-z]{1,8})*)(?:;q=([0-9.]+))?/i', $acceptLanguage, $matches)) {
                return null;
            }
            
            $languages = [];
            for ($i = 0; $i < count($matches[1]); $i++) {
                $lang = strtolower($matches[1][$i]);
                $quality = isset($matches[2][$i]) && !empty($matches[2][$i]) ? (float)$matches[2][$i] : 1.0;
                
                if ($quality >= 0 && $quality <= 1) {
                    $languages[$lang] = $quality;
                }
            }
            
            arsort($languages);
            
            foreach ($languages as $lang => $quality) {
                // Check exact match
                if ($this->isSupported($lang)) {
                    return $lang;
                }
                
                // Check base language
                $baseLang = explode('-', $lang)[0];
                if ($this->isSupported($baseLang)) {
                    return $baseLang;
                }
            }
            
        } catch(Exception $e) 
            error_log('LanguageManager: Browser detection failed: ' . $e->getMessage());
        
        
        return null;
    }
    
    /**
     * Detect language from geolocation
     */
    private function detectFromGeolocation(): ?string {
        try {
            // Check CloudFlare country header
            if (isset($_SERVER['HTTP_CF_IPCOUNTRY'])) {
                $country = strtoupper(trim($_SERVER['HTTP_CF_IPCOUNTRY']));
                if (preg_match('/^[A-Z]{2}$/', $country)) {
                    return $this->countryToLanguage[$country] ?? null;
                }
            }
            
            // Check other country headers
            $headers = ['HTTP_X_COUNTRY_CODE', 'HTTP_X_GEOIP_COUNTRY'];
            foreach ($headers as $header) {
                if (isset($_SERVER[$header])) {
                    $country = strtoupper(trim($_SERVER[$header]));
                    if (preg_match('/^[A-Z]{2}$/', $country)) {
                        return $this->countryToLanguage[$country] ?? null;
                    }
                }
            }
            
        } catch(Exception $e) 
            error_log('LanguageManager: Geolocation detection failed: ' . $e->getMessage());
        
        
        return null;
    }
    
    /**
     * Get available languages with information
     */
    public function getAvailableLanguages(): array {
        $languageFiles = glob($this->getLanguageDirectory() . '*.php');
        $languages = [];
        
        foreach ($languageFiles as $file) {
            $language = basename($file, '.php');
            $languages[$language] = $this->getLanguageInfo($language);
        }
        
        return $languages;
    }
    
    /**
     * Get language information
     */
    public function getLanguageInfo(string $language): array {
        return [
            'code' => $language,
            'name' => $this->getLanguageName($language),
            'native_name' => $this->getNativeName($language),
            'direction' => $this->getDirection($language),
            'is_rtl' => $this->isRTL($language),
            'flag' => $this->getLanguageFlag($language),
            'flag_code' => $this->getFlagCode($language)
        ];
    }
    
    /**
     * Get language flag URL
     */
    public function getLanguageFlag(string $language): string {
        return $this->getBestFlagPath($language);
    }
    
    /**
     * Get language native name
     */
    public function getLanguageNativeName(string $language): string {
        return $this->getNativeName($language);
    }
    
    /**
     * Save language to session
     */
    public function saveLanguageToSession(string $language): bool {
        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['language'] = $language;
            return true;
        } catch(Exception $e) 
            error_log('LanguageManager: Failed to save language to session: ' . $e->getMessage());
            return false;
        
    }
    
    /**
     * Save language to cookie
     */
    public function saveLanguageToCookie(string $language): bool {
        try {
            $cookieName = 'language';
            $lifetime = self::COOKIE_LIFETIME;
            
            return setcookie(
                $cookieName,
                $language,
                time() + $lifetime,
                '/',
                '',
                $this->isSecureConnection(),
                true // HttpOnly
            );
        } catch(Exception $e) 
            error_log('LanguageManager: Failed to save language to cookie: ' . $e->getMessage());
            return false;
        
    }
    
    /**
     * Check if language is supported (alias for isSupported)
     */
    public function isLanguageSupported(string $language): bool {
        return $this->isSupported($language);
    }
    
    /**
     * Get system statistics
     */
    public function getSystemStats(): array {
        return [
            'current_language' => $this->getCurrentLanguage(),
            'supported_languages' => count($this->getSupportedLanguages()),
            'language_priority_count' => count($this->languagePriority),
            'rtl_languages' => self::RTL_LANGUAGES,
            'cache_enabled' => isset($this->cache),
            'memory_usage' => memory_get_usage(true)
        ];
    }
    
    /**
     * Clear cache
     */
    public function clearCache(): void {
        $this->cache = [];
    }
    
    /**
     * Get language directory path
     */
    private function getLanguageDirectory(): string {
        return defined('LANGUAGE_PATH') ? LANGUAGE_PATH : 'resources/lang/';
    }
    
    /**
     * Check if connection is secure
     */
    private function isSecureConnection(): bool {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
               $_SERVER['SERVER_PORT'] == 443 ||
               (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    }
    
    /**
     * Get debug information for language system
     * Only available when DEBUG_MODE is enabled
     */
    public function getDebugInfo(): array {
        if (!$this->isDebugMode()) {
            return ['debug_mode' => false, 'message' => 'Debug mode not enabled'];
        }
        
        return [
            'debug_mode' => true,
            'timestamp' => date('Y-m-d H:i:s'),
            'current_language' => $this->getCurrentLanguage(),
            'session_language' => $this->getSessionLanguage(),
            'cookie_language' => $this->getCookieLanguage(),
            'url_language' => $this->sanitizeLanguage($_GET['lang'] ?? null),
            'browser_languages' => $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'Not available',
            'supported_languages' => $this->getSupportedLanguages(),
            'supported_count' => count($this->getSupportedLanguages()),
            'cache_entries' => count($this->cache),
            'cache_data' => $this->cache,
            'session_status' => [
                'active' => $this->isSessionAvailable(),
                'status_code' => session_status(),
                'status_name' => $this->getSessionStatusName()
            ],
            'server_info' => [
                'https' => $this->isHttps(),
                'host' => $_SERVER['HTTP_HOST'] ?? 'Unknown',
                'server_addr' => $_SERVER['SERVER_ADDR'] ?? 'Unknown',
                'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 100)
            ],
            'geolocation_headers' => [
                'cf_country' => $_SERVER['HTTP_CF_IPCOUNTRY'] ?? 'Not available',
                'x_country_code' => $_SERVER['HTTP_X_COUNTRY_CODE'] ?? 'Not available',
                'x_geoip_country' => $_SERVER['HTTP_X_GEOIP_COUNTRY'] ?? 'Not available'
            ],
            'memory_usage' => [
                'current' => memory_get_usage(true),
                'peak' => memory_get_peak_usage(true),
                'formatted_current' => $this->formatBytes(memory_get_usage(true)),
                'formatted_peak' => $this->formatBytes(memory_get_peak_usage(true))
            ]
        ];
    }
    
    /**
     * Get session status name for debugging
     */
    private function getSessionStatusName(): string {
        switch (session_status()) {
            case PHP_SESSION_DISABLED:
                return 'PHP_SESSION_DISABLED';
            case PHP_SESSION_NONE:
                return 'PHP_SESSION_NONE';
            case PHP_SESSION_ACTIVE:
                return 'PHP_SESSION_ACTIVE';
            default:
                return 'Unknown';
        }
    }
    
    /**
     * Format bytes for human-readable output
     */
    private function formatBytes(int $size, int $precision = 2): string {
        if ($size === 0) {
            return '0 B';
        }
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $base = log($size, 1024);
        
        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $units[floor($base)];
    }
    
    /**
     * Generate debug view HTML (only in debug mode)
     */
    public function renderDebugView(): string {
        if (!$this->isDebugMode()) {
            return '';
        }
        
        $debugInfo = $this->getDebugInfo();
        
        $html = '
        <div id="language-debug-info" style="
            position: fixed;
            bottom: 10px;
            right: 10px;
            background: rgba(0,0,0,0.9);
            color: #fff;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 12px;
            max-width: 400px;
            max-height: 500px;
            overflow-y: auto;
            z-index: 9999;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        ">
            <h4 style="margin: 0 0 10px 0; color: #4CAF50;">Language Debug Info</h4>
            <div style="margin-bottom: 8px;"><strong>Current:</strong> ' . htmlspecialchars($debugInfo['current_language']) . '</div>
            <div style="margin-bottom: 8px;"><strong>Session:</strong> ' . htmlspecialchars($debugInfo['session_language'] ?: 'null') . '</div>
            <div style="margin-bottom: 8px;"><strong>Cookie:</strong> ' . htmlspecialchars($debugInfo['cookie_language'] ?: 'null') . '</div>
            <div style="margin-bottom: 8px;"><strong>URL:</strong> ' . htmlspecialchars($debugInfo['url_language'] ?: 'null') . '</div>
            <div style="margin-bottom: 8px;"><strong>Supported:</strong> ' . $debugInfo['supported_count'] . ' languages</div>
            <div style="margin-bottom: 8px;"><strong>Cache:</strong> ' . $debugInfo['cache_entries'] . ' entries</div>
            <div style="margin-bottom: 8px;"><strong>Session:</strong> ' . $debugInfo['session_status']['status_name'] . '</div>
            <div style="margin-bottom: 8px;"><strong>Memory:</strong> ' . $debugInfo['memory_usage']['formatted_current'] . '</div>
            <div style="margin-bottom: 8px;"><strong>HTTPS:</strong> ' . ($debugInfo['server_info']['https'] ? 'Yes' : 'No') . '</div>
            <details style="margin-top: 10px;">
                <summary style="cursor: pointer; color: #81C784;">Browser Languages</summary>
                <div style="margin-top: 5px; font-size: 10px; word-break: break-all;">' . htmlspecialchars(substr($debugInfo['browser_languages'], 0, 200)) . '</div>
            </details>
            <details style="margin-top: 5px;">
                <summary style="cursor: pointer; color: #81C784;">Cache Data</summary>
                <pre style="margin-top: 5px; font-size: 10px; max-height: 100px; overflow-y: auto;">' . htmlspecialchars(json_encode($debugInfo['cache_data'], JSON_PRETTY_PRINT)) . '</pre>
            </details>
        </div>';
        
        return $html;
    }
}
