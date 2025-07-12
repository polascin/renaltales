<?php
/**
 * Important Languages Analysis
 * Identifies missing important world languages from the Renal Tales application
 */

define('LANGUAGE_PATH', 'resources/lang/');
require_once 'core/LanguageDetector.php';

echo "=== IMPORTANT LANGUAGES ANALYSIS ===\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

// Get list of currently supported languages
$detector = new LanguageDetector();
$supportedLanguages = $detector->getSupportedLanguages();

// Get actual language files
$langFiles = glob('resources/lang/*.php');
$actualLanguages = [];
foreach ($langFiles as $file) {
    $lang = basename($file, '.php');
    $actualLanguages[] = $lang;
}
sort($actualLanguages);

echo "Current language support:\n";
echo "- Language files found: " . count($actualLanguages) . "\n";
echo "- Detector reports: " . count($supportedLanguages) . " languages\n\n";

// Define important world languages by category
$importantLanguages = [
    'Top 10 Most Spoken Languages (Native + Second Language)' => [
        'en' => 'English (1.5B speakers)',
        'zh' => 'Chinese/Mandarin (1.1B speakers)', 
        'hi' => 'Hindi (602M speakers)',
        'es' => 'Spanish (559M speakers)',
        'fr' => 'French (280M speakers)',
        'ar' => 'Arabic (422M speakers)',
        'bn' => 'Bengali (268M speakers)',
        'ru' => 'Russian (258M speakers)',
        'pt' => 'Portuguese (263M speakers)',
        'id' => 'Indonesian (199M speakers)'
    ],
    
    'Major European Languages' => [
        'de' => 'German (134M speakers)',
        'it' => 'Italian (68M speakers)',
        'pl' => 'Polish (45M speakers)',
        'uk' => 'Ukrainian (37M speakers)',
        'nl' => 'Dutch (25M speakers)',
        'ro' => 'Romanian (24M speakers)',
        'el' => 'Greek (13M speakers)',
        'cs' => 'Czech (10.7M speakers)',
        'hu' => 'Hungarian (13M speakers)',
        'sv' => 'Swedish (10M speakers)',
        'sk' => 'Slovak (5.2M speakers)',
        'bg' => 'Bulgarian (9M speakers)',
        'hr' => 'Croatian (5.6M speakers)',
        'da' => 'Danish (6M speakers)',
        'fi' => 'Finnish (5.5M speakers)',
        'no' => 'Norwegian (5.3M speakers)',
        'lt' => 'Lithuanian (2.8M speakers)',
        'sl' => 'Slovenian (2.5M speakers)',
        'lv' => 'Latvian (1.75M speakers)',
        'et' => 'Estonian (1.1M speakers)'
    ],
    
    'Major Asian Languages' => [
        'ja' => 'Japanese (125M speakers)',
        'ko' => 'Korean (81M speakers)',
        'vi' => 'Vietnamese (85M speakers)',
        'th' => 'Thai (61M speakers)',
        'tr' => 'Turkish (84M speakers)',
        'fa' => 'Persian/Farsi (77M speakers)',
        'ur' => 'Urdu (68M speakers)',
        'ta' => 'Tamil (78M speakers)',
        'te' => 'Telugu (82M speakers)',
        'mr' => 'Marathi (83M speakers)',
        'gu' => 'Gujarati (60M speakers)',
        'kn' => 'Kannada (66M speakers)',
        'ml' => 'Malayalam (38M speakers)',
        'pa' => 'Punjabi (113M speakers)',
        'ne' => 'Nepali (16M speakers)',
        'si' => 'Sinhala (17M speakers)',
        'my' => 'Burmese (33M speakers)',
        'km' => 'Khmer (16M speakers)',
        'lo' => 'Lao (7M speakers)',
        'ka' => 'Georgian (4M speakers)',
        'hy' => 'Armenian (7M speakers)',
        'he' => 'Hebrew (9M speakers)',
        'az' => 'Azerbaijani (10M speakers)',
        'kk' => 'Kazakh (13M speakers)',
        'ky' => 'Kyrgyz (4.3M speakers)',
        'uz' => 'Uzbek (34M speakers)',
        'tg' => 'Tajik (8.5M speakers)',
        'mn' => 'Mongolian (5.7M speakers)'
    ],
    
    'Major African Languages' => [
        'sw' => 'Swahili (200M speakers)',
        'am' => 'Amharic (57M speakers)',
        'ha' => 'Hausa (70M speakers)',
        'yo' => 'Yoruba (46M speakers)',
        'ig' => 'Igbo (44M speakers)',
        'zu' => 'Zulu (27M speakers)',
        'af' => 'Afrikaans (16M speakers)',
        'xh' => 'Xhosa (19M speakers)',
        'so' => 'Somali (21M speakers)',
        'mg' => 'Malagasy (25M speakers)',
        'rw' => 'Kinyarwanda (12M speakers)'
    ],
    
    'Medical & Research Important Languages' => [
        'la' => 'Latin (medical terminology)',
        'sa' => 'Sanskrit (Ayurveda)',
        'eu' => 'Basque (genetic research)',
        'mt' => 'Maltese (EU language)',
        'is' => 'Icelandic (genetic studies)',
        'ga' => 'Irish (EU language)',
        'cy' => 'Welsh (genetic studies)',
        'fo' => 'Faroese (small populations)',
        'lb' => 'Luxembourgish (EU research)'
    ],
    
    'Regional Medical Centers' => [
        'ms' => 'Malay (Southeast Asia medical hub)',
        'tl' => 'Filipino/Tagalog (Philippines)',
        'jv' => 'Javanese (Indonesia)',
        'ceb' => 'Cebuano (Philippines)',
        'eo' => 'Esperanto (international medicine)'
    ]
];

