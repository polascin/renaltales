<?php
/**
 * Language Detector Class
 * Detects user's preferred language from multiple sources with fallback to English
 * Supports European, Asian, and African languages with enhanced security and error handling
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0test
 */

namespace RenalTales\Core;

class LanguageDetector {
    
    /**
     * Language to country flag mapping
     */
    private static $languageToFlag = [
        // European languages
        'en' => 'gb',  // English → Great Britain
        'en-us' => 'us',  // American English → United States
        'en-gb' => 'gb',  // British English → Great Britain
        'en-ca' => 'ca',  // Canadian English → Canada
        'en-au' => 'au',  // Australian English → Australia
        'en-nz' => 'nz',  // New Zealand English → New Zealand
        'en-za' => 'za',  // South African English → South Africa
        'sk' => 'sk',  // Slovak → Slovakia
        'cs' => 'cz',  // Czech → Czech Republic
        'de' => 'de',  // German → Germany
        'pl' => 'pl',  // Polish → Poland
        'hu' => 'hu',  // Hungarian → Hungary
        'uk' => 'ua',  // Ukrainian → Ukraine
        'ru' => 'ru',  // Russian → Russia
        'it' => 'it',  // Italian → Italy
        'nl' => 'nl',  // Dutch → Netherlands
        'fr' => 'fr',  // French → France
        'es' => 'es',  // Spanish → Spain
        'pt' => 'pt',  // Portuguese → Portugal
        'ro' => 'ro',  // Romanian → Romania
        'bg' => 'bg',  // Bulgarian → Bulgaria
        'sl' => 'si',  // Slovenian → Slovenia
        'hr' => 'hr',  // Croatian → Croatia
        'sr' => 'rs',  // Serbian → Serbia
        'mk' => 'mk',  // Macedonian → North Macedonia
        'sq' => 'al',  // Albanian → Albania
        'el' => 'gr',  // Greek → Greece
        'da' => 'dk',  // Danish → Denmark
        'no' => 'no',  // Norwegian → Norway
        'sv' => 'se',  // Swedish → Sweden
        'fi' => 'fi',  // Finnish → Finland
        'is' => 'is',  // Icelandic → Iceland
        'et' => 'ee',  // Estonian → Estonia
        'lv' => 'lv',  // Latvian → Latvia
        'lt' => 'lt',  // Lithuanian → Lithuania
        'tr' => 'tr',  // Turkish → Turkey
        'eo' => 'eo',  // Esperanto → Special Esperanto flag
        // NEW European languages
        'ca' => 'ad',  // Catalan → Andorra (also used in Spain)
        'eu' => 'es',  // Basque → Spain
        'cy' => 'gb-wls',  // Welsh → Wales
        'ga' => 'ie',  // Irish → Ireland
        'gd' => 'gb-sct',  // Scottish Gaelic → Scotland
        'mt' => 'mt',  // Maltese → Malta
        'gl' => 'es',  // Galician → Spain
        'be' => 'by',  // Belarusian → Belarus
        'lb' => 'lu',  // Luxembourgish → Luxembourg
        'rm' => 'ch',  // Romansh → Switzerland
        'fo' => 'fo',  // Faroese → Faroe Islands
        'kl' => 'gl',  // Greenlandic → Greenland
        'se' => 'no',  // Northern Sami → Norway
        
        // Asian languages
        'ja' => 'jp',  // Japanese → Japan
        'zh' => 'cn',  // Chinese → China
        'ko' => 'kr',  // Korean → South Korea
        'th' => 'th',  // Thai → Thailand
        'vi' => 'vn',  // Vietnamese → Vietnam
        'hi' => 'in',  // Hindi → India
        'ar' => 'sa',  // Arabic → Saudi Arabia
        'fa' => 'ir',  // Persian → Iran
        'he' => 'il',  // Hebrew → Israel
        'ur' => 'pk',  // Urdu → Pakistan
        'bn' => 'bd',  // Bengali → Bangladesh
        'ta' => 'in',  // Tamil → India
        // Additional Asian languages
        'id' => 'id',  // Indonesian → Indonesia
        'ms' => 'my',  // Malay → Malaysia
        'tl' => 'ph',  // Filipino → Philippines
        'mr' => 'in',  // Marathi → India
        'jv' => 'id',  // Javanese → Indonesia
        'yue' => 'hk',  // Cantonese → Hong Kong
        'wu' => 'cn',  // Wu Chinese → China
        'bho' => 'in',  // Bhojpuri → India
        'ps' => 'af',  // Pashto → Afghanistan
        'su' => 'id',  // Sundanese → Indonesia
        'or' => 'in',  // Odia → India
        'as' => 'in',  // Assamese → India
        'gu' => 'in',  // Gujarati → India
        'kn' => 'in',  // Kannada → India
        'te' => 'in',  // Telugu → India
        'ml' => 'in',  // Malayalam → India
        'pa' => 'in',  // Punjabi → India
        'ne' => 'np',  // Nepali → Nepal
        'my' => 'mm',  // Burmese → Myanmar
        'km' => 'kh',  // Khmer → Cambodia
        'lo' => 'la',  // Lao → Laos
        'ka' => 'ge',  // Georgian → Georgia
        'hy' => 'am',  // Armenian → Armenia
        'az' => 'az',  // Azerbaijani → Azerbaijan
        'kk' => 'kz',  // Kazakh → Kazakhstan
        'ky' => 'kg',  // Kyrgyz → Kyrgyzstan
        'uz' => 'uz',  // Uzbek → Uzbekistan
        'tg' => 'tj',  // Tajik → Tajikistan
        'mn' => 'mn',  // Mongolian → Mongolia
        'si' => 'lk',  // Sinhala → Sri Lanka
        'bo' => 'cn',  // Tibetan → China (Tibet)
        'ug' => 'cn',  // Uyghur → China
        'dv' => 'mv',  // Dhivehi → Maldives
        'tk' => 'tm',  // Turkmen → Turkmenistan
        'sd' => 'pk',  // Sindhi → Pakistan
        'mai' => 'in',  // Maithili → India
        'bh' => 'in',  // Bihari → India
        'sa' => 'in',  // Sanskrit → India
        'la' => 'va',  // Latin → Vatican City
        
        // African languages
        'sw' => 'ke',  // Swahili → Kenya
        'af' => 'za',  // Afrikaans → South Africa
        'am' => 'et',  // Amharic → Ethiopia
        'ha' => 'ng',  // Hausa → Nigeria
        'yo' => 'ng',  // Yoruba → Nigeria
        'ig' => 'ng',  // Igbo → Nigeria
        'zu' => 'za',  // Zulu → South Africa
        'xh' => 'za',  // Xhosa → South Africa
        'tn' => 'za',  // Tswana → South Africa
        'st' => 'za',  // Sesotho → South Africa
        'ss' => 'za',  // Swati → South Africa
        'nr' => 'za',  // Ndebele → South Africa
        'nso' => 'za', // Northern Sotho → South Africa
        've' => 'za',  // Venda → South Africa
        'ts' => 'za',  // Tsonga → South Africa
        'ti' => 'er',  // Tigrinya → Eritrea
        'om' => 'et',  // Oromo → Ethiopia
        'so' => 'so',  // Somali → Somalia
        'rw' => 'rw',  // Kinyarwanda → Rwanda
        'rn' => 'bi',  // Kirundi → Burundi
        'lg' => 'ug',  // Luganda → Uganda
        'nd' => 'zw',  // Ndebele (Zimbabwe) → Zimbabwe
        'lua' => 'cd', // Luba-Lulua → DRC
        'mg' => 'mg',  // Malagasy → Madagascar
        'ff' => 'sn',  // Fulani/Fula → Senegal
        'ln' => 'cd',  // Lingala → DRC
        'bm' => 'ml',  // Bambara → Mali
        'ak' => 'gh',  // Akan/Twi → Ghana
        'sn' => 'zw',  // Shona → Zimbabwe
        'ny' => 'mw',  // Chichewa → Malawi
        'wo' => 'sn',  // Wolof → Senegal
        'sg' => 'cf',  // Sango → Central African Republic
        'kg' => 'cd',  // Kongo → DRC
        'lu' => 'cd',  // Luba-Katanga → DRC
        
        // American languages
        'qu' => 'pe',  // Quechua → Peru
        'gn' => 'py',  // Guaraní → Paraguay
        'ay' => 'bo',  // Aymara → Bolivia
        'ht' => 'ht',  // Haitian Creole → Haiti
        
        // Additional languages
        'ceb' => 'ph', // Cebuano → Philippines
        'hil' => 'ph', // Hiligaynon → Philippines
        'war' => 'ph', // Waray → Philippines
        'bcl' => 'ph', // Bikol → Philippines
        'pam' => 'ph', // Kapampangan → Philippines
        'ilo' => 'ph', // Ilocano → Philippines
    ];
    
