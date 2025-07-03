<?php
/**
 * Language Detector Class
 * Detects user's preferred language from multiple sources with fallback to English
 * Supports European, Asian, and African languages
 * 
 * @author Generated for language detection
 * @version 1.0
 */
class LanguageDetector {
    
    /**
     * Supported languages with their variants
     */
    private $supportedLanguages = [
        // European languages
        'en' => ['en', 'en-us', 'en-gb', 'en-ca', 'en-au', 'en-nz', 'en-za'],
        'sk' => ['sk', 'sk-sk'],
        'cs' => ['cs', 'cs-cz'],
        'de' => ['de', 'de-de', 'de-at', 'de-ch'],
        'pl' => ['pl', 'pl-pl'],
        'hu' => ['hu', 'hu-hu'],
        'uk' => ['uk', 'uk-ua'],
        'ru' => ['ru', 'ru-ru', 'ru-by', 'ru-kz'],
        'it' => ['it', 'it-it', 'it-ch'],
        'nl' => ['nl', 'nl-nl', 'nl-be'],
        'fr' => ['fr', 'fr-fr', 'fr-ca', 'fr-be', 'fr-ch'],
        'es' => ['es', 'es-es', 'es-mx', 'es-ar', 'es-co', 'es-pe', 'es-ve'],
        'pt' => ['pt', 'pt-br', 'pt-pt'],
        'ro' => ['ro', 'ro-ro', 'ro-md'],
        'bg' => ['bg', 'bg-bg'],
        'sl' => ['sl', 'sl-si'],
        'hr' => ['hr', 'hr-hr'],
        'sr' => ['sr', 'sr-rs', 'sr-ba', 'sr-me'],
        'mk' => ['mk', 'mk-mk'],
        'sq' => ['sq', 'sq-al', 'sq-xk'],
        'el' => ['el', 'el-gr'],
        'da' => ['da', 'da-dk'],
        'no' => ['no', 'nb', 'nn', 'nb-no', 'nn-no'],
        'sv' => ['sv', 'sv-se'],
        'fi' => ['fi', 'fi-fi'],
        'is' => ['is', 'is-is'],
        'et' => ['et', 'et-ee'],
        'lv' => ['lv', 'lv-lv'],
        'lt' => ['lt', 'lt-lt'],
        'tr' => ['tr', 'tr-tr'],
        'eo' => ['eo'], // Esperanto
        
        // Asian languages
        'ja' => ['ja', 'ja-jp'], // Japanese
        'zh' => ['zh', 'zh-cn', 'zh-tw', 'zh-hk', 'zh-mo', 'zh-sg'], // Chinese
        'ko' => ['ko', 'ko-kr'], // Korean
        'th' => ['th', 'th-th'], // Thai
        'vi' => ['vi', 'vi-vn'], // Vietnamese
        'hi' => ['hi', 'hi-in'], // Hindi
        'ar' => ['ar', 'ar-sa', 'ar-eg', 'ar-ae', 'ar-ma', 'ar-dz'], // Arabic
        'fa' => ['fa', 'fa-ir'], // Persian/Farsi
        'he' => ['he', 'he-il'], // Hebrew
        'ur' => ['ur', 'ur-pk'], // Urdu
        'bn' => ['bn', 'bn-bd', 'bn-in'], // Bengali
        'ta' => ['ta', 'ta-in', 'ta-lk'], // Tamil
        'te' => ['te', 'te-in'], // Telugu
        'ml' => ['ml', 'ml-in'], // Malayalam
        'kn' => ['kn', 'kn-in'], // Kannada
        'gu' => ['gu', 'gu-in'], // Gujarati
        'pa' => ['pa', 'pa-in'], // Punjabi
        'ne' => ['ne', 'ne-np'], // Nepali
        'si' => ['si', 'si-lk'], // Sinhala
        'my' => ['my', 'my-mm'], // Myanmar/Burmese
        'km' => ['km', 'km-kh'], // Khmer
        'lo' => ['lo', 'lo-la'], // Lao
        'ka' => ['ka', 'ka-ge'], // Georgian
        'hy' => ['hy', 'hy-am'], // Armenian
        'az' => ['az', 'az-az'], // Azerbaijani
        'kk' => ['kk', 'kk-kz'], // Kazakh
        'ky' => ['ky', 'ky-kg'], // Kyrgyz
        'uz' => ['uz', 'uz-uz'], // Uzbek
        'tg' => ['tg', 'tg-tj'], // Tajik
        'mn' => ['mn', 'mn-mn'], // Mongolian
        
        // African languages
        'sw' => ['sw', 'sw-ke', 'sw-tz'], // Swahili
        'am' => ['am', 'am-et'], // Amharic
        'zu' => ['zu', 'zu-za'], // Zulu
        'af' => ['af', 'af-za'], // Afrikaans
        'xh' => ['xh', 'xh-za'], // Xhosa
        'st' => ['st', 'st-za'], // Sesotho
        'tn' => ['tn', 'tn-za'], // Setswana
        'ss' => ['ss', 'ss-za'], // Siswati
        've' => ['ve', 've-za'], // Venda
        'ts' => ['ts', 'ts-za'], // Tsonga
        'nr' => ['nr', 'nr-za'], // Ndebele
        'nso' => ['nso', 'nso-za'], // Northern Sotho
        'ha' => ['ha', 'ha-ng'], // Hausa
        'yo' => ['yo', 'yo-ng'], // Yoruba
        'ig' => ['ig', 'ig-ng'], // Igbo
        'rw' => ['rw', 'rw-rw'], // Kinyarwanda
        'rn' => ['rn', 'rn-bi'], // Kirundi
        'lg' => ['lg', 'lg-ug'], // Luganda
        'so' => ['so', 'so-so'], // Somali
        'ti' => ['ti', 'ti-er'], // Tigrinya
        'om' => ['om', 'om-et'], // Oromo
        'mg' => ['mg', 'mg-mg'], // Malagasy
    ];
    
