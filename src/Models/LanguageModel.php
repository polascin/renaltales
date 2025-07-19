<?php

declare(strict_types=1);

namespace RenalTales\Models;

/**
 * Language Model
 *
 * Handles dynamic language loading, support checks, translation lookup,
 * and user language preference for a multilingual web application.
 *
 * @author Ľubomír Polaščín
 * @package RenalTales
 * @version 2025.v3.1.dev
 */

// File: src/Models/LanguageModel.php


class LanguageModel
{
    /**
     * Get the number of supported languages
     *
     * @return int
     */
    public function getNumberOfSupportedLanguages(): int
    {
        return count($this->getSupportedLanguages());
    }
    /**
     * @var string Path to language files
     */
    private string $languagePath;


    /**
     * @var string Current language code
     */
    private string $currentLanguage;

    /**
     * @var array<string, string> Loaded translations for current language
     */
    private array $translations = [];

    /**
     * LanguageModel constructor.
     * Sets current language and loads translations.
     *
     * @param string|null $languagePath
     * @param string|null $defaultLanguage
     */
    public function __construct(?string $languagePath = null, ?string $defaultLanguage = null)
    {
        // Use APP_ROOT constant if available, otherwise calculate from current directory
        $appRoot = defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 2);
        $this->languagePath = $languagePath ?? $appRoot . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR;

