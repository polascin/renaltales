<?php
/**
 * Refined PHP Issue Fix Script
 * 
 * This script fixes the syntax errors caused by the previous automated fixes
 * and applies more careful patterns to resolve PHP compatibility issues.
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

declare(strict_types=1);

class SyntaxErrorFixer {
    private array $config;
    private array $fixed = [];
    private int $totalFixes = 0;
    
    public function __construct() {
        $this->config = [
            'app_dir' => dirname(__FILE__),
            'excluded_dirs' => ['vendor', 'node_modules', '.git', 'cache', 'storage'],
            'log_file' => 'syntax_fixes.log',
            'backup_dir' => 'backups/syntax_' . date('Y-m-d_H-i-s')
        ];
        
        // Create backup directory
        if (!is_dir($this->config['backup_dir'])) {
            mkdir($this->config['backup_dir'], 0755, true);
        }
        
        $this->log("Syntax Error Fixer started");
    }
    
    public function run(): void {
        echo "=== Syntax Error Fixer ===\n";
        echo "Restoring files from backups and applying careful fixes...\n\n";
        
        // First, restore files from the previous backup
        $this->restoreFromBackup();
        
        // Then apply more careful fixes
        $files = $this->getPhpFiles();
        
        foreach ($files as $file) {
            echo "Processing: " . basename($file) . "\n";
            $this->fixFileSafely($file);
        }
        
        echo "\n=== Fix Complete ===\n";
        echo "Total fixes applied: " . $this->totalFixes . "\n";
        echo "Files modified: " . count($this->fixed) . "\n";
        echo "Log file: " . $this->config['log_file'] . "\n";
    }
    
    private function restoreFromBackup(): void {
        $backupDir = 'backups/2025-07-12_19-40-10';
        if (is_dir($backupDir)) {
            $backupFiles = glob($backupDir . '/*.php');
            foreach ($backupFiles as $backupFile) {
                $originalFile = $this->config['app_dir'] . '/' . basename($backupFile);
                if (file_exists($originalFile)) {
                    copy($backupFile, $originalFile);
                    echo "Restored: " . basename($backupFile) . "\n";
                }
            }
        }
    }
    
    private function fixFileSafely(string $filePath): void {
        $content = file_get_contents($filePath);
        $originalContent = $content;
        
        // Create backup
        $backupPath = $this->config['backup_dir'] . '/' . basename($filePath);
        file_put_contents($backupPath, $originalContent);
        
        // Apply safe fixes only
        $content = $this->fixSuperglobalAccessSafe($content, $filePath);
        $content = $this->fixArrayAccessSafe($content, $filePath);
        $content = $this->addMissingTryCatch($content, $filePath);
        $content = $this->addErrorLoggingSafe($content, $filePath);
        
        // Save if modified
        if ($content !== $originalContent) {
            file_put_contents($filePath, $content);
            $this->fixed[] = $filePath;
            $this->log("Fixed file: $filePath");
        }
    }
    
    /**
     * Safely fix superglobal access with very specific patterns
     */
    private function fixSuperglobalAccessSafe(string $content, string $filePath): string {
        $fixes = 0;
        
        // Only fix very specific and safe patterns
        $patterns = [
            // $_ENV['key'] without existing null coalescing
            '/(?<!\(\s*)\$_ENV\[([\'"][^\'"\]]+[\'"])\](?!\s*\?\?|\s*\|\|)(?=\s*[;\),\n\r])/' => '($_ENV[$1] ?? null)',
            '/(?<!\(\s*)\$_SERVER\[([\'"][^\'"\]]+[\'"])\](?!\s*\?\?|\s*\|\|)(?=\s*[;\),\n\r])/' => '($_SERVER[$1] ?? null)',
            '/(?<!\(\s*)\$_GET\[([\'"][^\'"\]]+[\'"])\](?!\s*\?\?|\s*\|\|)(?=\s*[;\),\n\r])/' => '($_GET[$1] ?? null)',
            '/(?<!\(\s*)\$_POST\[([\'"][^\'"\]]+[\'"])\](?!\s*\?\?|\s*\|\|)(?=\s*[;\),\n\r])/' => '($_POST[$1] ?? null)',
            '/(?<!\(\s*)\$_SESSION\[([\'"][^\'"\]]+[\'"])\](?!\s*\?\?|\s*\|\|)(?=\s*[;\),\n\r])/' => '($_SESSION[$1] ?? null)',
        ];
        
        foreach ($patterns as $pattern => $replacement) {
            $newContent = preg_replace($pattern, $replacement, $content);
            if ($newContent !== null && $newContent !== $content) {
                $matches = preg_match_all($pattern, $content);
                $fixes += $matches;
                $content = $newContent;
            }
        }
        
        if ($fixes > 0) {
            $this->totalFixes += $fixes;
            $this->log("Applied $fixes safe superglobal access fixes in $filePath");
        }
        
        return $content;
    }
    
    /**
     * Safely fix array access with careful patterns
     */
    private function fixArrayAccessSafe(string $content, string $filePath): string {
        $fixes = 0;
        
        // Only fix very specific cases of regular array access
        $patterns = [
            // $config['key'] at end of statement or line
            '/(?<!\(\s*)\$config\[([\'"][^\'"\]]+[\'"])\](?!\s*\?\?|\s*\|\|)(?=\s*[;\n\r])/' => '($config[$1] ?? null)',
            '/(?<!\(\s*)\$options\[([\'"][^\'"\]]+[\'"])\](?!\s*\?\?|\s*\|\|)(?=\s*[;\n\r])/' => '($options[$1] ?? null)',
            '/(?<!\(\s*)\$params\[([\'"][^\'"\]]+[\'"])\](?!\s*\?\?|\s*\|\|)(?=\s*[;\n\r])/' => '($params[$1] ?? null)',
        ];
        
        foreach ($patterns as $pattern => $replacement) {
            $newContent = preg_replace($pattern, $replacement, $content);
            if ($newContent !== null && $newContent !== $content) {
                $matches = preg_match_all($pattern, $content);
                $fixes += $matches;
                $content = $newContent;
            }
        }
        
        if ($fixes > 0) {
            $this->totalFixes += $fixes;
            $this->log("Applied $fixes safe array access fixes in $filePath");
        }
        
        return $content;
    }
    
    /**
     * Add missing try-catch blocks safely
     */
    private function addMissingTryCatch(string $content, string $filePath): string {
        $fixes = 0;
        
        // Look for try blocks without corresponding catch, but be very careful
        $lines = explode("\n", $content);
        $newLines = [];
        $inTryBlock = false;
        $tryLevel = 0;
        $foundCatch = false;
        
        foreach ($lines as $i => $line) {
            $newLines[] = $line;
            
            // Check for try statement
            if (preg_match('/^\s*try\s*\{/', $line)) {
                $inTryBlock = true;
                $tryLevel = 1;
                $foundCatch = false;
            }
            
            // Track brace levels
            if ($inTryBlock) {
                $tryLevel += substr_count($line, '{') - substr_count($line, '}');
                
                // Check for catch on current or next lines
                if (preg_match('/catch\s*\(/', $line) || 
                    (isset($lines[$i + 1]) && preg_match('/^\s*catch\s*\(/', $lines[$i + 1]))) {
                    $foundCatch = true;
                }
                
                // If we're back to level 0 and no catch found, add one
                if ($tryLevel <= 0 && !$foundCatch) {
                    $newLines[] = "} catch (Exception \$e) {";
                    $newLines[] = "    error_log('Error in " . basename($filePath) . ": ' . \$e->getMessage());";
                    $newLines[] = "    throw \$e;";
                    $fixes++;
                    $inTryBlock = false;
                }
            }
        }
        
        if ($fixes > 0) {
            $this->totalFixes += $fixes;
            $this->log("Applied $fixes try-catch fixes in $filePath");
            return implode("\n", $newLines);
        }
        
        return $content;
    }
    
    /**
     * Add error logging to catch blocks safely
     */
    private function addErrorLoggingSafe(string $content, string $filePath): string {
        $fixes = 0;
        
        // Find catch blocks and add error logging if missing
        $pattern = '/catch\s*\(\s*([^)]+)\s*\)\s*\{([^}]*)\}/s';
        
        $content = preg_replace_callback($pattern, function($matches) use (&$fixes, $filePath) {
            $catchSignature = $matches[1];
            $catchBody = $matches[2];
            
            // Check if error_log is already present
            if (strpos($catchBody, 'error_log') === false) {
                // Extract variable name
                if (preg_match('/\$([a-zA-Z_][a-zA-Z0-9_]*)/', $catchSignature, $varMatches)) {
                    $varName = $varMatches[1];
                    $logStatement = "\n    error_log('Exception in " . basename($filePath) . ": ' . \$" . $varName . "->getMessage());";
                    $catchBody = $logStatement . $catchBody;
                    $fixes++;
                }
            }
            
            return "catch($catchSignature) {$catchBody}";
        }, $content);
        
        if ($fixes > 0) {
            $this->totalFixes += $fixes;
            $this->log("Applied $fixes error logging fixes in $filePath");
        }
        
        return $content;
    }
    
    /**
     * Get specific PHP files that need fixing
     */
    private function getPhpFiles(): array {
        $files = [];
        $patterns = [
            $this->config['app_dir'] . '/src/Core/*.php',
            $this->config['app_dir'] . '/src/Controllers/*.php',
            $this->config['app_dir'] . '/src/Models/*.php',
            $this->config['app_dir'] . '/src/Views/*.php',
            $this->config['app_dir'] . '/config/*.php',
            $this->config['app_dir'] . '/public/*.php',
            $this->config['app_dir'] . '/bootstrap.php'
        ];
        
        foreach ($patterns as $pattern) {
            $matches = glob($pattern);
            if ($matches) {
                foreach ($matches as $file) {
                    // Skip test files and fix scripts
                    if (strpos(basename($file), 'test_') === 0 ||
                        strpos($file, 'fix_') !== false ||
                        strpos($file, 'php_error_resolution.php') !== false) {
                        continue;
                    }
                    
                    if (!in_array($file, $files)) {
                        $files[] = $file;
                    }
                }
            }
        }
        
        return $files;
    }
    
    /**
     * Log messages
     */
    private function log(string $message): void {
        $logMessage = date('Y-m-d H:i:s') . ' - ' . $message . "\n";
        file_put_contents($this->config['log_file'], $logMessage, FILE_APPEND);
    }
}

// Run the syntax error fixer
$fixer = new SyntaxErrorFixer();
$fixer->run();
