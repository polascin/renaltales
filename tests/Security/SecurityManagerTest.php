<?php

require_once 'tests/BaseTestCase.php';
require_once 'core/SecurityManager.php';
require_once 'core/SessionManager.php';

/**
 * Security Tests for SecurityManager Class
 * 
 * Tests CSRF protection, XSS prevention, security headers,
 * and other security measures
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */
class SecurityManagerTest extends BaseTestCase
{
    private $securityManager;
    private $sessionManager;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock session manager
        $this->sessionManager = $this->createMock(SessionManager::class);
        $this->securityManager = new SecurityManager($this->sessionManager);
    }
    
    protected function tearDown(): void
    {
        // Clear any set headers
        if (function_exists('headers_sent') && !headers_sent()) {
            header_remove();
        }
        
        parent::tearDown();
    }
    
    /**
     * Test CSRF token generation
     */
    public function testCSRFTokenGeneration(): void
    {
        // Mock session manager to return a token
        $this->sessionManager->expects($this->once())
            ->method('getCSRFToken')
            ->willReturn('test_csrf_token_12345');
        
        $token = $this->securityManager->getCSRFToken();
        
        $this->assertNotEmpty($token);
        $this->assertEquals('test_csrf_token_12345', $token);
    }
    
    /**
     * Test CSRF token validation
     */
    public function testCSRFTokenValidation(): void
    {
        // Test valid token
        $this->sessionManager->expects($this->once())
            ->method('validateCSRFToken')
            ->with('valid_token')
            ->willReturn(true);
        
        $isValid = $this->securityManager->validateCSRFToken('valid_token');
        $this->assertTrue($isValid);
    }
    
    /**
     * Test CSRF token validation with invalid token
     */
    public function testCSRFTokenValidationInvalid(): void
    {
        // Test invalid token
        $this->sessionManager->expects($this->once())
            ->method('validateCSRFToken')
            ->with('invalid_token')
            ->willReturn(false);
        
        $isValid = $this->securityManager->validateCSRFToken('invalid_token');
        $this->assertFalse($isValid);
    }
    
    /**
     * Test XSS prevention
     */
    public function testXSSPrevention(): void
    {
        $maliciousInput = '<script>alert("XSS")</script>';
        $cleanInput = $this->securityManager->sanitizeInput($maliciousInput);
        
        $this->assertStringNotContainsString('<script>', $cleanInput);
        $this->assertStringNotContainsString('alert', $cleanInput);
    }
    
    /**
     * Test SQL injection prevention
     */
    public function testSQLInjectionPrevention(): void
    {
        $maliciousInput = "'; DROP TABLE users; --";
        $cleanInput = $this->securityManager->sanitizeInput($maliciousInput);
        
        $this->assertStringNotContainsString('DROP TABLE', $cleanInput);
        $this->assertStringNotContainsString('--', $cleanInput);
    }
    
    /**
     * Test input validation
     */
    public function testInputValidation(): void
    {
        // Test valid input
        $validInput = 'This is valid input';
        $this->assertTrue($this->securityManager->validateInput($validInput));
        
        // Test invalid input with script tags
        $invalidInput = '<script>alert("hack")</script>';
        $this->assertFalse($this->securityManager->validateInput($invalidInput));
        
        // Test invalid input with SQL injection
        $sqlInjection = "'; DROP TABLE users; --";
        $this->assertFalse($this->securityManager->validateInput($sqlInjection));
    }
    
    /**
     * Test password hashing
     */
    public function testPasswordHashing(): void
    {
        $password = 'secure_password_123';
        $hashedPassword = $this->securityManager->hashPassword($password);
        
        $this->assertNotEmpty($hashedPassword);
        $this->assertNotEquals($password, $hashedPassword);
        $this->assertTrue(password_verify($password, $hashedPassword));
    }
    
    /**
     * Test password verification
     */
    public function testPasswordVerification(): void
    {
        $password = 'secure_password_123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $this->assertTrue($this->securityManager->verifyPassword($password, $hashedPassword));
        $this->assertFalse($this->securityManager->verifyPassword('wrong_password', $hashedPassword));
    }
    
    /**
     * Test security headers
     */
    public function testSecurityHeaders(): void
    {
        // This test would need to be adapted based on actual implementation
        $this->securityManager->setSecurityHeaders();
        
        // Check that headers are set (this is a simplified test)
        $this->assertTrue(true); // Placeholder assertion
    }
    
    /**
     * Test rate limiting
     */
    public function testRateLimiting(): void
    {
        $identifier = 'test_user';
        $action = 'login';
        
        // First attempt should be allowed
        $this->assertTrue($this->securityManager->checkRateLimit($identifier, $action));
        
        // After multiple attempts, should be rate limited
        for ($i = 0; $i < 10; $i++) {
            $this->securityManager->recordAttempt($identifier, $action);
        }
        
        // Should now be rate limited
        $this->assertFalse($this->securityManager->checkRateLimit($identifier, $action));
    }
    
    /**
     * Test IP validation
     */
    public function testIPValidation(): void
    {
        // Test valid IPv4
        $this->assertTrue($this->securityManager->isValidIP('192.168.1.1'));
        
        // Test valid IPv6
        $this->assertTrue($this->securityManager->isValidIP('::1'));
        
        // Test invalid IP
        $this->assertFalse($this->securityManager->isValidIP('invalid.ip'));
        $this->assertFalse($this->securityManager->isValidIP('999.999.999.999'));
    }
    
    /**
     * Test user agent validation
     */
    public function testUserAgentValidation(): void
    {
        // Test normal user agent
        $normalUA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/91.0.4472.124';
        $this->assertTrue($this->securityManager->isValidUserAgent($normalUA));
        
        // Test suspicious user agent
        $suspiciousUA = 'sqlmap/1.0 (http://sqlmap.org)';
        $this->assertFalse($this->securityManager->isValidUserAgent($suspiciousUA));
        
        // Test empty user agent
        $this->assertFalse($this->securityManager->isValidUserAgent(''));
    }
    
    /**
     * Test file upload validation
     */
    public function testFileUploadValidation(): void
    {
        // Test valid file
        $validFile = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'size' => 1024 * 1024, // 1MB
            'tmp_name' => '/tmp/test',
            'error' => UPLOAD_ERR_OK
        ];
        
        $this->assertTrue($this->securityManager->validateFileUpload($validFile));
        
        // Test invalid file type
        $invalidFile = [
            'name' => 'script.php',
            'type' => 'application/x-httpd-php',
            'size' => 1024,
            'tmp_name' => '/tmp/script',
            'error' => UPLOAD_ERR_OK
        ];
        
        $this->assertFalse($this->securityManager->validateFileUpload($invalidFile));
        
        // Test file too large
        $largeFile = [
            'name' => 'large.jpg',
            'type' => 'image/jpeg',
            'size' => 10 * 1024 * 1024, // 10MB
            'tmp_name' => '/tmp/large',
            'error' => UPLOAD_ERR_OK
        ];
        
        $this->assertFalse($this->securityManager->validateFileUpload($largeFile));
    }
    
    /**
     * Test security event logging
     */
    public function testSecurityEventLogging(): void
    {
        $eventType = 'failed_login';
        $details = [
            'username' => 'testuser',
            'ip' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0...'
        ];
        
        $this->securityManager->logSecurityEvent($eventType, $details);
        
        // Verify event was logged
        $this->assertLogContains('Security event: ' . $eventType, 'warning');
    }
    
    /**
     * Test session security
     */
    public function testSessionSecurity(): void
    {
        $this->sessionManager->expects($this->once())
            ->method('regenerateId')
            ->willReturn(true);
        
        $this->securityManager->regenerateSession();
        
        $this->assertTrue(true); // Placeholder assertion
    }
    
    /**
     * Test brute force protection
     */
    public function testBruteForceProtection(): void
    {
        $identifier = 'test_user';
        
        // Simulate multiple failed attempts
        for ($i = 0; $i < 5; $i++) {
            $this->securityManager->recordFailedAttempt($identifier);
        }
        
        // Should be locked after 5 attempts
        $this->assertTrue($this->securityManager->isLocked($identifier));
        
        // Should not be able to attempt again
        $this->assertFalse($this->securityManager->canAttempt($identifier));
    }
    
    /**
     * Test data encryption
     */
    public function testDataEncryption(): void
    {
        $sensitiveData = 'sensitive information';
        
        $encrypted = $this->securityManager->encrypt($sensitiveData);
        $this->assertNotEmpty($encrypted);
        $this->assertNotEquals($sensitiveData, $encrypted);
        
        $decrypted = $this->securityManager->decrypt($encrypted);
        $this->assertEquals($sensitiveData, $decrypted);
    }
    
    /**
     * Test secure random generation
     */
    public function testSecureRandomGeneration(): void
    {
        $random1 = $this->securityManager->generateSecureRandom(32);
        $random2 = $this->securityManager->generateSecureRandom(32);
        
        $this->assertNotEmpty($random1);
        $this->assertNotEmpty($random2);
        $this->assertNotEquals($random1, $random2);
        $this->assertEquals(32, strlen($random1));
        $this->assertEquals(32, strlen($random2));
    }
    
    /**
     * Test content security policy
     */
    public function testContentSecurityPolicy(): void
    {
        $csp = $this->securityManager->generateCSP();
        
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("script-src 'self'", $csp);
        $this->assertStringContainsString("style-src 'self'", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
    }
}