    /**
     * Cache for file existence checks to improve performance
     */
    private static $fileExistenceCache = [];
    
    /**
     * Cache for flag paths to improve performance
     */
    private static $flagPathCache = [];
    
    /**
     * Flag usage statistics for monitoring
     */
    private static $flagUsageStats = [];
    
    /**
     * Missing flag logs to track gaps
     */
    private static $missingFlagLogs = [];
    
    /**
     * Supported languages with their variants
     */
    private $supportedLanguages = [
        // Core supported languages (languages that have translation files)
        'en' => ['en', 'en-us', 'en-gb', 'en-ca', 'en-au', 'en-nz', 'en-za'],
        'sk' => ['sk', 'sk-sk'],
        'cs' => ['cs', 'cs-cz'],
        'de' => ['de', 'de-de', 'de-at', 'de-ch'],
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
        'MN' => 'mn', 'NP' => 'ne', 'ID' => 'id', 'MY' => 'ms', 'PH' => 'tl',
        'AF' => 'ps', 'TM' => 'tk', 'MV' => 'dv', 'BT' => 'dz',
        
        // Africa
        'KE' => 'sw', 'TZ' => 'sw', 'ET' => 'am', 'ZA' => 'af', 'NG' => 'ha',
        'RW' => 'rw', 'BI' => 'rn', 'UG' => 'lg', 'SO' => 'so', 'ER' => 'ti',
        'MG' => 'mg', 'MW' => 'ny', 'ZW' => 'sn', 'ML' => 'bm', 'SN' => 'wo',
        'CD' => 'ln', 'CF' => 'sg',
        
        // Americas
        'HT' => 'ht', 'PE' => 'qu', 'PY' => 'gn', 'BO' => 'ay',
        
        // Default English-speaking countries
        'US' => 'en-us', 'GB' => 'en', 'CA' => 'en', 'AU' => 'en', 'NZ' => 'en',
    ];
    
