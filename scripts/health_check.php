<?php

namespace RenalTales\Scripts;

/**
 * Health Check Script for VS Code Problems
 * Checks for common issues and validates system integrity
 */

require_once __DIR__ . '/bootstrap.php';

class HealthChecker {
    private array $errors = [];
    private array $warnings = [];
    private array $info = [];
    
    public function runAll(): void {
        echo "🔍 Running comprehensive health check...\n\n";
        
        $this->checkPhpSyntax();
        $this->checkFilePermissions();
        $this->checkRequiredFiles();
        $this->checkDatabaseConfig();
        $this->checkSecurityConfig();
        $this->checkEnvironmentFiles();
        $this->checkDirectoryStructure();
        
        $this->displayResults();
    }
    
    private function checkPhpSyntax(): void {
        echo "📋 Checking PHP syntax...\n";
        
        $phpFiles = glob(__DIR__ . '/{*.php,*/*.php,*/*/*.php,*/*/*/*.php}', GLOB_BRACE);
        
        foreach ($phpFiles as $file) {
            $output = [];
            $returnCode = 0;
            exec("php -l \"$file\" 2>&1", $output, $returnCode);
            
            if ($returnCode !== 0) {
                $this->errors[] = "PHP Syntax Error in $file: " . implode("\n", $output);
            }
        }
        
        if (empty($this->errors)) {
            $this->info[] = "✅ All PHP files have valid syntax";
        }
    }
    
    private function checkFilePermissions(): void {
        echo "🔐 Checking file permissions...\n";
        
        $criticalDirs = [
            __DIR__ . '/storage',
            __DIR__ . '/storage/uploads',
            __DIR__ . '/storage/logs'
        ];
        
        foreach ($criticalDirs as $dir) {
            if (is_dir($dir)) {
                if (!is_writable($dir)) {
                    $this->warnings[] = "Directory not writable: $dir";
                }
            } else {
                $this->warnings[] = "Missing directory: $dir";
            }
        }
        
        // Check sensitive files are not world-readable
        $sensitiveFiles = [
            __DIR__ . '/.env',
            __DIR__ . '/.env.production',
            __DIR__ . '/.env.development'
        ];
        
        foreach ($sensitiveFiles as $file) {
            if (file_exists($file)) {
                $perms = fileperms($file);
                if (($perms & 0044) !== 0) { // Check if others can read
                    $this->warnings[] = "File may be world-readable: $file";
                }
            }
        }
    }
    
    private function checkRequiredFiles(): void {
        echo "📁 Checking required files...\n";
        
        $requiredFiles = [
            'core/Database.php',
            'core/AuthenticationManager.php',
            'core/SessionManager.php',
            'core/SecurityManager.php',
            'core/AdminSecurityManager.php',
            'core/SessionRegenerationManager.php',
            'models/AdminSession.php',
            'models/SecurityEvent.php',
            'controllers/ApplicationController.php',
            'controllers/BaseController.php',
            'views/ApplicationView.php'
        ];
        
        foreach ($requiredFiles as $file) {
            $fullPath = __DIR__ . '/' . $file;
            if (!file_exists($fullPath)) {
                $this->errors[] = "Missing required file: $file";
            }
        }
        
        if (count($this->errors) === 0) {
            $this->info[] = "✅ All required files are present";
        }
    }
    
    private function checkDatabaseConfig(): void {
        echo "🗄️ Checking database configuration...\n";
        
        // Check if environment variables are set
        $dbVars = ['DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'];
        $missingVars = [];
        
        foreach ($dbVars as $var) {
            if (empty($_ENV[$var])) {
                $missingVars[] = $var;
            }
        }
        
        if (!empty($missingVars)) {
            $this->warnings[] = "Missing database environment variables: " . implode(', ', $missingVars);
            $this->info[] = "ℹ️ This is expected in development - use .env.development or .env.production";
        }
        
        // Check database connection if variables are set
        if (empty($missingVars)) {
            try {
                if (class_exists('Database')) {
                    $db = new Database();
                    $this->info[] = "✅ Database connection successful";
                } else {
                    $this->warnings[] = "Database class not available for connection test";
                }
            } catch (Exception $e) {
                $this->warnings[] = "Database connection failed: " . $e->getMessage();
            }
        }
    }
    
