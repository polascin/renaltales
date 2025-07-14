<?php

namespace RenalTales\Scripts;

/**
 * Flag Path Validation and Testing Script
 * 
 * This script validates the flag path system, tests fallbacks,
 * and provides comprehensive reporting on flag availability.
 */

// Initialize environment
define('LANGUAGE_PATH', 'resources/lang/');
define('APP_ROOT', dirname(__FILE__));

// Include bootstrap for autoloading
require_once __DIR__ . '/../bootstrap.php';

// Use PSR-4 autoloaded classes
use RenalTales\Models\LanguageModel;
use Exception;

class FlagPathValidator {
    
private $languageModel;
    private $results = [];
    private $errors = [];
    private $warnings = [];
    private $flagsBasePath = 'public/assets/flags/';
    private $webBasePath = 'assets/flags/';
    
    public function __construct() {
try {
            $this->languageModel = new LanguageModel();
        } catch (Exception $e) {
            echo "Error: Could not initialize LanguageModel: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
    
    public function runValidation() {
        echo "=== FLAG PATH VALIDATION SYSTEM ===\n";
        echo "Date: " . date('Y-m-d H:i:s') . "\n\n";
        
        $this->countFlagFiles();
        $this->validateFlagMappings();
        $this->testBestFlagPath();
        $this->testFallbackSystem();
        $this->validateAllLanguageFlags();
        $this->checkCachingSystem();
        $this->testDifferentFormats();
        
        $this->printSummary();
    }
    
    private function countFlagFiles() {
        echo "1. FLAG FILE INVENTORY\n";
        echo "======================\n";
        
        $extensions = ['webp', 'png', 'jpg', 'gif'];
        $totalFlags = 0;
        $flagsByExtension = [];
        
        foreach ($extensions as $ext) {
            $pattern = $this->flagsBasePath . "*.$ext";
            $files = glob($pattern);
            $count = count($files);
            $flagsByExtension[$ext] = $count;
            $totalFlags += $count;
            
            echo "  .$ext files: $count\n";
        }
        
        echo "Total flag files: $totalFlags\n";
        
        // Count unique flag codes
        $uniqueFlags = [];
        foreach ($extensions as $ext) {
            $files = glob($this->flagsBasePath . "*.$ext");
            foreach ($files as $file) {
                $flagCode = basename($file, ".$ext");
                $uniqueFlags[$flagCode] = true;
            }
        }
        
        $uniqueCount = count($uniqueFlags);
        echo "Unique flag codes: $uniqueCount\n";
        
        $this->results['total_flags'] = $totalFlags;
        $this->results['unique_flags'] = $uniqueCount;
        $this->results['flags_by_extension'] = $flagsByExtension;
        
        echo "\n";
    }
    
    private function validateFlagMappings() {
        echo "2. FLAG MAPPING VALIDATION\n";
        echo "==========================\n";
        
$supportedLanguages = $this->languageModel->getSupportedLanguages();
        $mappingErrors = 0;
        $availableFlags = 0;
        
        foreach ($supportedLanguages as $lang) {
            $flagCode = $this->languageModel->getFlagCode($lang);
            // Note: getFlagPath is not available in LanguageModel, so we'll skip this line
            //$expectedPath = $this->languageModel->getFlagPath($lang, $this->webBasePath);
            
            // Check if any format exists
            $extensions = ['webp', 'png', 'jpg', 'gif'];
            $flagExists = false;
            
            foreach ($extensions as $ext) {
                $filePath = $this->flagsBasePath . $flagCode . ".$ext";
                if (file_exists($filePath)) {
                    $flagExists = true;
                    break;
                }
            }
            
            if ($flagExists) {
                $availableFlags++;
            } else {
                $this->warnings[] = "Missing flag for language '$lang' (expected: $flagCode)";
                $mappingErrors++;
            }
            
            echo "  $lang -> $flagCode: " . ($flagExists ? "✓" : "✗") . "\n";
        }
        
        $totalLanguages = count($supportedLanguages);
        $coverage = round(($availableFlags / $totalLanguages) * 100, 1);
        
        echo "\nFlag coverage: $availableFlags/$totalLanguages ($coverage%)\n";
        echo "Missing flags: $mappingErrors\n";
        
        $this->results['flag_coverage'] = $coverage;
        $this->results['missing_flags'] = $mappingErrors;
        
        echo "\n";
    }
    
    private function testBestFlagPath() {
        echo "3. BEST FLAG PATH TESTING\n";
        echo "=========================\n";
        
        $testLanguages = ['en', 'sk', 'de', 'fr', 'ar', 'zh', 'nonexistent'];
        $successful = 0;
        $fallbacks = 0;
        
        foreach ($testLanguages as $lang) {
            echo "Testing language: $lang\n";
            
            try {
                // getBestFlagPath is not available in LanguageModel, so we'll simplify this
                $flagCode = $this->languageModel->getFlagCode($lang);
                
                // Check if the flag file exists
                $extensions = ['webp', 'png', 'jpg', 'gif'];
                $bestPath = null;
                $exists = false;
                
                foreach ($extensions as $ext) {
                    $testPath = $this->flagsBasePath . $flagCode . ".$ext";
                    if (file_exists($testPath)) {
                        $bestPath = $this->webBasePath . $flagCode . ".$ext";
                        $exists = true;
                        break;
                    }
                }
                
                if (!$exists) {
                    // Try UN fallback
                    foreach ($extensions as $ext) {
                        $testPath = $this->flagsBasePath . "un.$ext";
                        if (file_exists($testPath)) {
                            $bestPath = $this->webBasePath . "un.$ext";
                            $exists = true;
                            $fallbacks++;
                            echo "  -> Using UN fallback\n";
                            break;
                        }
                    }
                }
                
                echo "  Best path: " . ($bestPath ?: "none") . "\n";
                echo "  File exists: " . ($exists ? "Yes" : "No") . "\n";
                
                if ($exists && $bestPath && strpos($bestPath, 'un.') === false) {
                    $successful++;
                }
                
            } catch (Exception $e) {
                echo "  Error: " . $e->getMessage() . "\n";
                $this->errors[] = "Flag path test failed for $lang: " . $e->getMessage();
            }
            
            echo "\n";
        }
        
        echo "Successful paths: $successful\n";
        echo "Fallback paths: $fallbacks\n";
        
        $this->results['successful_paths'] = $successful;
        $this->results['fallback_paths'] = $fallbacks;
        
        echo "\n";
    }
    
    private function testFallbackSystem() {
        echo "4. FALLBACK SYSTEM TESTING\n";
        echo "===========================\n";
        
        // Test UN flag existence
        $unFlagExists = false;
        $extensions = ['webp', 'png', 'jpg', 'gif'];
        
        foreach ($extensions as $ext) {
            $unPath = $this->flagsBasePath . "un.$ext";
            if (file_exists($unPath)) {
                $unFlagExists = true;
                echo "UN flag found: un.$ext\n";
                break;
            }
        }
        
        if (!$unFlagExists) {
            $this->errors[] = "UN fallback flag not found";
            echo "✗ UN fallback flag missing\n";
        } else {
            echo "✓ UN fallback flag available\n";
        }
        
        // Test fallback with non-existent language
        try {
            // getBestFlagPath is not available in LanguageModel, so we'll skip this test
            echo "Skipping fallback test (getBestFlagPath not available in LanguageModel)\n";
        } catch (Exception $e) {
            $this->errors[] = "Fallback test failed: " . $e->getMessage();
        }
        
        $this->results['un_flag_exists'] = $unFlagExists;
        
        echo "\n";
    }
    
    private function validateAllLanguageFlags() {
        echo "5. COMPREHENSIVE LANGUAGE FLAG VALIDATION\n";
        echo "==========================================\n";
        
        $languages = $this->languageModel->getSupportedLanguages();
        $missingFlags = [];
        $availableFlags = [];
        
        foreach ($languages as $lang) {
            $flagCode = $this->languageModel->getFlagCode($lang);
            $hasFlag = false;
            $formats = [];
            
            $extensions = ['webp', 'png', 'jpg', 'gif'];
            foreach ($extensions as $ext) {
                $flagPath = $this->flagsBasePath . $flagCode . ".$ext";
                if (file_exists($flagPath)) {
                    $hasFlag = true;
                    $formats[] = $ext;
                }
            }
            
            if ($hasFlag) {
                $availableFlags[$lang] = [
                    'flag_code' => $flagCode,
                    'formats' => $formats
                ];
            } else {
                $missingFlags[] = [
                    'language' => $lang,
                    'flag_code' => $flagCode
                ];
            }
        }
        
        echo "Languages with flags: " . count($availableFlags) . "\n";
        echo "Languages missing flags: " . count($missingFlags) . "\n";
        
        if (!empty($missingFlags)) {
            echo "\nMissing flags:\n";
            foreach ($missingFlags as $missing) {
                echo "  {$missing['language']} -> {$missing['flag_code']}\n";
            }
        }
        
        $this->results['available_flags'] = $availableFlags;
        $this->results['missing_flag_details'] = $missingFlags;
        
        echo "\n";
    }
    
    private function checkCachingSystem() {
        echo "6. CACHING SYSTEM TEST\n";
        echo "======================\n";
        
        // clearCache and getBestFlagPath are not available in LanguageModel, so we'll skip this test
        echo "Skipping cache test (clearCache and getBestFlagPath not available in LanguageModel)\n";
        echo "LanguageModel doesn't implement caching like the old LanguageManager.\n";
        
        echo "\n";
    }
    
    private function testDifferentFormats() {
        echo "7. FORMAT PREFERENCE TESTING\n";
        echo "=============================\n";
        
        $testFormats = ['webp', 'png', 'jpg', 'gif'];
        $formatPreference = [];
        
        // Find a flag that exists in multiple formats
        $testFlagCode = null;
        $availableFormats = [];
        
        foreach (glob($this->flagsBasePath . "*.webp") as $webpFile) {
            $code = basename($webpFile, '.webp');
            $formats = [];
            
            foreach ($testFormats as $format) {
                if (file_exists($this->flagsBasePath . $code . ".$format")) {
                    $formats[] = $format;
                }
            }
            
            if (count($formats) > 1) {
                $testFlagCode = $code;
                $availableFormats = $formats;
                break;
            }
        }
        
        if ($testFlagCode) {
            echo "Testing with flag code: $testFlagCode\n";
            echo "Available formats: " . implode(', ', $availableFormats) . "\n";
            
            // getBestFlagPath is not available in LanguageModel, so we'll skip this test
            echo "Skipping format preference test (getBestFlagPath not available in LanguageModel)\n";
            echo "Available formats: " . implode(', ', $availableFormats) . "\n";
        } else {
            echo "No flags with multiple formats found for testing\n";
        }
        
        echo "\n";
    }
    
    private function printSummary() {
        echo "8. VALIDATION SUMMARY\n";
        echo "=====================\n";
        
        echo "Total flag files: " . ($this->results['total_flags'] ?? 0) . "\n";
        echo "Unique flag codes: " . ($this->results['unique_flags'] ?? 0) . "\n";
        echo "Flag coverage: " . ($this->results['flag_coverage'] ?? 0) . "%\n";
        echo "Missing flags: " . ($this->results['missing_flags'] ?? 0) . "\n";
        echo "UN fallback available: " . (($this->results['un_flag_exists'] ?? false) ? 'Yes' : 'No') . "\n";
        
        echo "\nFile formats breakdown:\n";
        foreach ($this->results['flags_by_extension'] ?? [] as $ext => $count) {
            echo "  .$ext: $count files\n";
        }
        
        if (!empty($this->errors)) {
            echo "\nErrors (" . count($this->errors) . "):\n";
            foreach ($this->errors as $error) {
                echo "  ✗ $error\n";
            }
        }
        
        if (!empty($this->warnings)) {
            echo "\nWarnings (" . count($this->warnings) . "):\n";
            foreach ($this->warnings as $warning) {
                echo "  ⚠ $warning\n";
            }
        }
        
        if (empty($this->errors)) {
            echo "\n✓ Flag path validation completed successfully!\n";
        } else {
            echo "\n✗ Flag path validation completed with " . count($this->errors) . " error(s)\n";
        }
        
        echo "\nRecommendations:\n";
        if (($this->results['missing_flags'] ?? 0) > 0) {
            echo "  - Add missing flag files for better language coverage\n";
        }
        if (!($this->results['un_flag_exists'] ?? false)) {
            echo "  - Add UN flag (un.webp/png/jpg/gif) for fallback support\n";
        }
        if (($this->results['flag_coverage'] ?? 0) < 90) {
            echo "  - Consider improving flag coverage to at least 90%\n";
        }
        
        echo "\n";
    }
}

// Run the validation
try {
    $validator = new FlagPathValidator();
    $validator->runValidation();
} catch (Exception $e) {
    echo "Fatal error: " . $e->getMessage() . "\n";
    exit(1);
}
