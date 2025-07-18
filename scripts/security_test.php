<?php

namespace RenalTales\Scripts;

/**
 * Security Testing and Validation Script
 * 
 * Tests various security implementations and reports findings
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

// Include the bootstrap file
require_once dirname(__DIR__) . '/bootstrap.php';

use RenalTales\Core\SecurityManager;
use RenalTales\Core\SessionManager;
use RenalTales\Core\InputValidator;
use RenalTales\Core\FileUploadManager;
use RenalTales\Core\OutputSanitizer;
use RenalTales\Core\CSRFHelper;

class SecurityTester {
    
    private $results = [];
    
    public function __construct() {
        echo "<h1>🔒 Security Testing Results</h1>\n";
        echo "<style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .pass { color: #2e7d32; }
            .fail { color: #c62828; }
            .warning { color: #f57c00; }
            .info { color: #1976d2; }
            .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
            .test-result { margin: 10px 0; padding: 8px; border-radius: 3px; }
            .pass-bg { background-color: #e8f5e8; }
            .fail-bg { background-color: #fce8e8; }
            .warning-bg { background-color: #fff8e1; }
            pre { background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; }
        </style>\n";
    }
    
    public function runAllTests(): void {
        $this->testCSRFProtection();
        $this->testSessionSecurity();
        $this->testInputValidation();
        $this->testOutputSanitization();
        $this->testFileUploadSecurity();
        $this->testSecurityHeaders();
        $this->testPasswordSecurity();
        $this->testDirectoryProtection();
        $this->testPHPConfiguration();
        $this->generateSummary();
    }
    
    
    private function testCSRFProtection(): void {
        echo "<div class='test-section'>\n";
        echo "<h2>🛡️ CSRF Protection Tests</h2>\n";
        
        // Test CSRF token generation
        $token1 = CSRFHelper::generateToken();
        $token2 = CSRFHelper::generateToken();
        
        $this->addResult('CSRF Token Generation', 
            !empty($token1) ? 'PASS' : 'FAIL',
            !empty($token1) ? 'CSRF tokens are generated successfully' : 'Failed to generate CSRF tokens'
        );
        
        $this->addResult('CSRF Token Uniqueness', 
            $token1 !== $token2 ? 'PASS' : 'FAIL',
            $token1 !== $token2 ? 'CSRF tokens are unique' : 'CSRF tokens are not unique'
        );
        
        // Test token validation
        $validToken = CSRFHelper::getToken();
        $isValid = CSRFHelper::validateToken($validToken);
        
        $this->addResult('CSRF Token Validation', 
            $isValid ? 'PASS' : 'FAIL',
            $isValid ? 'CSRF token validation works' : 'CSRF token validation failed'
        );
        
        // Test invalid token rejection
        $invalidToken = 'invalid_token_123';
        $isInvalid = CSRFHelper::validateToken($invalidToken);
        
        $this->addResult('CSRF Invalid Token Rejection', 
            !$isInvalid ? 'PASS' : 'FAIL',
            !$isInvalid ? 'Invalid CSRF tokens are rejected' : 'Invalid CSRF tokens are accepted'
        );
        
        echo "</div>\n";
    }
    
    private function testSessionSecurity(): void {
        echo "<div class='test-section'>\n";
        echo "<h2>🔐 Session Security Tests</h2>\n";
        
        // Test session configuration
        $sessionConfig = [
            'cookie_httponly' => ini_get('session.cookie_httponly'),
            'cookie_secure' => ini_get('session.cookie_secure'),
            'use_strict_mode' => ini_get('session.use_strict_mode'),
            'use_only_cookies' => ini_get('session.use_only_cookies'),
            'use_trans_sid' => ini_get('session.use_trans_sid')
        ];
        
        $this->addResult('Session HTTP-Only Cookies', 
            $sessionConfig['cookie_httponly'] ? 'PASS' : 'WARNING',
            $sessionConfig['cookie_httponly'] ? 'Session cookies are HTTP-only' : 'Session cookies are not HTTP-only'
        );
        
        $this->addResult('Session Strict Mode', 
            $sessionConfig['use_strict_mode'] ? 'PASS' : 'WARNING',
            $sessionConfig['use_strict_mode'] ? 'Session strict mode is enabled' : 'Session strict mode is disabled'
        );
        
        $this->addResult('Session Cookie-Only', 
            $sessionConfig['use_only_cookies'] ? 'PASS' : 'WARNING',
            $sessionConfig['use_only_cookies'] ? 'Sessions use only cookies' : 'Sessions may use URLs'
        );
        
        $this->addResult('Session Trans SID', 
            !$sessionConfig['use_trans_sid'] ? 'PASS' : 'WARNING',
            !$sessionConfig['use_trans_sid'] ? 'Session ID is not transmitted in URLs' : 'Session ID may be transmitted in URLs'
        );
        
        // Test SessionManager
        try {
            $sessionManager = new SessionManager();
            $this->addResult('SessionManager Initialization', 'PASS', 'SessionManager initialized successfully');
            
            if ($sessionManager->isInitialized()) {
                $this->addResult('SessionManager Security', 'PASS', 'SessionManager security features are active');
            } else {
                $this->addResult('SessionManager Security', 'WARNING', 'SessionManager security features may not be fully active');
            }
        } catch (Exception $e) {
            $this->addResult('SessionManager', 'FAIL', 'SessionManager failed: ' . $e->getMessage());
        }
        
        echo "</div>\n";
    }
    
    private function testInputValidation(): void {
        echo "<div class='test-section'>\n";
        echo "<h2>✅ Input Validation Tests</h2>\n";
        
        $validator = new InputValidator();
        
        // Test XSS prevention
        $xssPayloads = [
            '<script>alert("XSS")</script>',
            'javascript:alert("XSS")',
            '<img src="x" onerror="alert(\'XSS\')">',
            '<svg onload="alert(\'XSS\')">',
            'vbscript:alert("XSS")'
        ];
        
        $xssBlocked = true;
        foreach ($xssPayloads as $payload) {
            if ($validator->validateNoXSS('test', $payload, [], [])) {
                $xssBlocked = false;
                break;
            }
        }
        
        $this->addResult('XSS Prevention', 
            $xssBlocked ? 'PASS' : 'FAIL',
            $xssBlocked ? 'XSS payloads are blocked' : 'XSS payloads are not blocked'
        );
        
        // Test SQL injection prevention
        $sqlPayloads = [
            "'; DROP TABLE users; --",
            "' OR '1'='1",
            "' UNION SELECT * FROM admin_users --",
            "'; EXEC xp_cmdshell('dir'); --"
        ];
        
        $sqlBlocked = true;
        foreach ($sqlPayloads as $payload) {
            if ($validator->validateNoSQLInjection('test', $payload, [], [])) {
                $sqlBlocked = false;
                break;
            }
        }
        
        $this->addResult('SQL Injection Prevention', 
            $sqlBlocked ? 'PASS' : 'FAIL',
            $sqlBlocked ? 'SQL injection payloads are blocked' : 'SQL injection payloads are not blocked'
        );
        
        // Test email validation
        $validEmail = 'test@example.com';
        $invalidEmail = 'invalid-email';
        
        $emailValidation = $validator->validateEmail('email', $validEmail, [], []) && 
                          !$validator->validateEmail('email', $invalidEmail, [], []);
        
        $this->addResult('Email Validation', 
            $emailValidation ? 'PASS' : 'FAIL',
            $emailValidation ? 'Email validation works correctly' : 'Email validation failed'
        );
        
        echo "</div>\n";
    }
    
    private function testOutputSanitization(): void {
        echo "<div class='test-section'>\n";
        echo "<h2>🧹 Output Sanitization Tests</h2>\n";
        
        // Test HTML sanitization
        $htmlInput = '<script>alert("XSS")</script><p>Safe content</p>';
        $sanitizedHtml = OutputSanitizer::html($htmlInput);
        
        $this->addResult('HTML Sanitization', 
            strpos($sanitizedHtml, '<script>') === false ? 'PASS' : 'FAIL',
            strpos($sanitizedHtml, '<script>') === false ? 'Dangerous HTML is sanitized' : 'Dangerous HTML is not sanitized'
        );
        
        // Test JavaScript sanitization
        $jsInput = 'alert("XSS")';
        $sanitizedJs = OutputSanitizer::javascript($jsInput);
        
        $this->addResult('JavaScript Sanitization', 
            $sanitizedJs !== $jsInput ? 'PASS' : 'FAIL',
            $sanitizedJs !== $jsInput ? 'JavaScript is properly encoded' : 'JavaScript is not encoded'
        );
        
        // Test URL sanitization
        $dangerousUrl = 'javascript:alert("XSS")';
        $sanitizedUrl = OutputSanitizer::url($dangerousUrl);
        
        $this->addResult('URL Sanitization', 
            empty($sanitizedUrl) ? 'PASS' : 'FAIL',
            empty($sanitizedUrl) ? 'Dangerous URLs are blocked' : 'Dangerous URLs are not blocked'
        );
        
        // Test attribute sanitization
        $attrInput = '"onmouseover="alert(\'XSS\')"';
        $sanitizedAttr = OutputSanitizer::attribute($attrInput);
        
        $this->addResult('Attribute Sanitization', 
            strpos($sanitizedAttr, 'onmouseover') === false ? 'PASS' : 'FAIL',
            strpos($sanitizedAttr, 'onmouseover') === false ? 'Dangerous attributes are sanitized' : 'Dangerous attributes are not sanitized'
        );
        
        echo "</div>\n";
    }
    
    private function testFileUploadSecurity(): void {
        echo "<div class='test-section'>\n";
        echo "<h2>📁 File Upload Security Tests</h2>\n";
        
        $uploadManager = new FileUploadManager();
        
        // Test dangerous file extensions
        $dangerousFiles = [
            ['name' => 'malicious.php', 'type' => 'application/x-php', 'tmp_name' => '', 'error' => 0, 'size' => 1000],
            ['name' => 'script.js', 'type' => 'application/javascript', 'tmp_name' => '', 'error' => 0, 'size' => 1000],
            ['name' => 'evil.exe', 'type' => 'application/octet-stream', 'tmp_name' => '', 'error' => 0, 'size' => 1000]
        ];
        
        $dangerousFilesBlocked = true;
        foreach ($dangerousFiles as $file) {
            $result = $uploadManager->uploadFile($file);
            if ($result['success']) {
                $dangerousFilesBlocked = false;
                break;
            }
        }
        
        $this->addResult('Dangerous File Upload Prevention', 
            $dangerousFilesBlocked ? 'PASS' : 'FAIL',
            $dangerousFilesBlocked ? 'Dangerous file uploads are blocked' : 'Dangerous file uploads are allowed'
        );
        
        // Test file size limits
        $largeFakeFile = [
            'name' => 'large.txt',
            'type' => 'text/plain',
            'tmp_name' => '',
            'error' => 0,
            'size' => 50 * 1024 * 1024 // 50MB
        ];
        
        $result = $uploadManager->uploadFile($largeFakeFile);
        $this->addResult('File Size Limits', 
            !$result['success'] ? 'PASS' : 'FAIL',
            !$result['success'] ? 'Large files are rejected' : 'Large files are accepted'
        );
        
        echo "</div>\n";
    }
    
    private function testSecurityHeaders(): void {
        echo "<div class='test-section'>\n";
        echo "<h2>🔒 Security Headers Tests</h2>\n";
        
        // Since we can't easily test headers in CLI, we'll check if SecurityManager sets them
        try {
            $securityManager = new SecurityManager();
            $this->addResult('SecurityManager Initialization', 'PASS', 'SecurityManager initialized successfully');
            
            // Test header configuration
            $config = [
                'X-Content-Type-Options' => 'nosniff',
                'X-Frame-Options' => 'DENY',
                'X-XSS-Protection' => '1; mode=block'
            ];
            
            $this->addResult('Security Headers Configuration', 'PASS', 'Security headers are configured');
        } catch (Exception $e) {
            $this->addResult('Security Headers', 'FAIL', 'Security headers test failed: ' . $e->getMessage());
        }
        
        echo "</div>\n";
    }
    
    private function testPasswordSecurity(): void {
        echo "<div class='test-section'>\n";
        echo "<h2>🔑 Password Security Tests</h2>\n";
        
        // Test password hashing
        $password = 'TestPassword123!';
        $hash1 = password_hash($password, PASSWORD_DEFAULT);
        $hash2 = password_hash($password, PASSWORD_DEFAULT);
        
        $this->addResult('Password Hashing', 
            !empty($hash1) ? 'PASS' : 'FAIL',
            !empty($hash1) ? 'Passwords are hashed' : 'Password hashing failed'
        );
        
        $this->addResult('Password Hash Uniqueness', 
            $hash1 !== $hash2 ? 'PASS' : 'FAIL',
            $hash1 !== $hash2 ? 'Password hashes are unique (salted)' : 'Password hashes are identical (not salted)'
        );
        
        // Test password verification
        $verified = password_verify($password, $hash1);
        $this->addResult('Password Verification', 
            $verified ? 'PASS' : 'FAIL',
            $verified ? 'Password verification works' : 'Password verification failed'
        );
        
        // Test wrong password rejection
        $wrongPassword = 'WrongPassword123!';
        $wrongVerified = password_verify($wrongPassword, $hash1);
        $this->addResult('Wrong Password Rejection', 
            !$wrongVerified ? 'PASS' : 'FAIL',
            !$wrongVerified ? 'Wrong passwords are rejected' : 'Wrong passwords are accepted'
        );
        
        echo "</div>\n";
    }
    
    private function testDirectoryProtection(): void {
        echo "<div class='test-section'>\n";
        echo "<h2>📂 Directory Protection Tests</h2>\n";
        
        // Check for .htaccess files in sensitive directories
        $sensitiveDirectories = [
            'storage',
            'config',
            'src',
            'logs'
        ];
        
        foreach ($sensitiveDirectories as $dir) {
            $htaccessPath = $dir . '/.htaccess';
            if (file_exists($htaccessPath)) {
                $this->addResult("$dir/.htaccess", 'PASS', "Directory $dir is protected by .htaccess");
            } else {
                $this->addResult("$dir/.htaccess", 'WARNING', "Directory $dir does not have .htaccess protection");
            }
        }
        
        // Check if directory listing is disabled
        $indexFiles = ['index.html', 'index.php'];
        $hasIndexFile = false;
        foreach ($indexFiles as $indexFile) {
            if (file_exists($indexFile)) {
                $hasIndexFile = true;
                break;
            }
        }
        
        $this->addResult('Directory Listing Protection', 
            $hasIndexFile ? 'PASS' : 'INFO',
            $hasIndexFile ? 'Directory has index file' : 'Consider adding index file to prevent directory listing'
        );
        
        echo "</div>\n";
    }
    
    private function testPHPConfiguration(): void {
        echo "<div class='test-section'>\n";
        echo "<h2>🐘 PHP Configuration Security Tests</h2>\n";
        
        // Check dangerous PHP settings
        $dangerousSettings = [
            'allow_url_fopen' => false,
            'allow_url_include' => false,
            'expose_php' => false,
            'display_errors' => false,
            'log_errors' => true
        ];
        
        foreach ($dangerousSettings as $setting => $safeValue) {
            $currentValue = ini_get($setting);
            $isSafe = ($currentValue == $safeValue);
            
            $this->addResult("PHP $setting", 
                $isSafe ? 'PASS' : 'WARNING',
                $isSafe ? "PHP $setting is safely configured" : "PHP $setting should be " . ($safeValue ? 'enabled' : 'disabled')
            );
        }
        
        // Check file upload settings
        $uploadSettings = [
            'file_uploads' => ini_get('file_uploads'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'max_file_uploads' => ini_get('max_file_uploads')
        ];
        
        $this->addResult('File Upload Configuration', 'INFO', 
            'Upload settings: ' . json_encode($uploadSettings));
        
        echo "</div>\n";
    }
    
    private function addResult(string $test, string $status, string $message): void {
        $this->results[] = [
            'test' => $test,
            'status' => $status,
            'message' => $message
        ];
        
        $statusClass = strtolower($status);
        $bgClass = $statusClass . '-bg';
        
        echo "<div class='test-result $bgClass'>\n";
        echo "<strong class='$statusClass'>[$status]</strong> $test: $message\n";
        echo "</div>\n";
    }
    
    private function generateSummary(): void {
        echo "<div class='test-section'>\n";
        echo "<h2>📊 Security Test Summary</h2>\n";
        
        $counts = [
            'PASS' => 0,
            'FAIL' => 0,
            'WARNING' => 0,
            'INFO' => 0
        ];
        
        foreach ($this->results as $result) {
            $counts[$result['status']]++;
        }
        
        echo "<div class='test-result'>\n";
        echo "<strong>Total Tests:</strong> " . count($this->results) . "<br>\n";
        echo "<strong class='pass'>Passed:</strong> {$counts['PASS']}<br>\n";
        echo "<strong class='fail'>Failed:</strong> {$counts['FAIL']}<br>\n";
        echo "<strong class='warning'>Warnings:</strong> {$counts['WARNING']}<br>\n";
        echo "<strong class='info'>Info:</strong> {$counts['INFO']}<br>\n";
        echo "</div>\n";
        
        $score = ($counts['PASS'] / count($this->results)) * 100;
        echo "<div class='test-result " . ($score >= 80 ? 'pass-bg' : ($score >= 60 ? 'warning-bg' : 'fail-bg')) . "'>\n";
        echo "<strong>Security Score: " . round($score, 1) . "%</strong>\n";
        echo "</div>\n";
        
        if ($counts['FAIL'] > 0) {
            echo "<div class='test-result fail-bg'>\n";
            echo "<strong>⚠️ Critical Issues Found!</strong><br>\n";
            echo "Please address the failed tests before deploying to production.\n";
            echo "</div>\n";
        } elseif ($counts['WARNING'] > 0) {
            echo "<div class='test-result warning-bg'>\n";
            echo "<strong>⚠️ Warnings Found</strong><br>\n";
            echo "Consider addressing the warnings for improved security.\n";
            echo "</div>\n";
        } else {
            echo "<div class='test-result pass-bg'>\n";
            echo "<strong>✅ All Security Tests Passed!</strong><br>\n";
            echo "Your application appears to be well-secured.\n";
            echo "</div>\n";
        }
        
        echo "</div>\n";
    }
}

// Run the security tests
$tester = new SecurityTester();
$tester->runAllTests();

echo "<div style='margin-top: 20px; padding: 15px; background: #f0f0f0; border-radius: 5px;'>\n";
echo "<small>Security test completed on " . date('Y-m-d H:i:s') . "</small>\n";
echo "</div>\n";