    private function checkSecurityConfig(): void {
        echo "🛡️ Checking security configuration...\n";
        
        // Check for hardcoded credentials
        $configFiles = [
            'config/database.php',
            'config/app.php',
            'config/environments/production.php',
            'config/environments/development.php'
        ];
        
        $patterns = [
            '/["\']password["\']\s*=>\s*["\'][^"\']+["\']/',
            '/["\']host["\']\s*=>\s*["\'][^"\']+\./',
            '/["\']username["\']\s*=>\s*["\'][a-zA-Z0-9]+["\']/',
            '/DB_PASSWORD\s*=\s*["\'][^"\']+["\']/'
        ];
        
        foreach ($configFiles as $file) {
            $fullPath = __DIR__ . '/' . $file;
            if (file_exists($fullPath)) {
                $content = file_get_contents($fullPath);
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $content)) {
                        $this->warnings[] = "Potential hardcoded credential in: $file";
                        break;
                    }
                }
            }
        }
        
        // Check security headers implementation
        if (class_exists('ApplicationController')) {
            $this->info[] = "✅ Security headers implementation available";
        }
    }
    
    private function checkEnvironmentFiles(): void {
        echo "🌍 Checking environment files...\n";
        
        $envFiles = ['.env', '.env.production', '.env.development'];
        
        foreach ($envFiles as $file) {
            $fullPath = __DIR__ . '/' . $file;
            if (file_exists($fullPath)) {
                $this->info[] = "✅ Found: $file";
                
                // Check if it has required structure
                $content = file_get_contents($fullPath);
                if (!str_contains($content, 'DB_HOST') || !str_contains($content, 'DB_DATABASE')) {
                    $this->warnings[] = "Environment file $file may be incomplete";
                }
            } else {
                $this->warnings[] = "Missing environment file: $file";
            }
        }
        
        // Check .gitignore excludes .env files
        $gitignorePath = __DIR__ . '/.gitignore';
        if (file_exists($gitignorePath)) {
            $gitignoreContent = file_get_contents($gitignorePath);
            if (!str_contains($gitignoreContent, '.env')) {
                $this->warnings[] = ".gitignore should exclude .env files";
            } else {
                $this->info[] = "✅ .gitignore properly excludes .env files";
            }
        }
    }
    
    private function checkDirectoryStructure(): void {
        echo "📂 Checking directory structure...\n";
        
        $requiredDirs = [
            'core', 'models', 'views', 'controllers', 'config', 
            'resources', 'storage', 'uploads', 'logs', 'database'
        ];
        
        foreach ($requiredDirs as $dir) {
            $fullPath = __DIR__ . '/' . $dir;
            if (!is_dir($fullPath)) {
                $this->warnings[] = "Missing directory: $dir";
            }
        }
        
        if (count($this->warnings) === 0) {
            $this->info[] = "✅ Directory structure is complete";
        }
    }
    
    private function displayResults(): void {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "📊 HEALTH CHECK RESULTS\n";
        echo str_repeat("=", 60) . "\n\n";
        
        if (!empty($this->errors)) {
            echo "🔴 ERRORS (" . count($this->errors) . "):\n";
            foreach ($this->errors as $error) {
                echo "   - $error\n";
            }
            echo "\n";
        }
        
        if (!empty($this->warnings)) {
            echo "🟡 WARNINGS (" . count($this->warnings) . "):\n";
            foreach ($this->warnings as $warning) {
                echo "   - $warning\n";
            }
            echo "\n";
        }
        
        if (!empty($this->info)) {
            echo "🟢 STATUS (" . count($this->info) . "):\n";
            foreach ($this->info as $info) {
                echo "   - $info\n";
            }
            echo "\n";
        }
        
        // Overall status
        if (empty($this->errors)) {
            if (empty($this->warnings)) {
                echo "🎉 OVERALL STATUS: EXCELLENT\n";
            } else {
                echo "✅ OVERALL STATUS: GOOD (minor warnings)\n";
            }
        } else {
            echo "❌ OVERALL STATUS: NEEDS ATTENTION\n";
        }
        
        echo "\nHealth check completed at: " . date('Y-m-d H:i:s') . "\n";
    }
}

// Run health check if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $checker = new HealthChecker();
    $checker->runAll();
}
