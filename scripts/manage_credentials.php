#!/usr/bin/env php
<?php
/**
 * Database Credential Management Script
 * 
 * This script helps manage database credentials securely across environments.
 * Run this after rotating database passwords.
 */

require_once __DIR__ . '/bootstrap.php';

class CredentialManager
{
    private const ENV_FILES = [
        'development' => '.env.development',
        'production' => '.env.production',
        'main' => '.env'
    ];
    
    private string $rootPath;
    
    public function __construct()
    {
        $this->rootPath = dirname(__FILE__);
    }
    
    /**
     * Test database connectivity with current credentials
     */
    public function testDatabaseConnection(string $environment = 'main'): bool
    {
        try {
            echo "🔍 Testing database connection for {$environment} environment...\n";
            
            // Load environment file
            $this->loadEnvironmentFile($environment);
            
            $config = [
                'host' => $_ENV['DB_HOST'] ?? 'localhost',
                'port' => $_ENV['DB_PORT'] ?? '3306',
                'database' => $_ENV['DB_DATABASE'] ?? '',
                'username' => $_ENV['DB_USERNAME'] ?? '',
                'password' => $_ENV['DB_PASSWORD'] ?? '',
                'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4'
            ];
            
            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
            
            $pdo = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 10
            ]);
            
            // Test with a simple query
            $stmt = $pdo->query('SELECT 1 as test');
            $result = $stmt->fetch();
            
            if ($result['test'] === 1) {
                echo "✅ Database connection successful!\n";
                echo "   Host: {$config['host']}\n";
                echo "   Database: {$config['database']}\n";
                echo "   Username: {$config['username']}\n";
                return true;
            }
            
        } catch (Exception $e) {
            echo "❌ Database connection failed!\n";
            echo "   Error: " . $e->getMessage() . "\n";
            return false;
        }
        
        return false;
    }
    
    /**
     * Load environment file
     */
    private function loadEnvironmentFile(string $environment): void
    {
        $envFile = $this->rootPath . '/' . self::ENV_FILES[$environment];
        
        if (!file_exists($envFile)) {
            throw new RuntimeException("Environment file not found: {$envFile}");
        }
        
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            if (strpos($line, '#') === 0 || strpos($line, '=') === false) {
                continue;
            }
            
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, '"\'');
            
            $_ENV[$key] = $value;
        }
    }
    
    /**
     * Validate environment files exist and have required variables
     */
    public function validateEnvironmentFiles(): bool
    {
        $requiredVars = ['DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'];
        $allValid = true;
        
        foreach (self::ENV_FILES as $env => $file) {
            $filePath = $this->rootPath . '/' . $file;
            
            echo "🔍 Validating {$env} environment file: {$file}\n";
            
            if (!file_exists($filePath)) {
                echo "❌ File missing: {$filePath}\n";
                $allValid = false;
                continue;
            }
            
            $content = file_get_contents($filePath);
            $missingVars = [];
            
            foreach ($requiredVars as $var) {
                if (strpos($content, $var . '=') === false) {
                    $missingVars[] = $var;
                }
            }
            
            if (!empty($missingVars)) {
                echo "❌ Missing variables: " . implode(', ', $missingVars) . "\n";
                $allValid = false;
            } else {
                echo "✅ All required variables present\n";
            }
        }
        
        return $allValid;
    }
    
    /**
     * Check for hardcoded credentials in configuration files
     */
    public function scanForHardcodedCredentials(): bool
    {
        $configFiles = [
            'config/environments/production.php',
            'config/environments/development.php',
            'config/database.php'
        ];
        
        $suspiciousPatterns = [
            'mariadb114.r6.websupport.sk',
            'SvwfeoXW',
            'by80b9pH',
            'WsVZOl#'
        ];
        
        $foundCredentials = false;
        
        echo "🔍 Scanning configuration files for hardcoded credentials...\n";
        
        foreach ($configFiles as $file) {
            $filePath = $this->rootPath . '/' . $file;
            
            if (!file_exists($filePath)) {
                continue;
            }
            
            $content = file_get_contents($filePath);
            
            foreach ($suspiciousPatterns as $pattern) {
                if (strpos($content, $pattern) !== false) {
                    echo "❌ Found hardcoded credential in {$file}: {$pattern}\n";
                    $foundCredentials = true;
                }
            }
        }
        
        if (!$foundCredentials) {
            echo "✅ No hardcoded credentials found in configuration files\n";
        }
        
        return !$foundCredentials;
    }
    
    /**
     * Display security status report
     */
    public function generateSecurityReport(): void
    {
        echo "\n=== SECURITY STATUS REPORT ===\n\n";
        
        $envFilesValid = $this->validateEnvironmentFiles();
        $noHardcodedCreds = $this->scanForHardcodedCredentials();
        
        echo "\n=== DATABASE CONNECTIVITY TESTS ===\n\n";
        
        foreach (['development', 'production', 'main'] as $env) {
            $this->testDatabaseConnection($env);
            echo "\n";
        }
        
        echo "=== OVERALL SECURITY STATUS ===\n\n";
        
        if ($envFilesValid && $noHardcodedCreds) {
            echo "🟢 SECURITY STATUS: SECURE\n";
            echo "✅ Environment files configured properly\n";
            echo "✅ No hardcoded credentials found\n";
        } else {
            echo "🔴 SECURITY STATUS: NEEDS ATTENTION\n";
            if (!$envFilesValid) echo "❌ Environment files need configuration\n";
            if (!$noHardcodedCreds) echo "❌ Hardcoded credentials found\n";
        }
    }
}

// Main execution
if ($argc < 2) {
    echo "Database Credential Management Tool\n\n";
    echo "Usage: php manage_credentials.php [command]\n\n";
    echo "Commands:\n";
    echo "  test [env]     Test database connection (env: development|production|main)\n";
    echo "  validate       Validate environment files\n";
    echo "  scan           Scan for hardcoded credentials\n";
    echo "  report         Generate full security report\n\n";
    exit(1);
}

$manager = new CredentialManager();
$command = $argv[1];

switch ($command) {
    case 'test':
        $environment = $argv[2] ?? 'main';
        $manager->testDatabaseConnection($environment);
        break;
        
    case 'validate':
        $manager->validateEnvironmentFiles();
        break;
        
    case 'scan':
        $manager->scanForHardcodedCredentials();
        break;
        
    case 'report':
        $manager->generateSecurityReport();
        break;
        
    default:
        echo "Unknown command: {$command}\n";
        exit(1);
}