    /**
     * Country to language mapping for geolocation-based detection
     */
    private $countryToLanguage = [
        // Europe
        'SK' => 'sk', 'CZ' => 'cs', 'DE' => 'de', 'AT' => 'de', 'CH' => 'de',
        'PL' => 'pl', 'HU' => 'hu', 'UA' => 'uk', 'RU' => 'ru', 'BY' => 'ru',
        'IT' => 'it', 'NL' => 'nl', 'BE' => 'nl', 'FR' => 'fr', 'ES' => 'es',
        'PT' => 'pt', 'BR' => 'pt', 'RO' => 'ro', 'MD' => 'ro', 'BG' => 'bg',
        'SI' => 'sl', 'HR' => 'hr', 'RS' => 'sr', 'BA' => 'sr', 'ME' => 'sr',
        'MK' => 'mk', 'AL' => 'sq', 'XK' => 'sq', 'GR' => 'el', 'DK' => 'da',
        'NO' => 'no', 'SE' => 'sv', 'FI' => 'fi', 'IS' => 'is', 'EE' => 'et',
        'LV' => 'lv', 'LT' => 'lt', 'TR' => 'tr',
        
        // Asia
        'JP' => 'ja', 'CN' => 'zh', 'TW' => 'zh', 'HK' => 'zh', 'MO' => 'zh',
        'SG' => 'zh', 'KR' => 'ko', 'TH' => 'th', 'VN' => 'vi', 'IN' => 'hi',
        'SA' => 'ar', 'AE' => 'ar', 'EG' => 'ar', 'MA' => 'ar', 'DZ' => 'ar',
        'IR' => 'fa', 'IL' => 'he', 'PK' => 'ur', 'BD' => 'bn', 'LK' => 'si',
        'MM' => 'my', 'KH' => 'km', 'LA' => 'lo', 'GE' => 'ka', 'AM' => 'hy',
        'AZ' => 'az', 'KZ' => 'kk', 'KG' => 'ky', 'UZ' => 'uz', 'TJ' => 'tg',
        'MN' => 'mn', 'NP' => 'ne',
        
        // Africa
        'KE' => 'sw', 'TZ' => 'sw', 'ET' => 'am', 'ZA' => 'af', 'NG' => 'ha',
        'RW' => 'rw', 'BI' => 'rn', 'UG' => 'lg', 'SO' => 'so', 'ER' => 'ti',
        'MG' => 'mg',
        
        // Default English-speaking countries
        'US' => 'en', 'GB' => 'en', 'CA' => 'en', 'AU' => 'en', 'NZ' => 'en',
    ];
    