    /**
     * Language names for display purposes
     */
    private $languageNames = [
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
        // NEW language names
        'ca' => 'Català',
        'eu' => 'Euskera',
        'cy' => 'Cymraeg',
        'ga' => 'Gaeilge',
        'gd' => 'Gàidhlig',
        'mt' => 'Malti',
        'gl' => 'Galego',
        'be' => 'Беларуская',
        'lb' => 'Lëtzebuergesch',
        'rm' => 'Rumantsch',
        'fo' => 'Føroyskt',
        'kl' => 'Kalaallisut',
        'se' => 'Sámegiella',
        'id' => 'Bahasa Indonesia',
        'ms' => 'Bahasa Melayu',
        'tl' => 'Filipino',
        'mr' => 'मराठी',
        'jv' => 'Basa Jawa',
        'yue' => '粵語',
        'wuu' => '吳語',
        'bho' => 'भोजपुरी',
        'ps' => 'پښتو',
        'su' => 'Basa Sunda',
        'or' => 'ଓଡ଼ିଆ',
        'as' => 'অসমীয়া',
        'dv' => 'ދިވެހި',
        'bo' => 'བོད་ཡིག',
        'ug' => 'ئۇيغۇرچە',
        'sd' => 'سنڌي',
        'mai' => 'मैथिली',
        'bh' => 'भोजपुरी',
        'sa' => 'संस्कृतम्',
        'la' => 'Latina',
        'tk' => 'Türkmen',
        'ny' => 'Chichewa',
        'sn' => 'ChiShona',
        'nd' => 'isiNdebele',
        'bm' => 'Bamanankan',
        'ff' => 'Fulfulde',
        'wo' => 'Wolof',
        'ln' => 'Lingála',
        'kg' => 'Kikongo',
        'lua' => 'Tshiluba',
        'sg' => 'Sängö',
        'ht' => 'Kreyòl Ayisyen',
        'qu' => 'Runasimi',
        'gn' => 'Avañe\'ẽ',
        'ay' => 'Aymar aru',
        'ceb' => 'Cebuano',
        'hil' => 'Hiligaynon',
        'war' => 'Winaray',
        'bcl' => 'Bikol',
        'pam' => 'Kapampangan',
        'ilo' => 'Ilokano',
    ];
    
    /**
     * Constructor - Initialize supported languages from available language files
     */
    public function __construct() {
        $this->loadSupportedLanguagesFromFiles();
    }
    
    /**
     * Load supported languages from available language files
     */
    private function loadSupportedLanguagesFromFiles() {
        if (!defined('LANGUAGE_PATH')) {
            return; // Keep default supported languages if path not defined
        }
        
        try {
            $languageFiles = glob(LANGUAGE_PATH . '*.php');
            if ($languageFiles === false) {
                return; // Keep default if glob fails
            }
            
            $availableLanguages = [];
            foreach ($languageFiles as $file) {
                $langCode = basename($file, '.php');
                // Support both standard language codes (2-3 letters) and region-specific codes (en-us)
                if (preg_match('/^[a-z]{2,3}(-[a-z]{2})?$/', $langCode)) {
                    // Determine base language (e.g., 'en' from 'en-us')
                    $baseLang = strpos($langCode, '-') !== false ? substr($langCode, 0, strpos($langCode, '-')) : $langCode;
                    
                    // Add to appropriate language group
                    if (!isset($availableLanguages[$baseLang])) {
                        $availableLanguages[$baseLang] = [];
                    }
                    
                    // Ensure base language is first in its group
                    if ($langCode === $baseLang && !in_array($baseLang, $availableLanguages[$baseLang])) {
                        array_unshift($availableLanguages[$baseLang], $baseLang);
                    } elseif ($langCode !== $baseLang && !in_array($langCode, $availableLanguages[$baseLang])) {
                        $availableLanguages[$baseLang][] = $langCode;
                    }
                }
            }
            
            // Only update if we found language files
            if (!empty($availableLanguages)) {
                $this->supportedLanguages = array_merge($this->supportedLanguages, $availableLanguages);
            }
        } catch(Exception $e) {
            error_log('LanguageDetector: Failed to load language files: ' . $e->getMessage());
        }
    }
    
    /**
     * Validate language code format
     * 
     * @param string $lang Language code to validate
     * @return bool True if valid format
     */
    private function isValidLanguageFormat($lang) {
        if (!is_string($lang)) {
            return false;
        }
        
        $lang = trim($lang);
        
        // Check for valid language code format (2-3 letters, optionally with region)
        return preg_match('/^[a-z]{2,3}(-[a-z]{2,4})?$/i', $lang);
    }
    
    /**
     * Sanitize language input
     * 
     * @param string $lang Language code
     * @return string|null Sanitized language code or null if invalid
     */
    private function sanitizeLanguage($lang) {
        if (!$this->isValidLanguageFormat($lang)) {
            return null;
        }
        
        return strtolower(trim($lang));
    }
    
