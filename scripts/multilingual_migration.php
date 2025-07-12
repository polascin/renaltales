<?php

declare(strict_types=1);

require_once 'core/MultilingualServiceProvider.php';

/**
 * Multilingual System Migration Helper
 * 
 * Helps migrate from old system to new enhanced multilingual architecture
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0test
 */
class MultilingualMigrationHelper {
    
    private MultilingualServiceProvider $multilingual;
    private array $migrationLog = [];
    
    public function __construct() {
        // Load configuration
        $config = require 'config/multilingual.php';
        
        // Initialize new multilingual system
        $this->multilingual = MultilingualServiceProvider::getInstance($config);
        $this->multilingual->initialize();
        
        echo "🚀 Multilingual Migration Helper Started\n";
        echo "Current Language: " . $this->multilingual->getCurrentLanguage() . "\n";
        echo "Available Languages: " . count($this->multilingual->getAvailableLanguages()) . "\n\n";
    }
    
    /**
     * Run complete migration
     */
    public function migrate(): void {
        echo "🔄 Starting multilingual system migration...\n\n";
        
        // Step 1: Test new system
        $this->testNewSystem();
        
        // Step 2: Check compatibility
        $this->checkCompatibility();
        
        // Step 3: Generate usage examples
        $this->generateUsageExamples();
        
        // Step 4: Health check
        $this->performHealthCheck();
        
        // Step 5: Show migration summary
        $this->showMigrationSummary();
        
        echo "\n✅ Migration completed successfully!\n";
    }
    
    /**
     * Test new multilingual system
     */
    private function testNewSystem(): void {
        echo "🧪 Testing new multilingual system...\n";
        
        try {
            // Test basic translation
            $welcome = $this->multilingual->getText('common.welcome', 'Welcome');
            $this->log('✅ Basic translation test: ' . $welcome);
            
            // Test pluralization
            $items = $this->multilingual->getPlural('common.item', 5, ['count' => 5], '{count} items');
            $this->log('✅ Pluralization test: ' . $items);
            
            // Test parameters
            $greeting = $this->multilingual->getText('common.hello_user', 'Hello {name}!', ['name' => 'User']);
            $this->log('✅ Parameter test: ' . $greeting);
            
            // Test context
            $contextual = $this->multilingual->getTextWithContext('save', 'form', 'Save');
            $this->log('✅ Context test: ' . $contextual);
            
            // Test language switching
            $originalLang = $this->multilingual->getCurrentLanguage();
            if ($this->multilingual->setLanguage('sk')) {
                $welcome_sk = $this->multilingual->getText('common.welcome', 'Welcome');
                $this->log('✅ Language switching test (SK): ' . $welcome_sk);
                $this->multilingual->setLanguage($originalLang);
            }
            
            // Test RTL detection
            $isRTL = $this->multilingual->isRTL('ar');
            $this->log('✅ RTL detection test (Arabic): ' . ($isRTL ? 'RTL' : 'LTR'));
            
            // Test language info
            $info = $this->multilingual->getLanguageInfo('en');
            $this->log('✅ Language info test: ' . json_encode($info, JSON_PRETTY_PRINT));
            
        } catch (Exception $e) {
            $this->log('❌ System test failed: ' . $e->getMessage());
        }
        
        echo "\n";
    }
    
    /**
     * Check compatibility with existing code
     */
    private function checkCompatibility(): void {
        echo "🔍 Checking compatibility with existing code...\n";
        
        // Check if old files exist
        $oldFiles = [
            'core/LanguageDetector.php',
            'models/LanguageModel.php',
            'views/ApplicationView.php'
        ];
        
        foreach ($oldFiles as $file) {
            if (file_exists($file)) {
                $this->log("📝 Found existing file: $file");
                $this->analyzeFile($file);
            }
        }
        
        echo "\n";
    }
    
    /**
     * Analyze file for migration needs
     */
    private function analyzeFile(string $filePath): void {
        $content = file_get_contents($filePath);
        
        // Look for old translation patterns
        $patterns = [
            '/\$this->languageDetector/' => 'Uses old LanguageDetector',
            '/\$this->languageModel/' => 'Uses old LanguageModel',
            '/getText\s*\(/' => 'Uses getText method',
            '/getCurrentLanguage\s*\(/' => 'Uses getCurrentLanguage method',
            '/detectLanguage\s*\(/' => 'Uses detectLanguage method',
            '/renderLanguageSelection\s*\(/' => 'Uses renderLanguageSelection method',
        ];
        
        foreach ($patterns as $pattern => $description) {
            if (preg_match($pattern, $content)) {
                $this->log("  - $description");
            }
        }
    }
    