    /**
     * Language names for display purposes
     */
    private $languageNames = [
        'en' => 'English',
        'sk' => 'Slovenčina',
        'cs' => 'Čeština',
        'de' => 'Deutsch',
        'pl' => 'Polski',
        'hu' => 'Magyar',
        'uk' => 'Українська',
        'ru' => 'Русский',
        'it' => 'Italiano',
        'nl' => 'Nederlands',
        'fr' => 'Français',
        'es' => 'Español',
        'pt' => 'Português',
        'ro' => 'Română',
        'bg' => 'Български',
        'sl' => 'Slovenščina',
        'hr' => 'Hrvatski',
        'sr' => 'Српски',
        'mk' => 'Македонски',
        'sq' => 'Shqip',
        'el' => 'Ελληνικά',
        'da' => 'Dansk',
        'no' => 'Norsk',
        'sv' => 'Svenska',
        'fi' => 'Suomi',
        'is' => 'Íslenska',
        'et' => 'Eesti',
        'lv' => 'Latviešu',
        'lt' => 'Lietuvių',
        'tr' => 'Türkçe',
        'eo' => 'Esperanto',
        'ja' => '日本語',
        'zh' => '中文',
        'ko' => '한국어',
        'th' => 'ไทย',
        'vi' => 'Tiếng Việt',
        'hi' => 'हिन्दी',
        'ar' => 'العربية',
        'fa' => 'فارسی',
        'he' => 'עברית',
        'ur' => 'اردو',
        'bn' => 'বাংলা',
        'ta' => 'தமிழ்',
        'te' => 'తెలుగు',
        'ml' => 'മലയാളം',
        'kn' => 'ಕನ್ನಡ',
        'gu' => 'ગુજરાતી',
        'pa' => 'ਪੰਜਾਬੀ',
        'ne' => 'नेपाली',
        'si' => 'සිංහල',
        'my' => 'မြန်မာ',
        'km' => 'ខ្មែរ',
        'lo' => 'ລາວ',
        'ka' => 'ქართული',
        'hy' => 'Հայերեն',
        'az' => 'Azərbaycan',
        'kk' => 'Қазақ',
        'ky' => 'Кыргыз',
        'uz' => 'O\'zbek',
        'tg' => 'Тоҷикӣ',
        'mn' => 'Монгол',
        'sw' => 'Kiswahili',
        'am' => 'አማርኛ',
        'zu' => 'isiZulu',
        'af' => 'Afrikaans',
        'xh' => 'isiXhosa',
        'st' => 'Sesotho',
        'tn' => 'Setswana',
        'ss' => 'siSwati',
        've' => 'Tshivenḓa',
        'ts' => 'Xitsonga',
        'nr' => 'isiNdebele',
        'nso' => 'Sepedi',
        'ha' => 'Hausa',
        'yo' => 'Yorùbá',
        'ig' => 'Igbo',
        'rw' => 'Kinyarwanda',
        'rn' => 'Kirundi',
        'lg' => 'Luganda',
        'so' => 'Soomaali',
        'ti' => 'ትግርኛ',
        'om' => 'Oromoo',
        'mg' => 'Malagasy',
    ];
    
    /**
     * Detect user's preferred language using multiple methods
     * 
     * @return string Language code (defaults to 'en')
     */
    public function detectLanguage() {
        // 1. Check URL parameter first (user's explicit choice)
        if (isset($_GET['lang']) && $this->isSupported($_GET['lang'])) {
            $this->setLanguage($_GET['lang']);
            return $_GET['lang'];
        }
        
        // 2. Check session (previously set preference)
        if (isset($_SESSION['language']) && $this->isSupported($_SESSION['language'])) {
            return $_SESSION['language'];
        }
        
        // 3. Check cookie (persistent preference)
        if (isset($_COOKIE['language']) && $this->isSupported($_COOKIE['language'])) {
            $_SESSION['language'] = $_COOKIE['language'];
            return $_COOKIE['language'];
        }
        
        // 4. Check Accept-Language header (browser preference)
        $browserLang = $this->detectFromBrowser();
        if ($browserLang) {
            $this->setLanguage($browserLang);
            return $browserLang;
        }
        
        // 5. Check user's IP geolocation (if available)
        $geoLang = $this->detectFromGeolocation();
        if ($geoLang && $this->isSupported($geoLang)) {
            $this->setLanguage($geoLang);
            return $geoLang;
        }
        
        // 6. Default to English
        $this->setLanguage('en');
        return 'en';
    }
    