    /**
     * Detect user's preferred language using multiple methods
     * 
     * @return string Language code (defaults to 'en')
     */
    public function detectLanguage() {
        try {
            if ($this->isDebugMode()) {
                error_log('LanguageDetector: Starting language detection');
            }
            
            // 1. Check URL parameter first (user's explicit choice)
            if (isset($_GET['lang'])) {
                $urlLang = $this->sanitizeLanguage($_GET['lang']);
                if ($this->isDebugMode()) {
                    error_log('LanguageDetector: URL parameter found: ' . ($_GET['lang'] ?? 'null') . ' -> sanitized: ' . ($urlLang ?: 'null'));
                }
                if ($urlLang && $this->isSupported($urlLang)) {
                    $this->setLanguage($urlLang);
                    if ($this->isDebugMode()) {
                        error_log('LanguageDetector: URL language selected: ' . $urlLang);
                    }
                    return $urlLang;
                }
            }
            
            // 2. Check session (previously set preference) - return early if valid
            $sessionLang = $this->getSessionLanguage();
            if ($this->isDebugMode()) {
                error_log('LanguageDetector: Session language: ' . ($sessionLang ?: 'null'));
            }
            if ($sessionLang && $this->isSupported($sessionLang) && $this->isValidSessionLanguage($sessionLang)) {
                if ($this->isDebugMode()) {
                    error_log('LanguageDetector: Session language selected: ' . $sessionLang);
                }
                return $sessionLang;
            }
            
            // 3. Check cookie (persistent preference)
            $cookieLang = $this->getCookieLanguage();
            if ($this->isDebugMode()) {
                error_log('LanguageDetector: Cookie language: ' . ($cookieLang ?: 'null'));
            }
            if ($cookieLang && $this->isSupported($cookieLang)) {
                $this->setSessionLanguage($cookieLang);
                if ($this->isDebugMode()) {
                    error_log('LanguageDetector: Cookie language selected: ' . $cookieLang);
                }
                return $cookieLang;
            }
            
            // 4. Check Accept-Language header (browser preference)
            $browserLang = $this->detectFromBrowser();
            if ($this->isDebugMode()) {
                error_log('LanguageDetector: Browser detected language: ' . ($browserLang ?: 'null'));
            }
            if ($browserLang) {
                $this->setLanguage($browserLang);
                if ($this->isDebugMode()) {
                    error_log('LanguageDetector: Browser language selected: ' . $browserLang);
                }
                return $browserLang;
            }
            
            // 5. Check user's IP geolocation (if available)
            $geoLang = $this->detectFromGeolocation();
            if ($geoLang && $this->isSupported($geoLang)) {
                $this->setLanguage($geoLang);
                return $geoLang;
            }
            
            // 6. Default to English
            if ($this->isDebugMode()) {
                error_log('LanguageDetector: Falling back to default language: en');
            }
            $this->setLanguage('en');
            return 'en';
        } catch(Exception $e) {
            error_log('LanguageDetector: Language detection failed: ' . $e->getMessage());
            return 'en'; // Safe fallback
        }
    }
    
    /**
     * Get current active language
     * Returns the currently set language from session, cookie, or detects it
     * 
     * @return string Current language code
     */
    public function getCurrentLanguage() {
        try {
            // First check session with validation - return early if valid
            $sessionLang = $this->getSessionLanguage();
            if ($sessionLang && $this->isSupported($sessionLang) && $this->isValidSessionLanguage($sessionLang)) {
                return $sessionLang;
            }
            
            // Then check cookie
            $cookieLang = $this->getCookieLanguage();
            if ($cookieLang && $this->isSupported($cookieLang)) {
                // Update session with cookie language
                $this->setSessionLanguage($cookieLang);
                return $cookieLang;
            }
            
            // Finally detect from browser/system
            return $this->detectLanguage();
            
        } catch(Exception $e) {
            error_log('LanguageDetector: Failed to get current language: ' . $e->getMessage());
            return 'en'; // Default fallback
        }
    }
    
    /**
     * Get language from session safely
     * 
     * @return string|null
     */
    private function getSessionLanguage() {
        if (!$this->isSessionAvailable()) {
            return null;
        }
        
        $sessionLang = $_SESSION['language'] ?? null;
        return $this->sanitizeLanguage($sessionLang);
    }
    
    /**
     * Get language from cookie safely
     * 
     * @return string|null
     */
    private function getCookieLanguage() {
        $cookieLang = $_COOKIE['language'] ?? null;
        return $this->sanitizeLanguage($cookieLang);
    }
    