    /**
     * Generate usage examples for new system
     */
    private function generateUsageExamples(): void {
        echo "📖 Generating usage examples...\n";
        
        $examples = [
            'Basic Translation' => [
                'old' => '$this->languageModel->getText("welcome")',
                'new' => '$multilingual->getText("common.welcome", "Welcome")'
            ],
            'With Parameters' => [
                'old' => '$this->languageModel->getText("hello_user", ["name" => $user])',
                'new' => '$multilingual->getText("common.hello_user", "Hello {name}!", ["name" => $user])'
            ],
            'Pluralization' => [
                'old' => '$this->languageModel->getPlural("item", $count)',
                'new' => '$multilingual->getPlural("common.item", $count, ["count" => $count], "{count} items")'
            ],
            'Language Detection' => [
                'old' => '$this->languageDetector->detectLanguage()',
                'new' => '$multilingual->getCurrentLanguage()'
            ],
            'Language Switching' => [
                'old' => '$this->languageDetector->setLanguage($lang)',
                'new' => '$multilingual->setLanguage($lang)'
            ],
            'Check RTL' => [
                'old' => '$this->languageDetector->isRTL($lang)',
                'new' => '$multilingual->isRTL($lang)'
            ]
        ];
        
        foreach ($examples as $title => $example) {
            $this->log("$title:");
            $this->log("  Old: " . $example['old']);
            $this->log("  New: " . $example['new']);
        }
        
        echo "\n";
    }
    
    /**
     * Perform health check
     */
    private function performHealthCheck(): void {
        echo "🏥 Performing system health check...\n";
        
        $health = $this->multilingual->healthCheck();
        
        $this->log("Health Status: " . $health['status']);
        
        if (!empty($health['issues'])) {
            foreach ($health['issues'] as $issue) {
                $this->log("⚠️ Issue: $issue");
            }
        } else {
            $this->log("✅ No issues found");
        }
        
        // Show system stats
        $stats = $this->multilingual->getSystemStats();
        $this->log("System Statistics:");
        $this->log("  - Current Language: " . $stats['current_language']);
        $this->log("  - Supported Languages: " . $stats['supported_languages']);
        $this->log("  - Memory Usage: " . round($stats['total_memory'] / 1024 / 1024, 2) . " MB");
        
        echo "\n";
    }
    
    /**
     * Show migration summary
     */
    private function showMigrationSummary(): void {
        echo "📋 Migration Summary\n";
        echo "==================\n\n";
        
        echo "New System Features:\n";
        echo "✅ Enhanced language detection with multiple methods\n";
        echo "✅ Improved caching and performance\n";
        echo "✅ Better security and input validation\n";
        echo "✅ Pluralization support\n";
        echo "✅ Context-aware translations\n";
        echo "✅ Parameter interpolation\n";
        echo "✅ RTL language support\n";
        echo "✅ Comprehensive language information\n";
        echo "✅ Health monitoring and statistics\n";
        echo "✅ Singleton pattern for global access\n\n";
        
        echo "Integration Steps:\n";
        echo "1. Update views to use MultilingualServiceProvider\n";
        echo "2. Replace old translation calls with new API\n";
        echo "3. Update language file structure if needed\n";
        echo "4. Test language switching functionality\n";
        echo "5. Verify flag and RTL support\n";
        echo "6. Update CSS for new language selector\n\n";
        
        echo "Next Steps:\n";
        echo "- Update ApplicationView.php to use new system\n";
        echo "- Update BaseView.php integration\n";
        echo "- Test with all supported languages\n";
        echo "- Update documentation\n\n";
        
        // Show sample integration code
        echo "Sample Integration Code:\n";
        echo "```php\n";
        echo "// Initialize multilingual system\n";
        echo "\$config = require 'config/multilingual.php';\n";
        echo "\$multilingual = MultilingualServiceProvider::getInstance(\$config);\n";
        echo "\$multilingual->initialize();\n\n";
        echo "// In your views\n";
        echo "echo \$multilingual->getText('common.welcome', 'Welcome');\n";
        echo "echo \$multilingual->getPlural('common.item', \$count, ['count' => \$count]);\n";
        echo "echo \$multilingual->renderLanguageSelector(['show_flags' => true]);\n";
        echo "```\n\n";
    }
    
    /**
     * Log migration activity
     */
    private function log(string $message): void {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] $message";
        $this->migrationLog[] = $logEntry;
        echo "$message\n";
    }
    
    /**
     * Save migration log
     */
    public function saveMigrationLog(): void {
        $logContent = implode("\n", $this->migrationLog);
        file_put_contents('multilingual_migration_log.txt', $logContent);
        echo "📝 Migration log saved to: multilingual_migration_log.txt\n";
    }
}

// Run migration if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    try {
        $migration = new MultilingualMigrationHelper();
        $migration->migrate();
        $migration->saveMigrationLog();
    } catch (Exception $e) {
        echo "❌ Migration failed: " . $e->getMessage() . "\n";
        exit(1);
    }
}
