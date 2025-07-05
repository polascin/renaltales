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
     * Language to country flag mapping
     */
    private static $languageToFlag = [
        // European languages
        'en' => 'gb',  // English → Great Britain
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
        // NEW Asian languages
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
        'mai' => 'in',  // Maithili → India
        'mag' => 'in',  // Magahi → India
        'gom' => 'in',  // Konkani → India
        'sat' => 'in',  // Santali → India
        'ks' => 'in',  // Kashmiri → India
        'sd' => 'pk',  // Sindhi → Pakistan
        'bal' => 'pk',  // Balochi → Pakistan
        'dv' => 'mv',  // Dhivehi → Maldives
        'ceb' => 'ph',  // Cebuano → Philippines
        'ilo' => 'ph',  // Ilocano → Philippines
        'war' => 'ph',  // Waray → Philippines
        'bcl' => 'ph',  // Bikol → Philippines
        'pam' => 'ph',  // Kapampangan → Philippines
        'tsg' => 'ph',  // Tausug → Philippines
        'min' => 'id',  // Minangkabau → Indonesia
        'mad' => 'id',  // Madurese → Indonesia
        'gan' => 'cn',  // Gan Chinese → China
        'hak' => 'cn',  // Hakka Chinese → China
        'nan' => 'cn',  // Min Nan Chinese → China
        'wuu' => 'cn',  // Wu dialects → China
        'tk' => 'tm',  // Turkmen → Turkmenistan
        'tt' => 'ru',  // Tatar → Russia
        'ba' => 'ru',  // Bashkir → Russia
        'cv' => 'ru',  // Chuvash → Russia
        'sah' => 'ru',  // Sakha/Yakut → Russia
        'tyv' => 'ru',  // Tuvan → Russia
        'alt' => 'ru',  // Altai → Russia
        'dz' => 'bt',  // Dzongkha → Bhutan
        'to' => 'to',  // Tongan → Tonga
        'sm' => 'ws',  // Samoan → Samoa
        'fj' => 'fj',  // Fijian → Fiji
        'bi' => 'vu',  // Bislama → Vanuatu
        'pau' => 'pw',  // Palauan → Palau
        
        // Additional Asian languages (missing from previous update)
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
        'bh' => 'in',  // Bihari → India
        'sa' => 'in',  // Sanskrit → India
        
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
        
        // Additional Philippine languages
        'hil' => 'ph', // Hiligaynon → Philippines
        
        // American languages
        'qu' => 'pe',  // Quechua → Peru
        'gn' => 'py',  // Guaraní → Paraguay
        'ay' => 'bo',  // Aymara → Bolivia
        'ht' => 'ht',  // Haitian Creole → Haiti
    ];
    
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
        // NEW European languages
        'ca' => ['ca', 'ca-es', 'ca-ad'], // Catalan
        'eu' => ['eu', 'eu-es'], // Basque
        'cy' => ['cy', 'cy-gb'], // Welsh
        'ga' => ['ga', 'ga-ie'], // Irish
        'gd' => ['gd', 'gd-gb'], // Scottish Gaelic
        'mt' => ['mt', 'mt-mt'], // Maltese
        'gl' => ['gl', 'gl-es'], // Galician
        'be' => ['be', 'be-by'], // Belarusian
        'lb' => ['lb', 'lb-lu'], // Luxembourgish
        'rm' => ['rm', 'rm-ch'], // Romansh
        'fo' => ['fo', 'fo-fo'], // Faroese
        'kl' => ['kl', 'kl-gl'], // Greenlandic
        'se' => ['se', 'se-no', 'se-se', 'se-fi'], // Northern Sami
        
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
        'bn' => ['bn', 'bn-bd'], // Bengali
        'ta' => ['ta', 'ta-in'], // Tamil
        // NEW Asian languages
        'id' => ['id', 'id-id'], // Indonesian
        'ms' => ['ms', 'ms-my'], // Malay
        'tl' => ['tl', 'tl-ph'], // Tagalog/Filipino
        'mr' => ['mr', 'mr-in'], // Marathi
        'jv' => ['jv', 'jv-id'], // Javanese
        'yue' => ['yue', 'yue-hk'], // Cantonese
        'wuu' => ['wuu', 'wuu-cn'], // Wu Chinese
        'bho' => ['bho', 'bho-in'], // Bhojpuri
        'ps' => ['ps', 'ps-af'], // Pashto
        'su' => ['su', 'su-id'], // Sundanese
        'or' => ['or', 'or-in'], // Odia
        'as' => ['as', 'as-in'], // Assamese
        'gu' => ['gu', 'gu-in'], // Gujarati
        'kn' => ['kn', 'kn-in'], // Kannada
        'te' => ['te', 'te-in'], // Telugu
        'ml' => ['ml', 'ml-in'], // Malayalam
        'pa' => ['pa', 'pa-in'], // Punjabi
        'ne' => ['ne', 'ne-np'], // Nepali
        'my' => ['my', 'my-mm'], // Burmese
        'km' => ['km', 'km-kh'], // Khmer
        'lo' => ['lo', 'lo-la'], // Lao
        'ka' => ['ka', 'ka-ge'], // Georgian
        'hy' => ['hy', 'hy-am'], // Armenian
        'az' => ['az', 'az-az'], // Azerbaijani
        'kk' => ['kk', 'kk-kz'], // Kazakh
        'ky' => ['ky', 'ky-kg'], // Kyrgyz
        'uz' => ['uz', 'uz-uz'], // Uzbek
        'tk' => ['tk', 'tk-tm'], // Turkmen
        'tg' => ['tg', 'tg-tj'], // Tajik
        'mn' => ['mn', 'mn-mn'], // Mongolian
        'si' => ['si', 'si-lk'], // Sinhala
        'dv' => ['dv', 'dv-mv'], // Divehi
        'bo' => ['bo', 'bo-cn'], // Tibetan
        'ug' => ['ug', 'ug-cn'], // Uyghur
        'sd' => ['sd', 'sd-pk'], // Sindhi
        'mai' => ['mai', 'mai-in'], // Maithili
        'bh' => ['bh', 'bh-in'], // Bihari
        'sa' => ['sa', 'sa-in'], // Sanskrit
        
        // African languages
        'sw' => ['sw', 'sw-ke', 'sw-tz'], // Swahili
        'af' => ['af', 'af-za'], // Afrikaans
        'am' => ['am', 'am-et'], // Amharic
        'ha' => ['ha', 'ha-ng'], // Hausa
        'yo' => ['yo', 'yo-ng'], // Yoruba
        'ig' => ['ig', 'ig-ng'], // Igbo
        'zu' => ['zu', 'zu-za'], // Zulu
        'xh' => ['xh', 'xh-za'], // Xhosa
        'tn' => ['tn', 'tn-za'], // Tswana
        'st' => ['st', 'st-za'], // Sesotho
        'ss' => ['ss', 'ss-za'], // Swati
        'nr' => ['nr', 'nr-za'], // Ndebele
        'nso' => ['nso', 'nso-za'], // Northern Sotho
        've' => ['ve', 've-za'], // Venda
        'ts' => ['ts', 'ts-za'], // Tsonga
        'ti' => ['ti', 'ti-er'], // Tigrinya
        'om' => ['om', 'om-et'], // Oromo
        'so' => ['so', 'so-so'], // Somali
        'rw' => ['rw', 'rw-rw'], // Kinyarwanda
        'rn' => ['rn', 'rn-bi'], // Kirundi
        'lg' => ['lg', 'lg-ug'], // Luganda
        'ny' => ['ny', 'ny-mw'], // Chichewa
        'sn' => ['sn', 'sn-zw'], // Shona
        'nd' => ['nd', 'nd-zw'], // Ndebele (Zimbabwe)
        'bm' => ['bm', 'bm-ml'], // Bambara
        'ff' => ['ff', 'ff-sn'], // Fulah
        'wo' => ['wo', 'wo-sn'], // Wolof
        'ln' => ['ln', 'ln-cd'], // Lingala
        'kg' => ['kg', 'kg-cd'], // Kikongo
        'lua' => ['lua', 'lua-cd'], // Luba-Lulua
        'sg' => ['sg', 'sg-cf'], // Sango
        'mg' => ['mg', 'mg-mg'], // Malagasy
        
        // Additional languages
        'ht' => ['ht', 'ht-ht'], // Haitian Creole
        'qu' => ['qu', 'qu-pe'], // Quechua
        'gn' => ['gn', 'gn-py'], // Guarani
        'ay' => ['ay', 'ay-bo'], // Aymara
        'ceb' => ['ceb', 'ceb-ph'], // Cebuano
        'hil' => ['hil', 'hil-ph'], // Hiligaynon
        'war' => ['war', 'war-ph'], // Waray
        'bcl' => ['bcl', 'bcl-ph'], // Bikol
        'pam' => ['pam', 'pam-ph'], // Kapampangan
        'ilo' => ['ilo', 'ilo-ph'], // Ilocano
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
     * Get flag code for a language
     * 
     * @param string $languageCode Language code
     * @return string Flag code (defaults to 'un' if not found)
     */
    public static function getFlagCode($languageCode) {
        return isset(self::$languageToFlag[$languageCode]) 
            ? self::$languageToFlag[$languageCode] 
            : 'un'; // Default to UN flag if not found
    }
    
    /**
     * Get complete flag path for a language
     *
     * @param string $languageCode Language code
     * @param string $basePath Base path for flags
     * @param string $extension File extension
     * @return string Complete path to the flag
     */
    public static function getFlagPath($languageCode, $basePath = 'assets/flags/', $extension = '.webp') {
        $flagCode = self::getFlagCode($languageCode);
        return $basePath . $flagCode . $extension;
    }
    
    /**
     * Get the best available flag path with format fallback
     * Tries WEBP first, then PNG, then JPG
     * 
     * @param string $languageCode Language code
     * @param string $basePath Base path for flags (relative to document root)
     * @param string $documentRoot Document root path for file existence checking
     * @return string Complete path to the best available flag format
     */
    public static function getBestFlagPath($languageCode, $basePath = 'assets/flags/', $documentRoot = null) {
        $flagCode = self::getFlagCode($languageCode);
        
        // Set default document root if not provided
        if ($documentRoot === null) {
            $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
        }
        
        // Try formats in order of preference: WEBP > PNG > JPG
        $formats = ['.webp', '.png', '.jpg'];
        
        foreach ($formats as $extension) {
            $flagPath = $basePath . $flagCode . $extension;
            $fullPath = rtrim($documentRoot, '/') . '/' . ltrim($flagPath, '/');
            
            if (file_exists($fullPath)) {
                return $flagPath;
            }
        }
        
        // If no flag found, return WEBP path (will fallback to UN flag or show as missing)
        return $basePath . $flagCode . '.webp';
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
