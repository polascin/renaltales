<?php

require_once 'tests/BaseTestCase.php';
require_once 'src/Application.php';

use RenalTales\Application;

/**
 * Unit Tests for Application Class
 * 
 * Tests core application initialization, configuration management,
 * and logging functionality
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */
class ApplicationTest extends BaseTestCase
{
    private $application;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->application = Application::getInstance();
    }
    
    protected function tearDown(): void
    {
        // Reset singleton instance for clean tests
        $reflection = new ReflectionClass(Application::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);
        
        parent::tearDown();
    }
    
    /**
     * Test singleton pattern
     */
    public function testSingletonPattern(): void
    {
        $app1 = Application::getInstance();
        $app2 = Application::getInstance();
        
        $this->assertSame($app1, $app2);
        $this->assertInstanceOf(Application::class, $app1);
    }
    
    /**
     * Test application initialization
     */
    public function testInitialization(): void
    {
        $config = [
            'app' => [
                'name' => 'Test App',
                'version' => '1.0.0',
                'env' => 'testing',
                'debug' => true
            ],
            'database' => [
                'host' => 'localhost',
                'name' => 'test_db'
            ]
        ];
        
        $this->application->initialize($config);
        
        $this->assertEquals('Test App', $this->application->getName());
        $this->assertEquals('1.0.0', $this->application->getVersion());
        $this->assertEquals('testing', $this->application->getEnvironment());
        $this->assertTrue($this->application->isDebug());
    }
    
    /**
     * Test configuration retrieval
     */
    public function testConfigurationRetrieval(): void
    {
        $config = [
            'app' => [
                'name' => 'Test App',
                'version' => '1.0.0'
            ],
            'database' => [
                'host' => 'localhost',
                'port' => 3306
            ]
        ];
        
        $this->application->initialize($config);
        
        // Test nested config access
        $this->assertEquals('Test App', $this->application->getConfig('app.name'));
        $this->assertEquals('localhost', $this->application->getConfig('database.host'));
        $this->assertEquals(3306, $this->application->getConfig('database.port'));
        
        // Test default values
        $this->assertEquals('default', $this->application->getConfig('nonexistent', 'default'));
        $this->assertNull($this->application->getConfig('nonexistent'));
    }
    
    /**
     * Test database configuration
     */
    public function testDatabaseConfiguration(): void
    {
        $config = [
            'database' => [
                'host' => 'localhost',
                'name' => 'test_db',
                'user' => 'root',
                'password' => 'secret'
            ]
        ];
        
        $this->application->initialize($config);
        
        $dbConfig = $this->application->getDatabaseConfig();
        
        $this->assertEquals('localhost', $dbConfig['host']);
        $this->assertEquals('test_db', $dbConfig['name']);
        $this->assertEquals('root', $dbConfig['user']);
        $this->assertEquals('secret', $dbConfig['password']);
    }
    
    /**
     * Test logging functionality
     */
    public function testLogging(): void
    {
        $config = [
            'app' => [
                'name' => 'Test App',
                'debug' => true
            ]
        ];
        
        $this->application->initialize($config);
        
        // Test info logging
        $this->application->logInfo('Test info message', ['key' => 'value']);
        
        // Test error logging
        $this->application->logError('Test error message', ['error' => 'details']);
        
        // Test debug logging
        $this->application->logDebug('Test debug message');
        
        // Assert that methods don't throw exceptions
        $this->assertTrue(true);
    }
    
    /**
     * Test path methods
     */
    public function testPathMethods(): void
    {
        $this->application->initialize([]);
        
        $rootPath = $this->application->getRootPath();
        $this->assertNotEmpty($rootPath);
        $this->assertDirectoryExists($rootPath);
        
        $storagePath = $this->application->getStoragePath();
        $this->assertStringContains('storage', $storagePath);
        
        $cachePath = $this->application->getCachePath();
        $this->assertStringContains('cache', $cachePath);
        
        $logsPath = $this->application->getLogsPath();
        $this->assertStringContains('logs', $logsPath);
    }
    
    /**
     * Test debug mode
     */
    public function testDebugMode(): void
    {
        // Test debug enabled
        $this->application->initialize(['app' => ['debug' => true]]);
        $this->assertTrue($this->application->isDebug());
        
        // Test debug disabled
        $this->application->initialize(['app' => ['debug' => false]]);
        $this->assertFalse($this->application->isDebug());
        
        // Test default value
        $this->application->initialize([]);
        $this->assertFalse($this->application->isDebug());
    }
    
    /**
     * Test environment detection
     */
    public function testEnvironmentDetection(): void
    {
        // Test production environment
        $this->application->initialize(['app' => ['env' => 'production']]);
        $this->assertEquals('production', $this->application->getEnvironment());
        
        // Test development environment
        $this->application->initialize(['app' => ['env' => 'development']]);
        $this->assertEquals('development', $this->application->getEnvironment());
        
        // Test default environment
        $this->application->initialize([]);
        $this->assertEquals('development', $this->application->getEnvironment());
    }
    
    /**
     * Test application name and version
     */
    public function testApplicationInfo(): void
    {
        $this->application->initialize([
            'app' => [
                'name' => 'RenalTales',
                'version' => '2025.v1.0'
            ]
        ]);
        
        $this->assertEquals('RenalTales', $this->application->getName());
        $this->assertEquals('2025.v1.0', $this->application->getVersion());
    }
    
    /**
     * Test configuration merging
     */
    public function testConfigurationMerging(): void
    {
        $config1 = [
            'app' => [
                'name' => 'App1',
                'version' => '1.0.0'
            ],
            'database' => [
                'host' => 'localhost'
            ]
        ];
        
        $this->application->initialize($config1);
        
        $config2 = [
            'app' => [
                'name' => 'App2',
                'debug' => true
            ],
            'database' => [
                'port' => 3306
            ]
        ];
        
        $this->application->initialize($config2);
        
        // Second config should override first
        $this->assertEquals('App2', $this->application->getName());
        $this->assertTrue($this->application->isDebug());
    }
    
    /**
     * Test error handling in configuration
     */
    public function testErrorHandling(): void
    {
        // Test with empty config
        $this->application->initialize([]);
        
        // Should use default values
        $this->assertEquals('RenalTales', $this->application->getName());
        $this->assertEquals('1.0.0', $this->application->getVersion());
        $this->assertEquals('development', $this->application->getEnvironment());
        $this->assertFalse($this->application->isDebug());
    }
}
