<?php

namespace RenalTales\Scripts;

/**
 * Comprehensive Multilingual Environment Consistency Check
 * 
 */

// Set up basic constants and autoloading
define('LANGUAGE_PATH', 'resources/lang/');
define('APP_ROOT', dirname(__FILE__));

// Include only the files we can test without full app bootstrap
spl_autoload_register(function ($class) {
    $paths = [
        'core/',
        'models/',
        'views/',
        'controllers/'
    ];
    
    foreach ($paths as $path) {
        $file = APP_ROOT . '/' . $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

class MultilingualConsistencyChecker {
    
    private $detector;
    private $languageModel;
    private $results = [];
    private $errors = [];
    private $warnings = [];
    
    public function __construct() {
        try {
            $this->detector = new LanguageDetector();
        } catch (Exception $e) {
            echo "Warning: Could not instantiate LanguageDetector: " . $e->getMessage() . "\n";
        }
        
        // Skip LanguageModel for now as it requires database
        $this->languageModel = null;
    }
    
    public function runAllChecks() {
        echo "=== MULTILINGUAL ENVIRONMENT CONSISTENCY CHECK ===\n";
        echo "Date: " . date('Y-m-d H:i:s') . "\n\n";
        
        $this->checkCoreComponents();
        $this->checkLanguageFiles();
        $this->checkTranslationKeys();
        $this->checkRTLSupport();
        $this->checkFlagSupport();
        $this->checkViewIntegration();
        $this->checkLanguagePriority();
        $this->checkAmericanEnglish();
        $this->checkMethodConsistency();
        
        $this->printSummary();
    }
    
    private function checkCoreComponents() {
        echo "1. CORE COMPONENTS CHECK\n";
        echo "========================\n";
        
        // Check LanguageDetector
        if (class_exists('LanguageDetector')) {
            echo "✓ LanguageDetector class exists\n";
            $this->results['detector'] = true;
        } else {
            echo "✗ LanguageDetector class missing\n";
            $this->errors[] = "LanguageDetector class not found";
        }
        
        // Check LanguageModel
        if (class_exists('LanguageModel')) {
            echo "✓ LanguageModel class exists\n";
            $this->results['model'] = true;
        } else {
            echo "✗ LanguageModel class missing\n";
            $this->errors[] = "LanguageModel class not found";
        }
        
        // Check BaseView
        if (class_exists('BaseView')) {
            echo "✓ BaseView class exists\n";
            $this->results['baseview'] = true;
        } else {
            echo "✗ BaseView class missing\n";
            $this->errors[] = "BaseView class not found";
        }
        
        echo "\n";
    }
    
    private function checkLanguageFiles() {
        echo "2. LANGUAGE FILES CHECK\n";
        echo "=======================\n";
        
        if ($this->detector) {
            try {
                $languages = $this->detector->getSupportedLanguages();
                $totalLangs = count($languages);
                $validFiles = 0;
                
                foreach ($languages as $lang) {
                    $file = LANGUAGE_PATH . $lang . '.php';
                    if (file_exists($file)) {
                        $validFiles++;
                        // Check file structure
                        try {
                            $content = require $file;
                            if (!is_array($content)) {
                                $this->errors[] = "Language file $lang.php does not return array";
                            }
                        } catch (Exception $e) {
                            $this->errors[] = "Error loading $lang.php: " . $e->getMessage();
                        }
                    } else {
                        $this->errors[] = "Language file missing: $lang.php";
                    }
                }
                
                echo "Total languages: $totalLangs\n";
                echo "Valid files: $validFiles\n";
                echo "Coverage: " . round(($validFiles/$totalLangs)*100, 1) . "%\n";
                
                $this->results['lang_files'] = $validFiles;
                $this->results['lang_total'] = $totalLangs;
            } catch (Exception $e) {
                echo "✗ Error checking language files: " . $e->getMessage() . "\n";
                $this->errors[] = "Language files check failed";
            }
        } else {
            echo "⚠ Cannot check language files - LanguageDetector not available\n";
            // Manual check of language directory
            $langFiles = glob(LANGUAGE_PATH . '*.php');
            $totalFiles = count($langFiles);
            echo "Language files found: $totalFiles\n";
            $this->results['lang_files'] = $totalFiles;
        }
        
        echo "\n";
    }
    
    private function checkTranslationKeys() {
        echo "3. TRANSLATION KEYS CHECK\n";
        echo "=========================\n";
        
        // Get reference keys from English
        $enFile = LANGUAGE_PATH . 'en.php';
        if (!file_exists($enFile)) {
            echo "✗ English reference file missing\n";
            $this->errors[] = "English reference file not found";
            return;
        }
        
        $enKeys = array_keys(require $enFile);
        $enKeyCount = count($enKeys);
        echo "English reference keys: $enKeyCount\n";
        
        // Check core languages for key consistency
        $coreLanguages = ['en', 'en-us', 'sk', 'cs', 'de'];
        $keyConsistency = [];
        
        foreach ($coreLanguages as $lang) {
            $file = LANGUAGE_PATH . $lang . '.php';
            if (file_exists($file)) {
                $langKeys = array_keys(require $file);
                $langKeyCount = count($langKeys);
                $missingKeys = array_diff($enKeys, $langKeys);
                $extraKeys = array_diff($langKeys, $enKeys);
                
                $keyConsistency[$lang] = [
                    'total' => $langKeyCount,
                    'missing' => count($missingKeys),
                    'extra' => count($extraKeys),
                    'coverage' => round(($langKeyCount/$enKeyCount)*100, 1)
                ];
                
                echo "  $lang: $langKeyCount keys ({$keyConsistency[$lang]['coverage']}%)\n";
                if ($missingKeys) {
                    $this->warnings[] = "$lang missing " . count($missingKeys) . " keys";
                }
            }
        }
        
        $this->results['key_consistency'] = $keyConsistency;
        echo "\n";
    }
    
    private function checkRTLSupport() {
        echo "4. RTL SUPPORT CHECK\n";
        echo "====================\n";
        
        $rtlLanguages = ['ar', 'fa', 'he', 'ur', 'dv', 'sd'];
        $supportedRTL = [];
        
        if ($this->detector) {
            foreach ($rtlLanguages as $lang) {
                try {
                    $isSupported = $this->detector->isSupported($lang);
                    $isRTL = $this->detector->isRTL($lang);
                    $direction = $this->detector->getDirection($lang);
                    
                    echo "  $lang: ";
                    if ($isSupported) {
                        echo "supported, ";
                        $supportedRTL[] = $lang;
                    } else {
                        echo "not supported, ";
                    }
                    echo "RTL: " . ($isRTL ? 'yes' : 'no') . ", ";
                    echo "direction: $direction\n";
                } catch (Exception $e) {
                    echo "  $lang: error - " . $e->getMessage() . "\n";
                }
            }
        } else {
            echo "⚠ Cannot check RTL support - LanguageDetector not available\n";
        }
        
        echo "RTL languages supported: " . count($supportedRTL) . "/" . count($rtlLanguages) . "\n";
        $this->results['rtl_support'] = count($supportedRTL);
        echo "\n";
    }
    
    private function checkFlagSupport() {
        echo "5. FLAG SUPPORT CHECK\n";
        echo "=====================\n";
        
        $coreLanguages = ['en', 'en-us', 'sk', 'cs', 'de', 'fr', 'es'];
        $flagsAvailable = 0;
        
        if ($this->detector) {
            foreach ($coreLanguages as $lang) {
                try {
                    $flagCode = $this->detector->getFlagCode($lang);
                    $flagPath = $this->detector->getFlagPath($lang);
                    
                    echo "  $lang: flag=$flagCode, path=$flagPath\n";
                    
                    // Check if flag file exists (checking common extensions)
                    $basePath = 'public/assets/flags/';
                    $extensions = ['.png', '.jpg', '.webp', '.gif'];
                    $flagExists = false;
                    
                    foreach ($extensions as $ext) {
                        if (file_exists($basePath . $flagCode . $ext)) {
                            $flagExists = true;
                            break;
                        }
                    }
                    
                    if ($flagExists) {
                        $flagsAvailable++;
                    } else {
                        $this->warnings[] = "Flag file missing for $lang ($flagCode)";
                    }
                } catch (Exception $e) {
                    echo "  $lang: error - " . $e->getMessage() . "\n";
                }
            }
        } else {
            echo "⚠ Cannot check flag support - LanguageDetector not available\n";
        }
        
        echo "Flags available: $flagsAvailable/" . count($coreLanguages) . "\n";
        $this->results['flag_support'] = $flagsAvailable;
        echo "\n";
    }
    
    private function checkViewIntegration() {
        echo "6. VIEW INTEGRATION CHECK\n";
        echo "=========================\n";
        
        // Check if BaseView file exists and analyze its content
        $baseViewFile = 'views/BaseView.php';
        if (file_exists($baseViewFile)) {
            echo "✓ BaseView.php file exists\n";
            
            $content = file_get_contents($baseViewFile);
            
            // Check for key methods
            $methods = ['getText', 'escape', 'render'];
            foreach ($methods as $method) {
                if (strpos($content, "function $method") !== false || 
                    strpos($content, "public function $method") !== false ||
                    strpos($content, "protected function $method") !== false) {
                    echo "✓ BaseView::$method() defined\n";
                } else {
                    echo "✗ BaseView::$method() not found\n";
                    $this->errors[] = "BaseView::$method() not defined";
                }
            }
        } else {
            echo "✗ BaseView.php file missing\n";
            $this->errors[] = "BaseView.php not found";
        }
        
        // Check ApplicationView file
        $appViewFile = 'views/ApplicationView.php';
        if (file_exists($appViewFile)) {
            echo "✓ ApplicationView.php file exists\n";
            
            $content = file_get_contents($appViewFile);
            
            // Count getText usage
            $getTextCount = substr_count($content, 'getText(');
            echo "  getText() usage count: $getTextCount\n";
            
            // Check for language-related methods
            if (strpos($content, 'renderLanguageSelector') !== false) {
                echo "✓ renderLanguageSelector method found\n";
            } else {
                echo "⚠ renderLanguageSelector method not found\n";
                $this->warnings[] = "renderLanguageSelector method missing";
            }
            
            if (strpos($content, 'renderLanguageFlags') !== false) {
                echo "✓ renderLanguageFlags method found\n";
            } else {
                echo "⚠ renderLanguageFlags method not found\n";
                $this->warnings[] = "renderLanguageFlags method missing";
            }
        } else {
            echo "✗ ApplicationView.php file missing\n";
            $this->errors[] = "ApplicationView.php not found";
        }
        
        echo "\n";
    }
    
    private function checkLanguagePriority() {
        echo "7. LANGUAGE PRIORITY CHECK\n";
        echo "==========================\n";
        
        if (!$this->detector) {
            echo "✗ Cannot check priority - LanguageDetector not available\n";
            return;
        }
        
        try {
            $languages = $this->detector->getSupportedLanguages();
            $expectedOrder = ['en', 'en-us', 'sk', 'cs', 'de', 'pl', 'hu', 'uk', 'ru'];
            
            echo "Expected first 9 languages: " . implode(', ', $expectedOrder) . "\n";
            echo "Actual first 9 languages: " . implode(', ', array_slice($languages, 0, 9)) . "\n";
            
            $orderCorrect = true;
            for ($i = 0; $i < min(9, count($languages)); $i++) {
                if ($languages[$i] !== $expectedOrder[$i]) {
                    $orderCorrect = false;
                    $this->warnings[] = "Language priority order incorrect at position " . ($i+1);
                    break;
                }
            }
            
            if ($orderCorrect) {
                echo "✓ Language priority order is correct\n";
            } else {
                echo "✗ Language priority order needs adjustment\n";
            }
            
            $this->results['priority_correct'] = $orderCorrect;
        } catch (Exception $e) {
            echo "✗ Error checking language priority: " . $e->getMessage() . "\n";
            $this->errors[] = "Language priority check failed";
        }
        
        echo "\n";
    }
    
    private function checkAmericanEnglish() {
        echo "8. AMERICAN ENGLISH CHECK\n";
        echo "=========================\n";
        
        if ($this->detector) {
            try {
                $enUsSupported = $this->detector->isSupported('en-us');
                $enUsName = $this->detector->getLanguageName('en-us');
                $enUsFlag = $this->detector->getFlagCode('en-us');
                
                echo "en-us supported: " . ($enUsSupported ? 'yes' : 'no') . "\n";
                echo "en-us name: $enUsName\n";
                echo "en-us flag: $enUsFlag\n";
                
                $this->results['en_us_support'] = $enUsSupported;
            } catch (Exception $e) {
                echo "✗ Error checking en-us support: " . $e->getMessage() . "\n";
                $this->errors[] = "en-us support check failed";
            }
        } else {
            echo "⚠ Cannot check en-us support - LanguageDetector not available\n";
        }
        
        // Check if file exists
        $enUsFile = LANGUAGE_PATH . 'en-us.php';
        if (file_exists($enUsFile)) {
            echo "✓ en-us.php file exists\n";
            try {
                $enUsData = require $enUsFile;
                if (is_array($enUsData)) {
                    $enUsKeys = array_keys($enUsData);
                    echo "en-us keys: " . count($enUsKeys) . "\n";
                } else {
                    echo "✗ en-us.php does not return array\n";
                    $this->errors[] = "en-us.php malformed";
                }
            } catch (Exception $e) {
                echo "✗ Error loading en-us.php: " . $e->getMessage() . "\n";
                $this->errors[] = "en-us.php loading failed";
            }
        } else {
            echo "✗ en-us.php file missing\n";
            $this->errors[] = "American English file not found";
        }
        
        echo "\n";
    }
    
    private function checkMethodConsistency() {
        echo "9. METHOD CONSISTENCY CHECK\n";
        echo "===========================\n";
        
        if ($this->detector) {
            // Check if all required methods exist and work
            $methods = [
                'getSupportedLanguages' => [],
                'getCurrentLanguage' => [],
                'getLanguageName' => ['en'],
                'getFlagCode' => ['en'],
                'isRTL' => ['ar'],
                'getDirection' => ['ar']
            ];
            
            foreach ($methods as $method => $args) {
                try {
                    if (method_exists($this->detector, $method)) {
                        $result = call_user_func_array([$this->detector, $method], $args);
                        echo "✓ LanguageDetector::$method() works\n";
                    } else {
                        echo "✗ LanguageDetector::$method() missing\n";
                        $this->errors[] = "Method $method not found";
                    }
                } catch (Exception $e) {
                    echo "✗ LanguageDetector::$method() error: " . $e->getMessage() . "\n";
                    $this->errors[] = "Method $method failed";
                }
            }
        } else {
            echo "⚠ Cannot check LanguageDetector methods - class not available\n";
        }
        
        // Skip LanguageModel methods since we don't have database connection
        echo "⚠ LanguageModel methods skipped (requires database)\n";
        
        echo "\n";
    }
    
    private function printSummary() {
        echo "=== SUMMARY ===\n";
        echo "===============\n";
        
        echo "RESULTS:\n";
        echo "- Total languages: " . ($this->results['lang_total'] ?? 'N/A') . "\n";
        echo "- Valid language files: " . ($this->results['lang_files'] ?? 'N/A') . "\n";
        echo "- RTL languages supported: " . ($this->results['rtl_support'] ?? 'N/A') . "/6\n";
        echo "- Flag support: " . ($this->results['flag_support'] ?? 'N/A') . "/7\n";
        echo "- American English: " . (($this->results['en_us_support'] ?? false) ? 'supported' : 'not supported') . "\n";
        echo "- Priority order: " . (($this->results['priority_correct'] ?? false) ? 'correct' : 'incorrect') . "\n";
        
        if (!empty($this->errors)) {
            echo "\nERRORS (" . count($this->errors) . "):\n";
            foreach ($this->errors as $error) {
                echo "- $error\n";
            }
        }
        
        if (!empty($this->warnings)) {
            echo "\nWARNINGS (" . count($this->warnings) . "):\n";
            foreach ($this->warnings as $warning) {
                echo "- $warning\n";
            }
        }
        
        $errorCount = count($this->errors);
        $warningCount = count($this->warnings);
        
        echo "\nOVERALL STATUS: ";
        if ($errorCount === 0 && $warningCount === 0) {
            echo "✓ EXCELLENT - No issues found\n";
        } elseif ($errorCount === 0) {
            echo "⚠ GOOD - Minor warnings only\n";
        } elseif ($errorCount <= 2) {
            echo "⚠ FAIR - Some issues need attention\n";
        } else {
            echo "✗ POOR - Multiple critical issues\n";
        }
        
        echo "\n";
    }
}

// Run the consistency check
$checker = new MultilingualConsistencyChecker();
$checker->runAllChecks();
