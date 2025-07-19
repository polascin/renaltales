&lt;?php
/**
 * CSS Validation and Testing Script
 * Tests the new CSS structure for syntax errors, missing files, and functionality
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

class CSSValidator {
    private $basePath;
    private $errors = [];
    private $warnings = [];
    private $passes = [];
    
    public function __construct($basePath) {
        $this->basePath = rtrim($basePath, '/\\');
    }
    
    public function validateAll() {
        echo "&lt;h1&gt;CSS Structure Validation Results&lt;/h1&gt;\n";
        echo "&lt;style&gt;
            body { font-family: Arial, sans-serif; margin: 20px; }
            .pass { color: green; }
            .warning { color: orange; }
            .error { color: red; }
            .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
            .file-path { font-family: monospace; background: #f5f5f5; padding: 2px 4px; }
            pre { background: #f8f8f8; padding: 10px; border-radius: 3px; overflow-x: auto; }
        &lt;/style&gt;\n";
        
        // Test 1: Check main CSS structure and imports
        $this->testMainCSSStructure();
        
        // Test 2: Validate individual CSS files
        $this->validateCSSFiles();
        
        // Test 3: Check for missing files
        $this->checkMissingFiles();
        
        // Test 4: Test CSS variable consistency
        $this->testCSSVariables();
        
        // Test 5: Validate responsive breakpoints
        $this->testResponsiveBreakpoints();
        
        // Display summary
        $this->displaySummary();
    }
    
    private function testMainCSSStructure() {
        echo "&lt;div class='section'&gt;&lt;h2&gt;Test 1: Main CSS Structure&lt;/h2&gt;\n";
        
        $mainCssPath = $this->basePath . DIRECTORY_SEPARATOR . 'main.css';
        
        if (!file_exists($mainCssPath)) {
            $this->addError("Main CSS file not found: $mainCssPath");
            return;
        }
        
        $content = file_get_contents($mainCssPath);
        
        // Check for essential imports
        $requiredImports = [
            'themes.css' =&gt; '@import url(\'public/assets/css/themes.css\')',
            'variables.css' =&gt; '@import url(\'core/variables.css\')',
            'reset.css' =&gt; '@import url(\'core/reset.css\')',
            'typography.css' =&gt; '@import url(\'core/typography.css\')',
            'layout.css' =&gt; '@import url(\'public/assets/css/layout.css\')',
            'basic.css' =&gt; '@import url(\'public/assets/css/basic.css\')',
            'responsive.css' =&gt; '@import url(\'public/assets/css/responsive.css\')'
        ];
        
        foreach ($requiredImports as $file =&gt; $expectedImport) {
            if (strpos($content, $expectedImport) !== false) {
                $this->addPass("✓ Import found: $file");
            } else {
                $this->addError("✗ Missing or incorrect import: $file");
            }
        }
        
        // Check import order (themes should be first)
        $firstImport = strpos($content, '@import');
        $themesImport = strpos($content, 'themes.css');
        
        if ($firstImport !== false && $themesImport !== false && $themesImport &lt; $firstImport + 100) {
            $this->addPass("✓ Themes imported first (correct order)");
        } else {
            $this->addWarning("⚠ Theme import may not be first - could cause cascade issues");
        }
        
        echo "&lt;/div&gt;\n";
    }
    
    private function validateCSSFiles() {
        echo "&lt;div class='section'&gt;&lt;h2&gt;Test 2: Individual CSS File Validation&lt;/h2&gt;\n";
        
        $cssFiles = [
            'main.css',
            'public/assets/css/themes.css',
            'public/assets/css/basic.css',
            'public/assets/css/components.css',
            'public/assets/css/layout.css',
            'public/assets/css/responsive.css',
            'core/variables.css',
            'core/reset.css',
            'core/typography.css',
            'components/buttons.css',
            'components/forms.css',
            'components/cards.css',
            'components/navigation.css',
            'layout/spacing.css'
        ];
        
        foreach ($cssFiles as $file) {
            $this->validateSingleCSSFile($file);
        }
        
        echo "&lt;/div&gt;\n";
    }
    
    private function validateSingleCSSFile($relativePath) {
        $fullPath = $this->basePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        
        if (!file_exists($fullPath)) {
            $this->addError("File not found: &lt;span class='file-path'&gt;$relativePath&lt;/span&gt;");
            return;
        }
        
        $content = file_get_contents($fullPath);
        $lines = explode("\n", $content);
        
        // Basic syntax validation
        $openBraces = substr_count($content, '{');
        $closeBraces = substr_count($content, '}');
        
        if ($openBraces !== $closeBraces) {
            $this->addError("Brace mismatch in &lt;span class='file-path'&gt;$relativePath&lt;/span&gt; (Open: $openBraces, Close: $closeBraces)");
        } else {
            $this->addPass("✓ Syntax valid: &lt;span class='file-path'&gt;$relativePath&lt;/span&gt;");
        }
        
        // Check for common CSS errors
        $this->checkCommonCSSErrors($relativePath, $content);
    }
    
    private function checkCommonCSSErrors($file, $content) {
        // Check for missing semicolons (basic check)
        $lines = explode("\n", $content);
        foreach ($lines as $lineNum =&gt; $line) {
            $trimmed = trim($line);
            
            // Skip comments and empty lines
            if (empty($trimmed) || strpos($trimmed, '/*') === 0 || strpos($trimmed, '//') === 0 || 
                strpos($trimmed, '}') === 0 || strpos($trimmed, '{') !== false || strpos($trimmed, '@') === 0) {
                continue;
            }
            
            // Check for missing semicolon on property declarations
            if (strpos($trimmed, ':') !== false && substr($trimmed, -1) !== ';' && substr($trimmed, -1) !== '{') {
                $this->addWarning("⚠ Possible missing semicolon in &lt;span class='file-path'&gt;$file&lt;/span&gt; line " . ($lineNum + 1) . ": $trimmed");
            }
        }
    }
    
    private function checkMissingFiles() {
        echo "&lt;div class='section'&gt;&lt;h2&gt;Test 3: Missing File Check&lt;/h2&gt;\n";
        
        // Parse main.css to find all @import statements
        $mainCssPath = $this->basePath . DIRECTORY_SEPARATOR . 'main.css';
        if (!file_exists($mainCssPath)) {
            $this->addError("Cannot check imports - main.css not found");
            echo "&lt;/div&gt;\n";
            return;
        }
        
        $content = file_get_contents($mainCssPath);
        preg_match_all("/@import\s+url\(['\"]?([^'\"]+)['\"]?\);?/", $content, $matches);
        
        foreach ($matches[1] as $importPath) {
            $fullPath = $this->basePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $importPath);
            
            if (file_exists($fullPath)) {
                $this->addPass("✓ Import file exists: &lt;span class='file-path'&gt;$importPath&lt;/span&gt;");
            } else {
                $this->addError("✗ Import file missing: &lt;span class='file-path'&gt;$importPath&lt;/span&gt;");
            }
        }
        
        echo "&lt;/div&gt;\n";
    }
    
    private function testCSSVariables() {
        echo "&lt;div class='section'&gt;&lt;h2&gt;Test 4: CSS Variable Consistency&lt;/h2&gt;\n";
        
        $variableFiles = [
            'public/assets/css/themes.css',
            'core/variables.css'
        ];
        
        $allVariables = [];
        
        foreach ($variableFiles as $file) {
            $fullPath = $this->basePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file);
            
            if (!file_exists($fullPath)) {
                $this->addWarning("⚠ Variable file not found: $file");
                continue;
            }
            
            $content = file_get_contents($fullPath);
            
            // Extract CSS variables
            preg_match_all('/--([a-zA-Z0-9\-]+)\s*:\s*([^;]+);/', $content, $matches);
            
            $fileVariables = array_combine($matches[1], $matches[2]);
            $allVariables[$file] = $fileVariables;
            
            $this->addPass("✓ Found " . count($fileVariables) . " variables in &lt;span class='file-path'&gt;$file&lt;/span&gt;");
        }
        
        // Check for common theme variables
        $requiredThemeVariables = [
            'primary-color', 'secondary-color', 'background-color', 'text-color',
            'border-color', 'card-bg-color', 'button-text-color'
        ];
        
        $themesPath = 'public/assets/css/themes.css';
        if (isset($allVariables[$themesPath])) {
            foreach ($requiredThemeVariables as $variable) {
                if (array_key_exists($variable, $allVariables[$themesPath])) {
                    $this->addPass("✓ Required theme variable found: --$variable");
                } else {
                    $this->addError("✗ Missing required theme variable: --$variable");
                }
            }
        }
        
        echo "&lt;/div&gt;\n";
    }
    
    private function testResponsiveBreakpoints() {
        echo "&lt;div class='section'&gt;&lt;h2&gt;Test 5: Responsive Breakpoint Validation&lt;/h2&gt;\n";
        
        $responsiveFile = $this->basePath . DIRECTORY_SEPARATOR . 'public/assets/css/responsive.css';
        
        if (!file_exists($responsiveFile)) {
            $this->addError("Responsive CSS file not found");
            echo "&lt;/div&gt;\n";
            return;
        }
        
        $content = file_get_contents($responsiveFile);
        
        // Check for common breakpoints
        $commonBreakpoints = [
            '768px' =&gt; 'tablet',
            '1024px' =&gt; 'desktop',
            '480px' =&gt; 'mobile'
        ];
        
        foreach ($commonBreakpoints as $breakpoint =&gt; $device) {
            if (strpos($content, $breakpoint) !== false) {
                $this->addPass("✓ Breakpoint found for $device: $breakpoint");
            } else {
                $this->addWarning("⚠ No breakpoint found for $device ($breakpoint)");
            }
        }
        
        // Count media queries
        $mediaQueryCount = preg_match_all('/@media[^{]+\{/', $content);
        $this->addPass("✓ Found $mediaQueryCount media queries in responsive.css");
        
        echo "&lt;/div&gt;\n";
    }
    
    private function addPass($message) {
        $this->passes[] = $message;
        echo "&lt;div class='pass'&gt;$message&lt;/div&gt;\n";
    }
    
    private function addWarning($message) {
        $this->warnings[] = $message;
        echo "&lt;div class='warning'&gt;$message&lt;/div&gt;\n";
    }
    
    private function addError($message) {
        $this->errors[] = $message;
        echo "&lt;div class='error'&gt;$message&lt;/div&gt;\n";
    }
    
    private function displaySummary() {
        echo "&lt;div class='section'&gt;&lt;h2&gt;Validation Summary&lt;/h2&gt;\n";
        echo "&lt;p&gt;&lt;strong&gt;Results:&lt;/strong&gt;&lt;/p&gt;\n";
        echo "&lt;ul&gt;\n";
        echo "&lt;li class='pass'&gt;" . count($this->passes) . " tests passed&lt;/li&gt;\n";
        echo "&lt;li class='warning'&gt;" . count($this->warnings) . " warnings&lt;/li&gt;\n";
        echo "&lt;li class='error'&gt;" . count($this->errors) . " errors&lt;/li&gt;\n";
        echo "&lt;/ul&gt;\n";
        
        if (count($this->errors) === 0) {
            echo "&lt;div class='pass'&gt;&lt;strong&gt;✅ CSS structure validation PASSED - No critical errors found!&lt;/strong&gt;&lt;/div&gt;\n";
        } else {
            echo "&lt;div class='error'&gt;&lt;strong&gt;❌ CSS structure validation FAILED - Please fix errors before proceeding&lt;/strong&gt;&lt;/div&gt;\n";
        }
        
        echo "&lt;/div&gt;\n";
    }
}

// Run the validation
$validator = new CSSValidator(__DIR__);
$validator-&gt;validateAll();
?&gt;