// Analyze missing languages
$missingImportant = [];
$totalImportant = 0;
$supportedImportant = 0;

echo "ANALYSIS OF IMPORTANT LANGUAGES:\n";
echo "=================================\n\n";

foreach ($importantLanguages as $category => $languages) {
    echo "$category:\n";
    echo str_repeat('-', strlen($category)) . "\n";
    
    $categoryMissing = [];
    $categorySupported = 0;
    
    foreach ($languages as $langCode => $description) {
        $totalImportant++;
        
        if (in_array($langCode, $actualLanguages)) {
            echo "✅ $langCode - $description (SUPPORTED)\n";
            $supportedImportant++;
            $categorySupported++;
        } else {
            echo "❌ $langCode - $description (MISSING)\n";
            $missingImportant[] = ['code' => $langCode, 'description' => $description, 'category' => $category];
            $categoryMissing[] = $langCode;
        }
    }
    
    $categoryTotal = count($languages);
    $categoryPercent = round(($categorySupported / $categoryTotal) * 100, 1);
    echo "\nCategory Summary: $categorySupported/$categoryTotal supported ($categoryPercent%)\n";
    
    if (!empty($categoryMissing)) {
        echo "Missing: " . implode(', ', $categoryMissing) . "\n";
    }
    
    echo "\n";
}

// Overall summary
echo "OVERALL SUMMARY:\n";
echo "================\n";
echo "Total important languages analyzed: $totalImportant\n";
echo "Important languages supported: $supportedImportant\n";
echo "Important languages missing: " . count($missingImportant) . "\n";
echo "Coverage of important languages: " . round(($supportedImportant / $totalImportant) * 100, 1) . "%\n\n";

if (!empty($missingImportant)) {
    echo "PRIORITY MISSING LANGUAGES:\n";
    echo "===========================\n";
    
    // Group by category for recommendations
    $missingByCategory = [];
    foreach ($missingImportant as $missing) {
        $missingByCategory[$missing['category']][] = $missing;
    }
    
    foreach ($missingByCategory as $category => $missing) {
        echo "\n$category:\n";
        foreach ($missing as $lang) {
            echo "  - {$lang['code']}: {$lang['description']}\n";
        }
    }
    
    echo "\nRECOMMENDATIONS:\n";
    echo "================\n";
    echo "High Priority (Medical/Research Centers):\n";
    $highPriority = ['la', 'ms', 'mt', 'is'];
    foreach ($highPriority as $lang) {
        foreach ($missingImportant as $missing) {
            if ($missing['code'] === $lang) {
                echo "  - {$missing['code']}: {$missing['description']}\n";
                break;
            }
        }
    }
    
    echo "\nMedium Priority (EU/Large Populations):\n";
    $mediumPriority = ['ga', 'cy', 'eu', 'lb'];
    foreach ($mediumPriority as $lang) {
        foreach ($missingImportant as $missing) {
            if ($missing['code'] === $lang) {
                echo "  - {$missing['code']}: {$missing['description']}\n";
                break;
            }
        }
    }
} else {
    echo "🎉 EXCELLENT: All important world languages are supported!\n";
}

echo "\nCURRENT LANGUAGE FILES BREAKDOWN:\n";
echo "==================================\n";
echo "European languages: " . count(array_filter($actualLanguages, function($lang) {
    $european = ['en', 'en-us', 'de', 'fr', 'es', 'it', 'pt', 'ru', 'pl', 'nl', 'el', 'cs', 'hu', 'sv', 'da', 'no', 'fi', 'sk', 'bg', 'hr', 'sl', 'lv', 'lt', 'et', 'ro', 'uk', 'sr', 'mk', 'sq', 'be', 'is', 'mt', 'ga', 'cy', 'eu', 'ca', 'gl', 'lb', 'rm', 'fo', 'kl', 'se', 'gd'];
    return in_array($lang, $european);
})) . "\n";

echo "Asian languages: " . count(array_filter($actualLanguages, function($lang) {
    $asian = ['zh', 'hi', 'ar', 'bn', 'ur', 'id', 'ja', 'ko', 'vi', 'th', 'ms', 'tl', 'fa', 'he', 'ta', 'te', 'mr', 'gu', 'kn', 'ml', 'pa', 'ne', 'si', 'my', 'km', 'lo', 'ka', 'hy', 'az', 'kk', 'ky', 'uz', 'tg', 'mn', 'jv', 'yue', 'wuu', 'bho', 'ps', 'su', 'or', 'as', 'mai', 'bh', 'sa', 'sd', 'dv', 'tk', 'bo', 'ug'];
    return in_array($lang, $asian);
})) . "\n";

echo "African languages: " . count(array_filter($actualLanguages, function($lang) {
    $african = ['sw', 'am', 'ha', 'yo', 'ig', 'zu', 'af', 'xh', 'so', 'mg', 'rw', 'rn', 'lg', 'sn', 'ny', 'wo', 'ln', 'kg', 'lua', 'sg', 'ff', 'bm', 'ak', 'om', 'ti', 'nd', 'nr', 'nso', 'st', 'ss', 've', 'ts', 'tn'];
    return in_array($lang, $african);
})) . "\n";

echo "Other languages: " . count(array_filter($actualLanguages, function($lang) {
    $other = ['ht', 'qu', 'gn', 'ay', 'eo', 'ceb', 'hil', 'war', 'bcl', 'pam', 'ilo'];
    return in_array($lang, $other);
})) . "\n";

echo "\n=== ANALYSIS COMPLETE ===\n";
