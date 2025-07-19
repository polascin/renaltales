<?php

/**
 * Script to populate languages table with language data
 * Based on available language files and LanguageModel mappings
 *
 * @author Ľubomír Polaščín
 * @version 2025.v3.1.dev
 */

declare(strict_types=1);

// Define APP_ROOT constant
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

// Include required files
require_once APP_ROOT . '/src/Models/LanguageModel.php';

use RenalTales\Models\LanguageModel;

// Database configuration
$config = [
    'host' => 'mariadb114.r6.websupport.sk',
    'port' => 3306,
    'database' => 'SvwfeoXW',
    'username' => 'by80b9pH',
    'password' => 'WsVZOl#;D07ju~0@_dF@',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci'
];

try {
    // Create database connection
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    echo "✅ Database connection successful\n";

    // Get supported languages from LanguageModel
    $languageModel = new LanguageModel();
    $supportedLanguages = $languageModel->getSupportedLanguages();

    // Deduplicate the array to avoid unique constraint violations
    $supportedLanguages = array_unique($supportedLanguages);

    echo "📊 Found " . count($supportedLanguages) . " supported languages (after deduplication)\n";

    // Path to language files
    $languageDir = APP_ROOT . '/resources/lang/';
    $availableLanguageFiles = [];

    // Scan for available language files
    if (is_dir($languageDir)) {
        $files = scandir($languageDir);
        foreach ($files as $file) {
            if (preg_match('/^([a-z]{2}(-[a-z]{2})?)\.php$/', $file, $matches)) {
                $availableLanguageFiles[] = $matches[1];
            }
        }
    }

    echo "📁 Found " . count($availableLanguageFiles) . " language files\n";

    // Clear existing data
    $pdo->exec("DELETE FROM languages");
    echo "🗑️  Cleared existing language data\n";

    // Prepare insert statement
    $insertStmt = $pdo->prepare("
        INSERT INTO languages (
            code, 
            name, 
            nativename, 
            active, 
            isdefault, 
            direction, 
            region, 
            sortorder, 
            createdat, 
            updatedat
        ) VALUES (
            :code, 
            :name, 
            :nativename, 
            :active, 
            :isdefault, 
            :direction, 
            :region, 
            :sortorder, 
            :createdat, 
            :updatedat
        )
    ");

    $insertedCount = 0;
    $currentTime = date('Y-m-d H:i:s');

    // Define some popular languages to set as active
    $popularLanguages = ['en', 'sk', 'cs', 'de', 'fr', 'es', 'ru', 'zh', 'ja', 'ar'];

    // Define RTL languages
    $rtlLanguages = ['ar', 'he', 'fa', 'ur', 'ps', 'sd', 'dv', 'ug'];

    // Define regions for sorting
    $regionMap = [
        // European languages
        'be' => 'Europe', 'bg' => 'Europe', 'ca' => 'Europe', 'cs' => 'Europe', 'cy' => 'Europe',
        'da' => 'Europe', 'de' => 'Europe', 'el' => 'Europe', 'en' => 'Europe', 'en-gb' => 'Europe',
        'eo' => 'Europe', 'es' => 'Europe', 'et' => 'Europe', 'eu' => 'Europe', 'fi' => 'Europe',
        'fo' => 'Europe', 'fr' => 'Europe', 'ga' => 'Europe', 'gd' => 'Europe', 'gl' => 'Europe',
        'hr' => 'Europe', 'hu' => 'Europe', 'is' => 'Europe', 'it' => 'Europe', 'lb' => 'Europe',
        'lt' => 'Europe', 'lv' => 'Europe', 'mk' => 'Europe', 'mt' => 'Europe', 'nl' => 'Europe',
        'no' => 'Europe', 'pl' => 'Europe', 'pt' => 'Europe', 'rm' => 'Europe', 'ro' => 'Europe',
        'ru' => 'Europe', 'se' => 'Europe', 'sk' => 'Europe', 'sl' => 'Europe', 'sq' => 'Europe',
        'sr' => 'Europe', 'sv' => 'Europe', 'uk' => 'Europe',

        // Asian languages
        'am' => 'Asia', 'ar' => 'Asia', 'as' => 'Asia', 'bn' => 'Asia', 'bo' => 'Asia', 'dv' => 'Asia',
        'fa' => 'Asia', 'gu' => 'Asia', 'he' => 'Asia', 'hi' => 'Asia', 'hy' => 'Asia', 'ja' => 'Asia',
        'jv' => 'Asia', 'ka' => 'Asia', 'kk' => 'Asia', 'km' => 'Asia', 'kn' => 'Asia', 'ko' => 'Asia',
        'lo' => 'Asia', 'ml' => 'Asia', 'mn' => 'Asia', 'mr' => 'Asia', 'my' => 'Asia', 'ne' => 'Asia',
        'or' => 'Asia', 'pa' => 'Asia', 'ps' => 'Asia', 'sa' => 'Asia', 'sd' => 'Asia', 'si' => 'Asia',
        'ta' => 'Asia', 'te' => 'Asia', 'th' => 'Asia', 'ti' => 'Asia', 'tk' => 'Asia', 'ug' => 'Asia',
        'ur' => 'Asia', 'uz' => 'Asia', 'vi' => 'Asia', 'wuu' => 'Asia', 'yue' => 'Asia', 'zh' => 'Asia',
        'zh-cn' => 'Asia', 'zh-tw' => 'Asia',

        // American languages
        'ay' => 'America', 'bho' => 'America', 'gn' => 'America', 'ht' => 'America', 'qu' => 'America',
        'war' => 'America', 'en-us' => 'America', 'en-ca' => 'America', 'pt-br' => 'America',

        // African languages
        'af' => 'Africa', 'bm' => 'Africa', 'ff' => 'Africa', 'ha' => 'Africa', 'ig' => 'Africa',
        'kg' => 'Africa', 'lg' => 'Africa', 'ln' => 'Africa', 'nd' => 'Africa', 'nr' => 'Africa',
        'nso' => 'Africa', 'ny' => 'Africa', 'om' => 'Africa', 'rn' => 'Africa', 'rw' => 'Africa',
        'sn' => 'Africa', 'so' => 'Africa', 'ss' => 'Africa', 'st' => 'Africa', 'sw' => 'Africa',
        'tn' => 'Africa', 'ts' => 'Africa', 've' => 'Africa', 'xh' => 'Africa', 'yo' => 'Africa',
        'zu' => 'Africa',

        // Oceania
        'en-au' => 'Oceania', 'en-nz' => 'Oceania', 'fj' => 'Oceania', 'su' => 'Oceania',

        // Other
        'az' => 'Other', 'bcl' => 'Other', 'bh' => 'Other', 'ceb' => 'Other', 'hil' => 'Other',
        'ilo' => 'Other', 'kl' => 'Other', 'ky' => 'Other', 'la' => 'Other', 'lua' => 'Other',
        'mai' => 'Other', 'mg' => 'Other', 'ms' => 'Other', 'pam' => 'Other',
    ];

    // Process each supported language
    foreach ($supportedLanguages as $index => $langCode) {
        $englishName = LanguageModel::getEnglishLanguageName($langCode);
        $nativeName = LanguageModel::getNativeLanguageName($langCode);

        // Check if language file exists
        $hasFile = in_array($langCode, $availableLanguageFiles);

        // Determine if language should be active
        $isActive = $hasFile && in_array($langCode, $popularLanguages);

        // Set default language (English)
        $isDefault = ($langCode === 'en');

        // Determine text direction
        $direction = in_array($langCode, $rtlLanguages) ? 'rtl' : 'ltr';

        // Get region
        $region = $regionMap[$langCode] ?? 'Other';

        // Sort order based on index in supported languages array
        $sortOrder = $index + 1;

        // Insert language record
        $insertStmt->execute([
            'code' => $langCode,
            'name' => $englishName,
            'nativename' => $nativeName,
            'active' => $isActive ? 1 : 0,
            'isdefault' => $isDefault ? 1 : 0,
            'direction' => $direction,
            'region' => $region,
            'sortorder' => $sortOrder,
            'createdat' => $currentTime,
            'updatedat' => $currentTime
        ]);

        $insertedCount++;

        // Show progress
        if ($insertedCount % 20 === 0) {
            echo "📝 Processed $insertedCount languages...\n";
        }
    }

    echo "✅ Successfully populated $insertedCount languages\n";

    // Show summary statistics
    $stats = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(active) as active,
            SUM(isdefault) as default_count,
            COUNT(DISTINCT region) as regions
        FROM languages
    ")->fetch();

    echo "\n📊 Summary Statistics:\n";
    echo "   Total languages: {$stats['total']}\n";
    echo "   Active languages: {$stats['active']}\n";
    echo "   Default languages: {$stats['default_count']}\n";
    echo "   Regions: {$stats['regions']}\n";

    // Show sample of inserted data
    echo "\n🔍 Sample of inserted data:\n";
    $sample = $pdo->query("
        SELECT code, name, nativename, active, isdefault, direction, region 
        FROM languages 
        WHERE active = 1 
        ORDER BY sortorder 
        LIMIT 10
    ");

    foreach ($sample as $row) {
        $status = $row['active'] ? '✅' : '❌';
        $default = $row['isdefault'] ? ' (DEFAULT)' : '';
        echo "   $status {$row['code']}: {$row['name']} ({$row['nativename']}) - {$row['region']} [{$row['direction']}]$default\n";
    }

    echo "\n🎉 Language population completed successfully!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . "\n";
    echo "📍 Line: " . $e->getLine() . "\n";
    exit(1);
}