    /**
     * Set language in session safely
     * 
     * @param string $lang Language code
     * @return bool Success status
     */
    private function setSessionLanguage($lang) {
        if (!$this->isSessionAvailable()) {
            return false;
        }
        
        try {
            $_SESSION['language'] = $lang;
            return true;
        } catch(Exception $e) {
            error_log('LanguageDetector: Failed to set session language: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if session is available and safe to use
     * 
     * @return bool
     */
    private function isSessionAvailable() {
        return session_status() === PHP_SESSION_ACTIVE;
    }
    
    /**
     * Validate session language to prevent overriding valid settings
     * 
     * @param string $sessionLang Language code from session
     * @return bool True if session language is valid and should be used
     */
    private function isValidSessionLanguage($sessionLang) {
        if (!$sessionLang) {
            return false;
        }
        
        // Check if session language is properly formatted
        if (!$this->isValidLanguageFormat($sessionLang)) {
            return false;
        }
        
        // Check if session language is supported
        if (!$this->isSupported($sessionLang)) {
            return false;
        }
        
        // Additional validation: check if session was set in current request
        // This prevents stale session data from overriding URL parameters
        if (isset($_GET['lang']) && $this->sanitizeLanguage($_GET['lang']) !== $sessionLang) {
            // If URL parameter exists and differs from session, URL takes priority
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if HTTPS is being used
     * 
     * @return bool
     */
    private function isHttps() {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
            || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on');
    }
    
    /**
     * Detect language from browser's Accept-Language header
     * 
     * @return string|null Language code or null if not found
     */
    private function detectFromBrowser() {
        $acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        
        if (empty($acceptLanguage) || strlen($acceptLanguage) > 1000) {
            return null; // Prevent potential DoS with very long headers
        }
        
        try {
            // Parse Accept-Language header with quality values
            if (!preg_match_all('/([a-z]{1,8}(?:-[a-z]{1,8})*)(?:;q=([0-9.]+))?/i', $acceptLanguage, $matches)) {
                return null;
            }
            
            $languages = [];
            for ($i = 0; $i < count($matches[1]); $i++) {
                $lang = strtolower($matches[1][$i]);
                $quality = isset($matches[2][$i]) && !empty($matches[2][$i]) ? (float)$matches[2][$i] : 1.0;
                
                // Validate quality value
                if ($quality < 0 || $quality > 1) {
                    continue;
                }
                
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
                if ($this->isSupported($langCode)) {
                    return $langCode;
                }
            }
        } catch(Exception $e) {
            error_log('LanguageDetector: Browser detection failed: ' . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Detect language from user's geolocation
     * 
     * @return string|null Language code or null if not detected
     */
    private function detectFromGeolocation() {
        try {
            // Check CloudFlare country header if available
            if (isset($_SERVER['HTTP_CF_IPCOUNTRY'])) {
                $country = strtoupper(trim($_SERVER['HTTP_CF_IPCOUNTRY']));
                if (preg_match('/^[A-Z]{2}$/', $country)) {
                    return $this->countryToLanguage[$country] ?? null;
                }
            }
            
            // Check other common country headers
            $countryHeaders = [
                'HTTP_X_COUNTRY_CODE',
                'HTTP_X_GEOIP_COUNTRY',
                'HTTP_CF_IPCOUNTRY'
            ];
            
            foreach ($countryHeaders as $header) {
                if (isset($_SERVER[$header])) {
                    $country = strtoupper(trim($_SERVER[$header]));
                    if (preg_match('/^[A-Z]{2}$/', $country)) {
                        return $this->countryToLanguage[$country] ?? null;
                    }
                }
            }
            
            // Could implement actual geolocation service here
            // For security and performance reasons, we don't do IP-based lookups by default
            
        } catch(Exception $e) {
            error_log('LanguageDetector: Geolocation detection failed: ' . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Get user's real IP address safely
     * 
     * @return string|null IP address or null
     */
    private function getUserIP() {
        $ipHeaders = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP', 
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        ];
        
        foreach ($ipHeaders as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                
                // Validate IP and exclude private/reserved ranges for security
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        // Return local IP as fallback (for development)
        return $_SERVER['REMOTE_ADDR'] ?? null;
    }
    
    /**
     * Check if language is supported
     * 
     * @param string $lang Language code
     * @return bool
     */
    public function isSupported($lang) {
        $lang = $this->sanitizeLanguage($lang);
        if (!$lang) {
            return false;
        }
        
        return array_key_exists($lang, $this->supportedLanguages);
    }
    
    /**
     * Set user's language preference with enhanced security
     * 
     * @param string $lang Language code
     * @return bool Success status
     */
    public function setLanguage($lang) {
        $lang = $this->sanitizeLanguage($lang);
        if (!$lang || !$this->isSupported($lang)) {
            return false;
        }
        
        try {
            // Store in session if available (with validation)
            $this->setSessionLanguage($lang);
            
            // Store in secure cookie for 30 days (only if headers not already sent)
            if (!headers_sent()) {
                $cookieOptions = [
                    'expires' => time() + (86400 * 30),
                    'path' => '/',
                    'domain' => '',
                    'secure' => $this->isHttps(),
                    'httponly' => true,
                    'samesite' => 'Lax'
                ];
                
                if (PHP_VERSION_ID >= 70300) {
                    // Use modern cookie options for PHP 7.3+
                    setcookie('language', $lang, $cookieOptions);
                } else {
                    // Fallback for older PHP versions
                    setcookie(
                        'language',
                        $lang,
                        $cookieOptions['expires'],
                        $cookieOptions['path'],
                        $cookieOptions['domain'],
                        $cookieOptions['secure'],
                        $cookieOptions['httponly']
                    );
                }
            } else {
                // Headers already sent, skip cookie setting but log it
                error_log('LanguageDetector: Headers already sent, skipping cookie setting for language: ' . $lang);
            }
            
            return true;
        } catch(Exception $e) {
            error_log('LanguageDetector: Failed to set language: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Reset language preference (useful for testing)
     * 
     * @return bool Success status
     */
    public function resetLanguage() {
        try {
            // Clear session language
            if ($this->isSessionAvailable()) {
                unset($_SESSION['language']);
            }
            
            // Clear cookie from $_COOKIE superglobal for immediate effect
            if (isset($_COOKIE['language'])) {
                unset($_COOKIE['language']);
            }
            
            // Clear cookie if headers not sent
            if (!headers_sent()) {
                $cookieOptions = [
                    'expires' => time() - 3600, // Set to past time to delete
                    'path' => '/',
                    'domain' => '',
                    'secure' => $this->isHttps(),
                    'httponly' => true,
                    'samesite' => 'Lax'
                ];
                
                if (PHP_VERSION_ID >= 70300) {
                    setcookie('language', '', $cookieOptions);
                } else {
                    setcookie(
                        'language',
                        '',
                        $cookieOptions['expires'],
                        $cookieOptions['path'],
                        $cookieOptions['domain'],
                        $cookieOptions['secure'],
                        $cookieOptions['httponly']
                    );
                }
            }
            
            return true;
        } catch(Exception $e) {
            error_log('LanguageDetector: Failed to reset language: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Priority order for language display
     * Based on: en, sk, cs, de, pl, hu, uk, ru, fr, es, it, pt, nl, da, no, se, fi, lt, lv, et, sl, hr, bg, ro,
     * then other European languages by speaker count, then Asian languages by speaker count, then all others by speaker count
     */
    private $languagePriority = [
        // Core priority languages
        'en', 'en-us', 'en-gb', 'en-ca', 'en-au', 'en-nz', 'en-za', 'sk', 'cs', 'de', 'pl', 'hu', 'uk', 'ru', 'fr', 'es', 'it', 'pt', 'nl', 'da', 'no', 'sv', 'fi', 'lt', 'lv', 'et', 'sl', 'hr', 'bg', 'ro',
        
        // Other European languages (by speaker count)
        'tr', 'el', 'sr', 'mk', 'sq', 'be', 'is', 'mt', 'ga', 'cy', 'eu', 'ca', 'gl', 'lb', 'rm', 'fo', 'kl', 'se', 'gd',
        
        // Asian languages (by speaker count)
        'zh', 'hi', 'ar', 'bn', 'ur', 'id', 'ja', 'ko', 'vi', 'th', 'ms', 'tl', 'fa', 'he', 'ta', 'te', 'mr', 'gu', 'kn', 'ml', 'pa', 'ne', 'si', 'my', 'km', 'lo', 'ka', 'hy', 'az', 'kk', 'ky', 'uz', 'tg', 'mn', 'jv', 'yue', 'wuu', 'bho', 'ps', 'su', 'or', 'as', 'mai', 'bh', 'sa', 'la', 'sd', 'dv', 'tk', 'bo', 'ug',
        
        // African languages (by speaker count)
        'sw', 'am', 'ha', 'yo', 'ig', 'zu', 'af', 'xh', 'so', 'mg', 'rw', 'rn', 'lg', 'sn', 'ny', 'wo', 'ln', 'kg', 'lua', 'sg', 'ff', 'bm', 'ak', 'om', 'ti', 'nd', 'nr', 'nso', 'st', 'ss', 've', 'ts', 'tn',
        
        // American indigenous and other languages
        'ht', 'qu', 'gn', 'ay', 'eo',
        
        // Regional/minority languages
        'ceb', 'hil', 'war', 'bcl', 'pam', 'ilo'
    ];

    /**
     * Get all supported languages (only those with translation files) in prioritized order
     * 
     * @return array Array of language codes in priority order
     */
    public function getSupportedLanguages() {
        $availableLanguages = array_keys($this->supportedLanguages);
        $prioritizedLanguages = [];
        $remainingLanguages = [];
        
        // First, add languages in priority order if they exist
        foreach ($this->languagePriority as $priorityLang) {
            if (in_array($priorityLang, $availableLanguages)) {
                $prioritizedLanguages[] = $priorityLang;
            }
        }
        
        // Then add any remaining languages that weren't in the priority list
        foreach ($availableLanguages as $lang) {
            if (!in_array($lang, $prioritizedLanguages)) {
                $remainingLanguages[] = $lang;
            }
        }
        
        // Sort remaining languages alphabetically
        sort($remainingLanguages);
        
        return array_merge($prioritizedLanguages, $remainingLanguages);
    }
    
    /**
     * Get language name for display
     * 
     * @param string $lang Language code
     * @return string Language name
     */
    public function getLanguageName($lang) {
        $lang = $this->sanitizeLanguage($lang);
        if (!$lang) {
            return 'Unknown';
        }
        
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
        $lang = $this->sanitizeLanguage($lang);
        if (!$lang) {
            return false;
        }
        
        $rtlLanguages = ['ar', 'fa', 'he', 'ur', 'dv', 'sd'];
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
     * Get flag code for a language with validation
     * 
     * @param string $languageCode Language code
     * @return string Flag code (defaults to 'un' if not found)
     */
    public function getFlagCode($languageCode) {
        $languageCode = $this->sanitizeLanguage($languageCode);
        if (!$languageCode) {
            return 'un';
        }
        
        return self::$languageToFlag[$languageCode] ?? 'un';
    }
    
    /**
     * Get complete flag path for a language with caching
     *
     * @param string $languageCode Language code
     * @param string $basePath Base path for flags
     * @param string $extension File extension
     * @return string Complete path to the flag
     */
    public function getFlagPath($languageCode, $basePath = 'assets/flags/', $extension = '.webp') {
        $languageCode = $this->sanitizeLanguage($languageCode);
        if (!$languageCode) {
            return $basePath . 'un' . $extension;
        }
        
        // Create cache key
        $cacheKey = $languageCode . '|' . $basePath . '|' . $extension;
        
        if (isset(self::$flagPathCache[$cacheKey])) {
            return self::$flagPathCache[$cacheKey];
        }
        
        $flagCode = $this->getFlagCode($languageCode);
        $flagPath = $basePath . $flagCode . $extension;
        
        // Cache the result
        self::$flagPathCache[$cacheKey] = $flagPath;
        
        // Record usage
        self::$flagUsageStats[$flagCode] = (self::$flagUsageStats[$flagCode] ?? 0) + 1;
        
        return $flagPath;
    }
    
    /**
     * Get the best available flag path with format fallback and caching
     * 
     * @param string $languageCode Language code
     * @param string $basePath Base path for flags
     * @param string $documentRoot Document root path for file existence checking
     * @return string Complete path to the best available flag format
     */
    public function getBestFlagPath($languageCode, $basePath = 'assets/flags/', $documentRoot = null) {
        $languageCode = $this->sanitizeLanguage($languageCode);
        if (!$languageCode) {
            return $basePath . 'un.webp';
        }
        
        // Create cache key
        $cacheKey = $languageCode . '|' . $basePath . '|' . ($documentRoot ?? 'default');
        
        if (isset(self::$flagPathCache[$cacheKey])) {
            return self::$flagPathCache[$cacheKey];
        }
        
        $flagCode = $this->getFlagCode($languageCode);
        
        // Set default document root if not provided
        if ($documentRoot === null) {
            $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
        }
        
        // Try formats in order of preference: WEBP > PNG > JPG
        $formats = ['.webp', '.png', '.jpg', '.gif'];
        
        foreach ($formats as $extension) {
            $flagPath = $basePath . $flagCode . $extension;
            $fullPath = rtrim($documentRoot, '/') . '/' . ltrim($flagPath, '/');
            
            // Check cache first
            if (isset(self::$fileExistenceCache[$fullPath])) {
                $exists = self::$fileExistenceCache[$fullPath];
            } else {
                // Check file existence and cache result
                $exists = @file_exists($fullPath);
                self::$fileExistenceCache[$fullPath] = $exists;
            }
            
            if ($exists) {
                self::$flagPathCache[$cacheKey] = $flagPath;
                return $flagPath;
            }
        }
        
        // If no flag found, log it and return WEBP path as fallback
        $fallbackPath = $basePath . $flagCode . '.webp';
        self::$flagPathCache[$cacheKey] = $fallbackPath;
        
        // Log missing flag for monitoring
        $this->logMissingFlag($languageCode, $flagCode);
        
        return $fallbackPath;
    }
    
    /**
     * Get language variants/dialects
     * 
     * @param string $lang Language code
     * @return array Array of language variants
     */
    public function getLanguageVariants($lang) {
        $lang = $this->sanitizeLanguage($lang);
        if (!$lang) {
            return [];
        }
        
        return $this->supportedLanguages[$lang] ?? [];
    }
    
    /**
     * Clear all caches (useful for testing or when flags are updated)
     */
    public function clearCaches() {
        self::$fileExistenceCache = [];
        self::$flagPathCache = [];
    }
    
    /**
     * Get cache statistics
     * 
     * @return array Cache statistics
     */
    public function getCacheStats() {
        return [
            'file_existence_cache_size' => count(self::$fileExistenceCache),
            'flag_path_cache_size' => count(self::$flagPathCache),
            'memory_usage' => memory_get_usage(true),
        ];
    }
    
    /**
     * Debug information about language detection with enhanced security
     * 
     * @return array Debug information (filtered for security)
     */
    public function getDebugInfo() {
        $debugInfo = [
            'detected_language' => $this->detectLanguage(),
            'supported_languages_count' => count($this->supportedLanguages),
            'cache_stats' => $this->getCacheStats(),
        ];
        
        // Only include sensitive information in debug mode and for allowed IPs
        $clientIP = $this->getUserIP();
        $allowedDebugIPs = ['127.0.0.1', '::1'];
        
        if (defined('DEBUG_MODE') && DEBUG_MODE && in_array($clientIP, $allowedDebugIPs)) {
            $debugInfo = array_merge($debugInfo, [
                'accept_language' => $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'Not set',
                'session_language' => $this->getSessionLanguage() ?? 'Not set',
                'cookie_language' => $this->getCookieLanguage() ?? 'Not set',
                'url_lang' => $this->sanitizeLanguage($_GET['lang'] ?? '') ?? 'Not set',
                'user_ip' => $clientIP,
                'cloudflare_country' => $_SERVER['HTTP_CF_IPCOUNTRY'] ?? 'Not available',
                'https_detected' => $this->isHttps(),
                'session_available' => $this->isSessionAvailable(),
            ]);
        }
        
        return $debugInfo;
    }
    
    /**
     * Log missing flag for monitoring and alerting
     * 
     * @param string $languageCode Original language code
     * @param string $flagCode Flag code that was missing
     */
    private function logMissingFlag($languageCode, $flagCode) {
        $logKey = $languageCode . '|' . $flagCode;
        
        // Prevent duplicate logging for the same missing flag
        if (isset(self::$missingFlagLogs[$logKey])) {
            return;
        }
        
        self::$missingFlagLogs[$logKey] = [
            'timestamp' => time(),
            'language_code' => $languageCode,
            'flag_code' => $flagCode,
            'user_ip' => $this->getUserIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ];
        
        // Log to error log
        $message = sprintf(
            'FLAG_MISSING: Language=%s, Flag=%s, IP=%s, UserAgent=%s',
            $languageCode,
            $flagCode,
            $this->getUserIP(),
            substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 100)
        );
        
        error_log($message);
    }
    
    /**
     * Get flag usage statistics
     * 
     * @return array Flag usage statistics
     */
    public function getFlagUsageStats() {
        return self::$flagUsageStats;
    }
    
    /**
     * Get missing flag logs
     * 
     * @return array Missing flag logs
     */
    public function getMissingFlagLogs() {
        return self::$missingFlagLogs;
    }
    
    /**
     * Get detailed flag monitoring report
     * 
     * @return array Comprehensive flag monitoring data
     */
    public function getFlagMonitoringReport() {
        $report = [
            'timestamp' => time(),
            'cache_stats' => $this->getCacheStats(),
            'usage_stats' => self::$flagUsageStats,
            'missing_flags' => self::$missingFlagLogs,
            'total_flag_requests' => array_sum(self::$flagUsageStats),
            'unique_flags_used' => count(self::$flagUsageStats),
            'missing_flag_count' => count(self::$missingFlagLogs),
            'cache_hit_rate' => $this->calculateCacheHitRate(),
            'top_flags' => $this->getTopFlags(),
            'recommendations' => $this->generateMonitoringRecommendations()
        ];
        
        return $report;
    }
    
    /**
     * Calculate cache hit rate
     * 
     * @return float Cache hit rate percentage
     */
    private function calculateCacheHitRate() {
        $totalRequests = array_sum(self::$flagUsageStats);
        $cacheSize = count(self::$flagPathCache);
        
        if ($totalRequests === 0) {
            return 0.0;
        }
        
        // Simplified calculation based on cache size vs requests
        return min(($cacheSize / $totalRequests) * 100, 100.0);
    }
    
    /**
     * Get top used flags
     * 
     * @param int $limit Number of top flags to return
     * @return array Top flags with usage counts
     */
    private function getTopFlags($limit = 10) {
        $sortedFlags = self::$flagUsageStats;
        arsort($sortedFlags);
        
        return array_slice($sortedFlags, 0, $limit, true);
    }
    
    /**
     * Generate monitoring recommendations
     * 
     * @return array Recommendations for flag system optimization
     */
    private function generateMonitoringRecommendations() {
        $recommendations = [];
        
        // Check for missing flags
        if (count(self::$missingFlagLogs) > 0) {
            $recommendations[] = 'Create missing flag files: ' . implode(', ', array_unique(array_column(self::$missingFlagLogs, 'flag_code')));
        }
        
        // Check cache performance
        $cacheHitRate = $this->calculateCacheHitRate();
        if ($cacheHitRate < 80) {
            $recommendations[] = 'Consider optimizing cache strategy - current hit rate: ' . number_format($cacheHitRate, 1) . '%';
        }
        
        // Check for frequently used flags
        $totalRequests = array_sum(self::$flagUsageStats);
        if ($totalRequests > 1000) {
            $recommendations[] = 'High flag usage detected - consider implementing flag preloading';
        }
        
        // Check for unused flags (if we have filesystem access)
        if (count(self::$flagUsageStats) < 50) {
            $recommendations[] = 'Many flags unused - consider cleanup or optimization';
        }
        
        return $recommendations;
    }
    
    /**
     * Check if we're in debug mode
     */
    private function isDebugMode(): bool {
        return defined('DEBUG_MODE') && DEBUG_MODE && (
            (isset($_SERVER['SERVER_ADDR']) && 
             ($_SERVER['SERVER_ADDR'] === '127.0.0.1' || $_SERVER['SERVER_ADDR'] === '::1')) ||
            (isset($_SERVER['HTTP_HOST']) && 
             (str_contains($_SERVER['HTTP_HOST'], 'localhost') || str_contains($_SERVER['HTTP_HOST'], '127.0.0.1')))
        );
    }
    
    /**
     * Reset monitoring statistics (useful for testing)
     */
    public function resetMonitoringStats() {
        self::$flagUsageStats = [];
        self::$missingFlagLogs = [];
    }
    
    /**
     * Validate flag system integrity
     * 
     * @param string $flagsDirectory Path to flags directory
     * @return array Validation results
     */
    public function validateFlagSystem($flagsDirectory = null) {
        if ($flagsDirectory === null) {
            $flagsDirectory = $_SERVER['DOCUMENT_ROOT'] . '/assets/flags/';
        }
        
        $results = [
            'valid' => true,
            'total_languages' => 0,
            'available_flags' => 0,
            'missing_flags' => [],
            'formats' => [],
            'errors' => []
        ];
        
        try {
            // Check if flags directory exists
            if (!is_dir($flagsDirectory)) {
                $results['valid'] = false;
                $results['errors'][] = 'Flags directory does not exist: ' . $flagsDirectory;
                return $results;
            }
            
            // Get all unique flag codes from language mapping
            $allFlagCodes = array_unique(array_values(self::$languageToFlag));
            $results['total_languages'] = count($allFlagCodes);
            
            // Check each flag code
            foreach ($allFlagCodes as $flagCode) {
                $found = false;
                $formats = ['.webp', '.png', '.jpg', '.gif'];
                
                foreach ($formats as $format) {
                    $flagPath = $flagsDirectory . $flagCode . $format;
                    if (file_exists($flagPath)) {
                        $found = true;
                        $results['formats'][$format] = ($results['formats'][$format] ?? 0) + 1;
                        break;
                    }
                }
                
                if ($found) {
                    $results['available_flags']++;
                } else {
                    $results['missing_flags'][] = $flagCode;
                    $results['valid'] = false;
                }
            }
            
            // Check for UN flag (critical for fallback)
            $unFlagExists = false;
            foreach (['.webp', '.png', '.jpg', '.gif'] as $format) {
                if (file_exists($flagsDirectory . 'un' . $format)) {
                    $unFlagExists = true;
                    break;
                }
            }
            
            if (!$unFlagExists) {
                $results['valid'] = false;
                $results['errors'][] = 'Critical: UN flag not found - fallback will not work';
            }
            
        } catch(Exception $e) {
            error_log('Exception in LanguageDetector.php: ' . $e->getMessage());
            $results['valid'] = false;
            $results['errors'][] = 'Validation error: ' . $e->getMessage();
        }
        
        return $results;
    }
}