    /**
     * Detect language from browser's Accept-Language header
     * 
     * @return string|null Language code or null if not found
     */
    private function detectFromBrowser() {
        $acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        
        if (empty($acceptLanguage)) {
            return null;
        }
        
        // Parse Accept-Language header with quality values
        preg_match_all('/([a-z]{1,8}(?:-[a-z]{1,8})*)(?:;q=([0-9.]+))?/i', $acceptLanguage, $matches);
        
        $languages = [];
        for ($i = 0; $i < count($matches[1]); $i++) {
            $lang = strtolower($matches[1][$i]);
            $quality = isset($matches[2][$i]) && !empty($matches[2][$i]) ? (float)$matches[2][$i] : 1.0;
            $languages[$lang] = $quality;
        }
        
        // Sort by quality (highest first)
        arsort($languages);
        
        // Find the best supported language
        foreach ($languages as $lang => $quality) {
            // Check for exact match first
            foreach ($this->supportedLanguages as $supportedCode => $variants) {
                if (in_array($lang, $variants)) {
                    return $supportedCode;
                }
            }
            
            // Check for partial match (language code without region)
            $langCode = explode('-', $lang)[0];
            foreach ($this->supportedLanguages as $supportedCode => $variants) {
                foreach ($variants as $variant) {
                    if (strpos($variant, $langCode) === 0) {
                        return $supportedCode;
                    }
                }
            }
        }
        
        return null;
    }
    
    /**
     * Detect language from user's geolocation (basic implementation)
     * Note: This is a placeholder for actual geolocation services
     * 
     * @return string|null Language code or null if not detected
     */
    private function detectFromGeolocation() {
        // This is a basic implementation
        // In production, you might want to use services like:
        // - MaxMind GeoIP2
        // - ip-api.com
        // - ipinfo.io
        // - CloudFlare's CF-IPCountry header
        
        // Check CloudFlare country header if available
        if (isset($_SERVER['HTTP_CF_IPCOUNTRY'])) {
            $country = strtoupper($_SERVER['HTTP_CF_IPCOUNTRY']);
            return $this->countryToLanguage[$country] ?? null;
        }
        
        // Example: Simple IP-based detection (not reliable for production)
        $ip = $this->getUserIP();
        if ($ip && $ip !== '127.0.0.1' && $ip !== '::1') {
            // You would implement actual geolocation service here
            // For now, return null to use other detection methods
        }
        
        return null;
    }
    
    /**
     * Get user's real IP address
     * 
     * @return string|null IP address or null
     */
    private function getUserIP() {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? null;
    }
    
    /**
     * Check if language is supported
     * 
     * @param string $lang Language code
     * @return bool
     */
    public function isSupported($lang) {
        $lang = strtolower(trim($lang));
        return array_key_exists($lang, $this->supportedLanguages);
    }
    
    /**
     * Set user's language preference
     * 
     * @param string $lang Language code
     * @return bool Success status
     */
    public function setLanguage($lang) {
        if ($this->isSupported($lang)) {
            // Store in session
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['language'] = $lang;
            
            // Store in cookie for 30 days
            setcookie('language', $lang, [
                'expires' => time() + (86400 * 30),
                'path' => '/',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            
            return true;
        }
        return false;
    }
    
    /**
     * Get all supported languages
     * 
     * @return array Array of language codes
     */
    public function getSupportedLanguages() {
        return array_keys($this->supportedLanguages);
    }
    
    /**
     * Get language name for display
     * 
     * @param string $lang Language code
     * @return string Language name
     */
    public function getLanguageName($lang) {
        return $this->languageNames[$lang] ?? ucfirst($lang);
    }
    
    /**
     * Get all language names for display
     * 
     * @return array Array of language codes => names
     */
    public function getAllLanguageNames() {
        return $this->languageNames;
    }
    
    /**
     * Check if language uses right-to-left writing
     * 
     * @param string $lang Language code
     * @return bool
     */
    public function isRTL($lang) {
        $rtlLanguages = ['ar', 'fa', 'he', 'ur'];
        return in_array($lang, $rtlLanguages);
    }
    
    /**
     * Get language direction for CSS
     * 
     * @param string $lang Language code
     * @return string 'rtl' or 'ltr'
     */
    public function getDirection($lang) {
        return $this->isRTL($lang) ? 'rtl' : 'ltr';
    }
    
    /**
     * Get language variants/dialects
     * 
     * @param string $lang Language code
     * @return array Array of language variants
     */
    public function getLanguageVariants($lang) {
        return $this->supportedLanguages[$lang] ?? [];
    }
    
    /**
     * Debug information about language detection
     * 
     * @return array Debug information
     */
    public function getDebugInfo() {
        return [
            'accept_language' => $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'Not set',
            'session_language' => $_SESSION['language'] ?? 'Not set',
            'cookie_language' => $_COOKIE['language'] ?? 'Not set',
            'url_lang' => $_GET['lang'] ?? 'Not set',
            'user_ip' => $this->getUserIP(),
            'cloudflare_country' => $_SERVER['HTTP_CF_IPCOUNTRY'] ?? 'Not available',
            'detected_language' => $this->detectLanguage(),
        ];
    }
}
?>
