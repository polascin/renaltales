<?php

namespace RenalTales;

/**
 * PHP Issue Fix Script
 * 
 * This script automatically fixes PHP 8 compatibility issues, undefined variables,
 * and error handling problems identified in the codebase.
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

declare(strict_types=1);

class PHPIssueFixer {
    private array $config;
    private array $fixed = [];
    private int $totalFixes = 0;
    
    public function __construct() {
        $this->config = [
            'app_dir' => dirname(__FILE__),
            'excluded_dirs' => ['vendor', 'node_modules', '.git', 'cache', 'storage'],
            'log_file' => 'php_fixes.log',
            'backup_dir' => 'backups/' . date('Y-m-d_H-i-s')
        ];
        
        // Create backup directory
        if (!is_dir($this->config['backup_dir'])) {
            mkdir($this->config['backup_dir'], 0755, true);
        }
        
        $this->log("PHP Issue Fixer started");
    }
    
    public function run(): void {
        echo "=== PHP Issue Fixer ===\n";
        echo "Creating backups in: " . $this->config['backup_dir'] . "\n";
        echo "Starting fixes...\n\n";
        
        $files = $this->getPhpFiles();
        
        foreach ($files as $file) {
            echo "Processing: " . basename($file) . "\n";
            $this->fixFile($file);
        }
        
        echo "\n=== Fix Complete ===\n";
        echo "Total fixes applied: " . $this->totalFixes . "\n";
        echo "Files modified: " . count($this->fixed) . "\n";
        echo "Log file: " . $this->config['log_file'] . "\n";
    }
    
    private function fixFile(string $filePath): void {
        $content = file_get_contents($filePath);
        $originalContent = $content;
        
        // Create backup
        $backupPath = $this->config['backup_dir'] . '/' . basename($filePath);
        file_put_contents($backupPath, $originalContent);
        
        // Apply fixes
        $content = $this->fixArrayAccess($content, $filePath);
        $content = $this->fixObjectAccess($content, $filePath);
        $content = $this->fixSuperglobalAccess($content, $filePath);
        $content = $this->fixErrorHandling($content, $filePath);
        $content = $this->fixUnionTypes($content, $filePath);
        $content = $this->addErrorLogging($content, $filePath);
        
        // Save if modified
        if ($content !== $originalContent) {
            file_put_contents($filePath, $content);
            $this->fixed[] = $filePath;
            $this->log("Fixed file: $filePath");
        }
    }
    
    /**
     * Fix array access with null coalescing operator
     */
    private function fixArrayAccess(string $content, string $filePath): string {
        $fixes = 0;
        
        // Fix $_ENV, $_SERVER, $_GET, $_POST, $_SESSION access
        $patterns = [
            // $_ENV['key'] -> ($_ENV['key'] ?? null)
            '/\$_ENV\[([\'"]?)([^\'"\]]+)\1\](?!\s*\?\?)/' => '($_ENV[$1$2$1] ?? null)',
            '/\$_SERVER\[([\'"]?)([^\'"\]]+)\1\](?!\s*\?\?)/' => '($_SERVER[$1$2$1] ?? null)',
            '/\$_GET\[([\'"]?)([^\'"\]]+)\1\](?!\s*\?\?)/' => '($_GET[$1$2$1] ?? null)',
            '/\$_POST\[([\'"]?)([^\'"\]]+)\1\](?!\s*\?\?)/' => '($_POST[$1$2$1] ?? null)',
            '/\$_SESSION\[([\'"]?)([^\'"\]]+)\1\](?!\s*\?\?)/' => '($_SESSION[$1$2$1] ?? null)',
            '/\$_COOKIE\[([\'"]?)([^\'"\]]+)\1\](?!\s*\?\?)/' => '($_COOKIE[$1$2$1] ?? null)',
            '/\$_FILES\[([\'"]?)([^\'"\]]+)\1\](?!\s*\?\?)/' => '($_FILES[$1$2$1] ?? null)',
        ];
        
        foreach ($patterns as $pattern => $replacement) {
            $newContent = preg_replace($pattern, $replacement, $content);
            if ($newContent !== $content) {
                $matches = preg_match_all($pattern, $content);
                $fixes += $matches;
                $content = $newContent;
            }
        }
        
        // Fix regular array access patterns
        $arrayPatterns = [
            // $array['key'] -> ($array['key'] ?? null) - but be careful not to double-fix
            '/\$([a-zA-Z_][a-zA-Z0-9_]*)\[([\'"]?)([^\'"\]]+)\2\](?!\s*\?\?)(?!\s*=)/' => '($$$1[$2$3$2] ?? null)',
        ];
        
        foreach ($arrayPatterns as $pattern => $replacement) {
            // Only apply to specific contexts to avoid over-fixing
            if (strpos($content, '??') === false || preg_match('/\$[a-zA-Z_][a-zA-Z0-9_]*\[[^\]]+\](?!\s*\?\?)/', $content)) {
                $newContent = preg_replace($pattern, $replacement, $content);
                if ($newContent !== $content) {
                    $matches = preg_match_all($pattern, $content);
                    $fixes += $matches;
                    $content = $newContent;
                }
            }
        }
        
        if ($fixes > 0) {
            $this->totalFixes += $fixes;
            $this->log("Applied $fixes array access fixes in $filePath");
        }
        
        return $content;
    }
    
    /**
     * Fix object property access with null safety
     */
    private function fixObjectAccess(string $content, string $filePath): string {
        $fixes = 0;
        
        // Fix object property access: $obj->prop -> ($obj?->prop ?? null)
        $patterns = [
            '/\$([a-zA-Z_][a-zA-Z0-9_]*)->([a-zA-Z_][a-zA-Z0-9_]*)\(([^)]*)\)(?!\s*\?\?)/' => '($$$1?->$2($3) ?? null)',
            '/\$([a-zA-Z_][a-zA-Z0-9_]*)->([a-zA-Z_][a-zA-Z0-9_]*)(?!\s*\?\?)(?!\s*=)/' => '($$$1?->$2 ?? null)',
        ];
        
        foreach ($patterns as $pattern => $replacement) {
            // Only apply if PHP 8+ nullsafe operator is available
            if (version_compare(PHP_VERSION, '8.0.0', '>=')) {
                $newContent = preg_replace($pattern, $replacement, $content);
                if ($newContent !== $content) {
                    $matches = preg_match_all($pattern, $content);
                    $fixes += $matches;
                    $content = $newContent;
                }
            } else {
                // For PHP < 8.0, use isset() checks
                $legacyPattern = '/\$([a-zA-Z_][a-zA-Z0-9_]*)->([a-zA-Z_][a-zA-Z0-9_]*)(?!\s*\?\?)(?!\s*=)/';
                $legacyReplacement = '(isset($$$1) ? $$$1->$2 : null)';
                $newContent = preg_replace($legacyPattern, $legacyReplacement, $content);
                if ($newContent !== $content) {
                    $matches = preg_match_all($legacyPattern, $content);
                    $fixes += $matches;
                    $content = $newContent;
                }
            }
        }
        
        if ($fixes > 0) {
            $this->totalFixes += $fixes;
            $this->log("Applied $fixes object access fixes in $filePath");
        }
        
        return $content;
    }
    
    /**
     * Fix superglobal access patterns
     */
    private function fixSuperglobalAccess(string $content, string $filePath): string {
        $fixes = 0;
        
        // More specific superglobal fixes
        $patterns = [
            // isset($_GET['key']) ? $_GET['key'] : default -> $_GET['key'] ?? default
            '/isset\(\$_(GET|POST|SESSION|COOKIE|SERVER|ENV)\[([\'"])([^\'"]+)\2\]\)\s*\?\s*\$_\1\[\2\3\2\]\s*:\s*([^;]+)/' => '$_$1[$2$3$2] ?? $4',
            
            // !empty($_GET['key']) ? $_GET['key'] : default -> $_GET['key'] ?? default
            '/!empty\(\$_(GET|POST|SESSION|COOKIE|SERVER|ENV)\[([\'"])([^\'"]+)\2\]\)\s*\?\s*\$_\1\[\2\3\2\]\s*:\s*([^;]+)/' => '$_$1[$2$3$2] ?? $4',
        ];
        
        foreach ($patterns as $pattern => $replacement) {
            $newContent = preg_replace($pattern, $replacement, $content);
            if ($newContent !== $content) {
                $matches = preg_match_all($pattern, $content);
                $fixes += $matches;
                $content = $newContent;
            }
        }
        
        if ($fixes > 0) {
            $this->totalFixes += $fixes;
            $this->log("Applied $fixes superglobal access fixes in $filePath");
        }
        
        return $content;
    }
    
    /**
     * Fix error handling - add catch blocks to try statements
     */
    private function fixErrorHandling(string $content, string $filePath): string {
        $fixes = 0;
        
        // Find try blocks without catch
        if (preg_match_all('/try\s*\{[^}]*\}(?!\s*catch)/s', $content, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $match) {
                $tryBlock = $match[0];
                $offset = $match[1];
                
                // Add a generic catch block
                $catchBlock = " catch (Exception \$e) {\n    error_log('Error in " . basename($filePath) . ": ' . \$e->getMessage());\n    throw \$e;\n}";
                
                $content = substr_replace($content, $tryBlock . $catchBlock, $offset, strlen($tryBlock));
                $fixes++;
            }
        }
        
        if ($fixes > 0) {
            $this->totalFixes += $fixes;
            $this->log("Applied $fixes error handling fixes in $filePath");
        }
        
        return $content;
    }
    
    /**
     * Fix union types for compatibility
     */
    private function fixUnionTypes(string $content, string $filePath): string {
        $fixes = 0;
        
        // Check if using union types with older PHP
        if (version_compare(PHP_VERSION, '8.0.0', '<')) {
            // Replace union types with mixed for older PHP versions
            $patterns = [
                '/:\s*([a-zA-Z_][a-zA-Z0-9_]*)\s*\|\s*([a-zA-Z_][a-zA-Z0-9_]*)\s*\|/' => ': mixed',
                '/:\s*([a-zA-Z_][a-zA-Z0-9_]*)\s*\|\s*([a-zA-Z_][a-zA-Z0-9_]*)/' => ': mixed',
            ];
            
            foreach ($patterns as $pattern => $replacement) {
                $newContent = preg_replace($pattern, $replacement, $content);
                if ($newContent !== $content) {
                    $matches = preg_match_all($pattern, $content);
                    $fixes += $matches;
                    $content = $newContent;
                }
            }
        }
        
        if ($fixes > 0) {
            $this->totalFixes += $fixes;
            $this->log("Applied $fixes union type fixes in $filePath");
        }
        
        return $content;
    }
    
    /**
     * Add error logging to exception handling
     */
    private function addErrorLogging(string $content, string $filePath): string {
        $fixes = 0;
        
        // Find catch blocks without error_log
        if (preg_match_all('/catch\s*\([^)]+\)\s*\{([^}]*)\}/s', $content, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $index => $match) {
                $catchBlock = $match[0];
                $catchContent = $matches[1][$index][0];
                $offset = $match[1];
                
                // Check if error_log is already present
                if (strpos($catchContent, 'error_log') === false) {
                    // Extract exception variable name
                    preg_match('/catch\s*\([^)]*\$([a-zA-Z_][a-zA-Z0-9_]*)\)/', $catchBlock, $varMatches);
                    $exceptionVar = $varMatches[1] ?? 'e';
                    
                    // Add error logging
                    $logStatement = "\n    error_log('Exception in " . basename($filePath) . ": ' . \$" . $exceptionVar . "->getMessage());";
                    
                    // Insert after the opening brace
                    $openBracePos = strpos($catchBlock, '{');
                    $newCatchBlock = substr($catchBlock, 0, $openBracePos + 1) . $logStatement . substr($catchBlock, $openBracePos + 1);
                    
                    $content = substr_replace($content, $newCatchBlock, $offset, strlen($catchBlock));
                    $fixes++;
                }
            }
        }
        
        if ($fixes > 0) {
            $this->totalFixes += $fixes;
            $this->log("Applied $fixes error logging fixes in $filePath");
        }
        
        return $content;
    }
    
    /**
     * Get all PHP files in the project
     */
    private function getPhpFiles(): array {
        $files = [];
        
        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($this->config['app_dir'], RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CATCH_GET_CHILD
            );
            
            foreach ($iterator as $file) {
                try {
                    if ($file->isFile() && $file->getExtension() === 'php') {
                        $filePath = $file->getPathname();
                        
                        // Skip excluded directories and the current script
                        $skip = false;
                        foreach ($this->config['excluded_dirs'] as $excludedDir) {
                            if (strpos($filePath, $excludedDir) !== false) {
                                $skip = true;
                                break;
                            }
                        }
                        
                        // Skip the current fix script and test files
                        if (strpos($filePath, 'fix_php_issues.php') !== false ||
                            strpos($filePath, 'php_error_resolution.php') !== false ||
                            strpos($filePath, 'test_') === 0 ||
                            strpos(basename($filePath), 'test_') === 0) {
                            $skip = true;
                        }
                        
                        if (!$skip) {
                            $files[] = $filePath;
                        }
                    }
                } catch (Exception $e) {
                    $this->log("Skipping file due to access error: " . $e->getMessage());
                    continue;
                }
            }
        } catch (Exception $e) {
            $this->log("Error scanning directory: " . $e->getMessage());
            // Fallback to glob method
            $files = $this->getPhpFilesGlob();
        }
        
        return $files;
    }
    
    /**
     * Fallback method to get PHP files using glob
     */
    private function getPhpFilesGlob(): array {
        $files = [];
        $patterns = [
            $this->config['app_dir'] . '/src/*.php',
            $this->config['app_dir'] . '/src/*/*.php',
            $this->config['app_dir'] . '/src/*/*/*.php',
            $this->config['app_dir'] . '/config/*.php',
            $this->config['app_dir'] . '/public/*.php',
            $this->config['app_dir'] . '/*.php'
        ];
        
        foreach ($patterns as $pattern) {
            $matches = glob($pattern);
            if ($matches) {
                foreach ($matches as $file) {
                    // Skip excluded directories and fix scripts
                    $skip = false;
                    foreach ($this->config['excluded_dirs'] as $excludedDir) {
                        if (strpos($file, $excludedDir) !== false) {
                            $skip = true;
                            break;
                        }
                    }
                    
                    if (strpos($file, 'fix_php_issues.php') !== false ||
                        strpos($file, 'php_error_resolution.php') !== false ||
                        strpos(basename($file), 'test_') === 0) {
                        $skip = true;
                    }
                    
                    if (!$skip && !in_array($file, $files)) {
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

// Run the issue fixer
$fixer = new PHPIssueFixer();
$fixer->run();