        // Determine default language from constant or fallback to 'en'
        $defaultLang = $defaultLanguage ?? (defined('DEFAULT_LANGUAGE') ? DEFAULT_LANGUAGE : 'en');
        $this->currentLanguage = $this->detectLanguage($defaultLang);
        $this->loadTranslations($this->currentLanguage);
    }

    /**
     * Detect user's preferred language (session > cookie > browser > default)
     *
     * @param string $defaultLanguage
     * @return string
     */
    public function detectLanguage(string $defaultLanguage = 'en'): string
    {
        // Session
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['language'])) {
            $sessionLang = $_SESSION['language'];
            if ($this->isSupported($sessionLang)) {
                return $sessionLang;
            }
        }
        // Cookie
        if (isset($_COOKIE['language'])) {
            $cookieLang = $_COOKIE['language'];
            if ($this->isSupported($cookieLang)) {
                return $cookieLang;
            }
        }
        // Browser Accept-Language
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            foreach ($languages as $lang) {
                $lang = substr(trim(explode(';', $lang)[0]), 0, 2);
                if ($this->isSupported($lang)) {
                    return $lang;
                }
            }
        }
        // Fallback
        return $defaultLanguage;
    }

    /**
     * Get the current language code
     */
    public function getCurrentLanguage(): string
    {
        return $this->currentLanguage;
    }

    /**
     * Set the current language and persist to session/cookie
     *
     * @param string $language
     * @return bool
     */
    public function setLanguage(string $language): bool
    {
        if (!$this->isSupported($language)) {
            return false;
        }
        $this->currentLanguage = $language;
        $this->loadTranslations($language);
        // Session
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['language'] = $language;
        }
        // Cookie (30 days)
        setcookie('language', $language, time() + (30 * 24 * 60 * 60), '/');
        return true;
    }

    /**
     * Get all supported language codes
     *
     * @return array<string>
     */
    public function getSupportedLanguages(): array
    {
        // Sorted to match the order of keys in languageToCountryCode
        return [
          // European languages
          'be',
          'bg',
          'ca',
          'cs',
          'cy',
          'da',
          'de',
          'el',
          'en',
          'en-gb',
          'eo',
          'es',
          'et',
          'eu',
          'fi',
          'fo',
          'fr',
          'ga',
          'gd',
          'gl',
          'hr',
          'hu',
          'is',
          'it',
          'lb',
          'lt',
          'lv',
          'mk',
          'mt',
          'nl',
          'no',
          'pl',
          'pt',
          'rm',
          'ro',
          'ru',
          'se',
          'sk',
          'sl',
          'sq',
          'sr',
          'sv',
          'uk',
          // Asian languages
          'am',
          'ar',
          'as',
          'bn',
          'bo',
          'dv',
          'fa',
          'gu',
          'he',
          'hi',
          'hy',
          'ja',
          'jv',
          'ka',
          'kk',
          'km',
          'kn',
          'ko',
          'lo',
          'ml',
          'mn',
          'mr',
          'my',
          'ne',
          'or',
          'pa',
          'ps',
          'sa',
          'sd',
          'si',
          'ta',
          'te',
          'th',
          'ti',
          'tk',
          'ug',
          'ur',
          'uz',
          'vi',
          'wuu',
          'yue',
          'zh',
          'zh-cn',
          'zh-tw',
          // American languages
          'ay',
          'bho',
          'gn',
          'ht',
          'qu',
          'war',
          'en-us',
          'en-ca',
          'es',
          'pt-br',
          // African languages
          'af',
          'bm',
          'ff',
          'ha',
          'ig',
          'kg',
          'lg',
          'ln',
          'nd',
          'nr',
          'nso',
          'ny',
          'om',
          'rn',
          'rw',
          'sn',
          'so',
          'ss',
          'st',
          'sw',
          'tn',
          'ts',
          've',
          'xh',
          'yo',
          'zu',
          // Australia, Oceania, and New Zealand languages
          'en-au',
          'en-nz',
          'fj',
          'mt',
          'su',
          // Other languages
          'az',
          'bcl',
          'bh',
          'ceb',
          'hil',
          'ilo',
          'kl',
          'ky',
          'la',
          'lua',
          'mai',
          'mg',
          'ms',
          'pam',
        ];
    }

    /**
     * Check if a language code is supported
     *
     * @param string $language
     * @return bool
     */
    public function isSupported(string $language): bool
    {
        return in_array($language, $this->getSupportedLanguages(), true);
    }

    /**
     * Get a translated string by key, with optional parameters and fallback
     *
     * @param string $key
     * @param array<string, string|int|float> $parameters
     * @param string $fallback
     * @return string
     */
    public function getText(string $key, array $parameters = [], string $fallback = ''): string
    {
        $text = $this->translations[$key] ?? $fallback ?: $key;
        foreach ($parameters as $param => $value) {
            $text = str_replace('{' . $param . '}', (string)$value, $text);
        }
        return $text;
    }

    /**
     * Get all translations for the current language
     *
     * @return array<string, string>
     */
    public function getAllTexts(): array
    {
        return $this->translations;
    }


    /**
     * Load translations for a specific language
     *
     * @param string $language
     */
    private function loadTranslations(string $language): void
    {
        $filePath = $this->languagePath . $language . '.php';
        if (file_exists($filePath)) {
            $translations = include $filePath;
            $this->translations = is_array($translations) ? $translations : [];
        } else {
            // Fallback to English if available
            $defaultPath = $this->languagePath . 'en.php';
            if (file_exists($defaultPath)) {
                $translations = include $defaultPath;
                $this->translations = is_array($translations) ? $translations : [];
            } else {
                $this->translations = [];
            }
        }
    }
    /**
     * Get the native name of a language by code.
     *
     * @param string $language
     * @return string
     */
    public function getLanguageName(string $language): string
    {
        return self::getNativeLanguageName($language);
    }

    /**
     * Get the flag code (country code) for a language.
     *
     * @param string $language
     * @return string
     */
    public function getFlagCode(string $language): string
    {
        return self::languageToCountryCode($language);
    }

    /**
     * Convert a language code to a country code for flag display.
     * Handles common mappings and fallbacks.
     *
     * @param string $language
     * @return string Country code (ISO 3166-1 alpha-2, lowercase)
     */
    public static function languageToCountryCode(string $language): string
    {
        // Custom mappings for languages that don't match country codes directly

        $map = [
          // European languages
          'be' => 'by',
          'bg' => 'bg',
          'ca' => 'es',
          'cs' => 'cz',
          'cy' => 'gb',
          'da' => 'dk',
          'de' => 'de',
          'el' => 'gr',
          'en' => 'gb',
          'en-gb' => 'gb',
          'eo' => 'eu',
          'es' => 'es',
          'et' => 'ee',
          'eu' => 'es',
          'fi' => 'fi',
          'fo' => 'fo',
          'fr' => 'fr',
          'ga' => 'ie',
          'gd' => 'gb',
          'gl' => 'es',
          'hr' => 'hr',
          'hu' => 'hu',
          'is' => 'is',
          'it' => 'it',
          'lb' => 'lu',
          'lt' => 'lt',
          'lv' => 'lv',
          'mk' => 'mk',
          'mt' => 'mt',
          'nl' => 'nl',
          'no' => 'no',
          'pl' => 'pl',
          'pt' => 'pt',
          'rm' => 'ch',
          'ro' => 'ro',
          'ru' => 'ru',
          'se' => 'no',
          'sk' => 'sk',
          'sl' => 'si',
          'sq' => 'al',
          'sr' => 'rs',
          'sv' => 'se',
          'uk' => 'ua',

          // Asian languages
          'am' => 'et',
          'ar' => 'sa',
          'as' => 'in',
          'bn' => 'bd',
          'bo' => 'cn',
          'dv' => 'mv',
          'fa' => 'ir',
          'gu' => 'in',
          'he' => 'il',
          'hi' => 'in',
          'hy' => 'am',
          'ja' => 'jp',
          'jv' => 'id',
          'ka' => 'ge',
          'kk' => 'kz',
          'km' => 'kh',
          'kn' => 'in',
          'ko' => 'kr',
          'lo' => 'la',
          'ml' => 'in',
          'mn' => 'mn',
          'mr' => 'in',
          'my' => 'mm',
          'ne' => 'np',
          'or' => 'in',
          'pa' => 'in',
          'ps' => 'af',
          'sa' => 'in',
          'sd' => 'pk',
          'si' => 'lk',
          'ta' => 'in',
          'te' => 'in',
          'th' => 'th',
          'ti' => 'er',
          'tk' => 'tm',
          'ug' => 'cn',
          'ur' => 'pk',
          'uz' => 'uz',
          'vi' => 'vn',
          'wuu' => 'cn',
          'yue' => 'cn',
          'zh' => 'cn',
          'zh-cn' => 'cn',
          'zh-tw' => 'tw',

          // American languages
          'ay' => 'bo',
          'bho' => 'in',
          'gn' => 'py',
          'ht' => 'ht',
          'qu' => 'pe',
          'war' => 'ph',
          'en-us' => 'us',
          'en-ca' => 'ca',
          'es' => 'es',
          'pt-br' => 'br',

          // African languages
          'af' => 'za',
          'bm' => 'ml',
          'ff' => 'sn',
          'ha' => 'ng',
          'ig' => 'ng',
          'kg' => 'cd',
          'lg' => 'ug',
          'ln' => 'cd',
          'nd' => 'zw',
          'nr' => 'za',
          'nso' => 'za',
          'ny' => 'mw',
          'om' => 'et',
          'rn' => 'bi',
          'rw' => 'rw',
          'sn' => 'zw',
          'so' => 'so',
          'ss' => 'sz',
          'st' => 'ls',
          'sw' => 'tz',
          'tn' => 'za',
          'ts' => 'za',
          've' => 'za',
          'xh' => 'za',
          'yo' => 'ng',
          'zu' => 'za',

          // Australia, Oceania, and New Zealand languages
          'en-au' => 'au',
          'en-nz' => 'nz',
          'fj' => 'fj',
          'mt' => 'mt',
          'su' => 'id',

          // Other languages
          'az' => 'az',
          'bcl' => 'ph',
          'bh' => 'in',
          'ceb' => 'ph',
          'hil' => 'ph',
          'ilo' => 'ph',
          'kl' => 'gl',
          'ky' => 'kg',
          'la' => 'va',
          'lua' => 'cd',
          'mai' => 'in',
          'mg' => 'mg',
          'ms' => 'my',
          'pam' => 'ph',
        ];

        $lang = strtolower($language);
        if (isset($map[$lang])) {
            return $map[$lang];
        }
        // If regional (e.g., en-us), use the part after the dash
        if (strpos($lang, '-') !== false) {
            $parts = explode('-', $lang);
            return strtolower($parts[1]);
        }
        // Fallback: use the language code itself
        return $lang;
    }

    /**
     * Get the native name of a language by code.
     *
     * @param string $code
     * @return string
     */
    public static function getNativeLanguageName(string $code): string
    {
        $map = [
          // European languages
          'be' => 'Беларуская',
          'bg' => 'Български',
          'ca' => 'Català',
          'cs' => 'Čeština',
          'cy' => 'Cymraeg',
          'da' => 'Dansk',
          'de' => 'Deutsch',
          'el' => 'Ελληνικά',
          'en' => 'English',
          'en-gb' => 'English (UK)',
          'eo' => 'Esperanto',
          'es' => 'Español',
          'et' => 'Eesti',
          'eu' => 'Euskara',
          'fi' => 'Suomi',
          'fo' => 'Føroyskt',
          'fr' => 'Français',
          'ga' => 'Gaeilge',
          'gd' => 'Gàidhlig',
          'gl' => 'Galego',
          'hr' => 'Hrvatski',
          'hu' => 'Magyar',
          'is' => 'Íslenska',
          'it' => 'Italiano',
          'lb' => 'Lëtzebuergesch',
          'lt' => 'Lietuvių',
          'lv' => 'Latviešu',
          'mk' => 'Македонски',
          'mt' => 'Malti',
          'nl' => 'Nederlands',
          'no' => 'Norsk',
          'pl' => 'Polski',
          'pt' => 'Português',
          'rm' => 'Rumantsch',
          'ro' => 'Română',
          'ru' => 'Русский',
          'se' => 'Sámegiella',
          'sk' => 'Slovenčina',
          'sl' => 'Slovenščina',
          'sq' => 'Shqip',
          'sr' => 'Српски',
          'sv' => 'Svenska',
          'uk' => 'Українська',

          // Asian languages
          'am' => 'አማርኛ',
          'ar' => 'العربية',
          'as' => 'অসমীয়া',
          'bn' => 'বাংলা',
          'bo' => 'བོད་སྐད་',
          'dv' => 'ދިވެހި',
          'fa' => 'فارسی',
          'gu' => 'ગુજરાતી',
          'he' => 'עברית',
          'hi' => 'हिन्दी',
          'hy' => 'Հայերեն',
          'ja' => '日本語',
          'jv' => 'Basa Jawa',
          'ka' => 'ქართული',
          'kk' => 'Қазақша',
          'km' => 'ភាសាខ្មែរ',
          'kn' => 'ಕನ್ನಡ',
          'ko' => '한국어',
          'lo' => 'ລາວ',
          'ml' => 'മലയാളം',
          'mn' => 'Монгол',
          'mr' => 'मराठी',
          'my' => 'မြန်မာဘာသာ',
          'ne' => 'नेपाली',
          'or' => 'ଓଡ଼ିଆ',
          'pa' => 'ਪੰਜਾਬੀ',
          'ps' => 'پښتو',
          'sa' => 'संस्कृतम्',
          'sd' => 'سنڌي',
          'si' => 'සිංහල',
          'ta' => 'தமிழ்',
          'te' => 'తెలుగు',
          'th' => 'ไทย',
          'ti' => 'ትግርኛ',
          'tk' => 'Türkmen',
          'ug' => 'ئۇيغۇرچە',
          'ur' => 'اردو',
          'uz' => 'Oʻzbek',
          'vi' => 'Tiếng Việt',
          'wuu' => '吴语',
          'yue' => '粵語',
          'zh' => '中文',
          'zh-cn' => '简体中文',
          'zh-tw' => '繁體中文',

          // American languages
          'ay' => 'Aymar aru',
          'bho' => 'भोजपुरी',
          'gn' => 'Avañeʼẽ',
          'ht' => 'Kreyòl ayisyen',
          'qu' => 'Runa Simi',
          'war' => 'Winaray',
          'en-us' => 'English (US)',
          'en-ca' => 'English (Canada)',
          'pt-br' => 'Português (Brasil)',

          // African languages
          'af' => 'Afrikaans',
          'bm' => 'Bamanankan',
          'ff' => 'Fula',
          'ha' => 'Hausa',
          'ig' => 'Igbo',
          'kg' => 'Kikongo',
          'lg' => 'Luganda',
          'ln' => 'Lingála',
          'nd' => 'isiNdebele',
          'nr' => 'isiNdebele',
          'nso' => 'Sesotho sa Leboa',
          'ny' => 'Chichewa',
          'om' => 'Oromoo',
          'rn' => 'Kirundi',
          'rw' => 'Kinyarwanda',
          'sn' => 'chiShona',
          'so' => 'Soomaали',
          'ss' => 'SiSwati',
          'st' => 'Sesotho',
          'sw' => 'Kiswahili',
          'tn' => 'Setswana',
          'ts' => 'Xitsonga',
          've' => 'Tshivenda',
          'xh' => 'isiXhosa',
          'yo' => 'Yorùbá',
          'zu' => 'isiZulu',

          // Australia, Oceania, and New Zealand languages
          'en-au' => 'English (Australia)',
          'en-nz' => 'English (NZ)',
          'fj' => 'Fijian',
          'mt' => 'Malti',
          'su' => 'Basa Sunda',

          // Other languages
          'az' => 'Azərbaycanca',
          'bcl' => 'Bikol',
          'bh' => 'भोजपुरी',
          'ceb' => 'Cebuano',
          'hil' => 'Hiligaynon',
          'ilo' => 'Ilokano',
          'kl' => 'Kalaallisut',
          'ky' => 'Кыргызча',
          'la' => 'Latina',
          'lua' => 'Tshiluba',
          'mai' => 'मैथिली',
          'mg' => 'Malagasy',
          'ms' => 'Bahasa Melayu',
          'pam' => 'Kapampangan',
        ];
        $code = strtolower($code);
        return $map[$code] ?? $code;
    }

    /**
     * Get the English name of a language by code, sorted by supported languages if available.
     *
     * @param string $code
     * @return string
     */
    public static function getEnglishLanguageName(string $code): string
    {
        $map = [
          // European languages
          'be' => 'Belarusian',
          'bg' => 'Bulgarian',
          'ca' => 'Catalan',
          'cs' => 'Czech',
          'cy' => 'Welsh',
          'da' => 'Danish',
          'de' => 'German',
          'el' => 'Greek',
          'en' => 'English',
          'en-gb' => 'English (UK)',
          'eo' => 'Esperanto',
          'es' => 'Spanish',
          'et' => 'Estonian',
          'eu' => 'Basque',
          'fi' => 'Finnish',
          'fo' => 'Faroese',
          'fr' => 'French',
          'ga' => 'Irish',
          'gd' => 'Scottish Gaelic',
          'gl' => 'Galician',
          'hr' => 'Croatian',
          'hu' => 'Hungarian',
          'is' => 'Icelandic',
          'it' => 'Italian',
          'lb' => 'Luxembourgish',
          'lt' => 'Lithuanian',
          'lv' => 'Latvian',
          'mk' => 'Macedonian',
          'mt' => 'Maltese',
          'nl' => 'Dutch',
          'no' => 'Norwegian',
          'pl' => 'Polish',
          'pt' => 'Portuguese',
          'rm' => 'Romansh',
          'ro' => 'Romanian',
          'ru' => 'Russian',
          'se' => 'Northern Sami',
          'sk' => 'Slovak',
          'sl' => 'Slovenian',
          'sq' => 'Albanian',
          'sr' => 'Serbian',
          'sv' => 'Swedish',
          'uk' => 'Ukrainian',

          // Asian languages
          'am' => 'Amharic',
          'ar' => 'Arabic',
          'as' => 'Assamese',
          'bn' => 'Bengali',
          'bo' => 'Tibetan',
          'dv' => 'Divehi',
          'fa' => 'Persian',
          'gu' => 'Gujarati',
          'he' => 'Hebrew',
          'hi' => 'Hindi',
          'hy' => 'Armenian',
          'ja' => 'Japanese',
          'jv' => 'Javanese',
          'ka' => 'Georgian',
          'kk' => 'Kazakh',
          'km' => 'Khmer',
          'kn' => 'Kannada',
          'ko' => 'Korean',
          'lo' => 'Lao',
          'ml' => 'Malayalam',
          'mn' => 'Mongolian',
          'mr' => 'Marathi',
          'my' => 'Burmese',
          'ne' => 'Nepali',
          'or' => 'Oriya',
          'pa' => 'Punjabi',
          'ps' => 'Pashto',
          'sa' => 'Sanskrit',
          'sd' => 'Sindhi',
          'si' => 'Sinhala',
          'ta' => 'Tamil',
          'te' => 'Telugu',
          'th' => 'Thai',
          'ti' => 'Tigrinya',
          'tk' => 'Turkmen',
          'ug' => 'Uyghur',
          'ur' => 'Urdu',
          'uz' => 'Uzbek',
          'vi' => 'Vietnamese',
          'wuu' => 'Wu Chinese',
          'yue' => 'Cantonese',
          'zh' => 'Chinese',
          'zh-cn' => 'Chinese (Simplified)',
          'zh-tw' => 'Chinese (Traditional)',

          // American languages
          'ay' => 'Aymara',
          'bho' => 'Bhojpuri',
          'gn' => 'Guarani',
          'ht' => 'Haitian',
          'qu' => 'Quechua',
          'war' => 'Waray',
          'en-us' => 'English (US)',
          'en-ca' => 'English (Canada)',
          'pt-br' => 'Portuguese (Brazil)',

          // African languages
          'af' => 'Afrikaans',
          'bm' => 'Bambara',
          'ff' => 'Fulah',
          'ha' => 'Hausa',
          'ig' => 'Igbo',
          'kg' => 'Kongo',
          'lg' => 'Ganda',
          'ln' => 'Lingala',
          'nd' => 'North Ndebele',
          'nr' => 'South Ndebele',
          'nso' => 'Northern Sotho',
          'ny' => 'Nyanja',
          'om' => 'Oromo',
          'rn' => 'Rundi',
          'rw' => 'Kinyarwanda',
          'sn' => 'Shona',
          'so' => 'Somali',
          'ss' => 'Swati',
          'st' => 'Southern Sotho',
          'sw' => 'Swahili',
          'tn' => 'Tswana',
          'ts' => 'Tsonga',
          've' => 'Venda',
          'xh' => 'Xhosa',
          'yo' => 'Yoruba',
          'zu' => 'Zulu',

          // Australia, Oceania, and New Zealand languages
          'en-au' => 'English (Australia)',
          'en-nz' => 'English (NZ)',
          'fj' => 'Fijian',
          'mt' => 'Maltese',
          'su' => 'Sundanese',

          // Other languages
          'az' => 'Azerbaijani',
          'bcl' => 'Bikol',
          'bh' => 'Bihari',
          'ceb' => 'Cebuano',
          'hil' => 'Hiligaynon',
          'ilo' => 'Iloko',
          'kl' => 'Kalaallisut',
          'ky' => 'Kyrgyz',
          'la' => 'Latin',
          'lua' => 'Luba-Lulua',
          'mai' => 'Maithili',
          'mg' => 'Malagasy',
          'ms' => 'Malay',
          'pam' => 'Pampanga',
        ];
        $code = strtolower($code);
        return $map[$code] ?? $code;
    }
}
