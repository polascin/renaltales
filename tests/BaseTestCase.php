<?php

use PHPUnit\Framework\TestCase;
use Monolog\Logger;
use Monolog\Handler\TestHandler;

/**
 * Base Test Case
 * 
 * Provides common functionality for all test classes including
 * database setup, fixtures, and helper methods
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */
abstract class BaseTestCase extends TestCase
{
    protected $database;
    protected $logger;
    protected $testHandler;
    
    /**
     * Set up test environment before each test
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Initialize database
        $this->database = getTestDatabase();
        
        // Initialize logger with test handler
        $this->logger = new Logger('test');
        $this->testHandler = new TestHandler();
        $this->logger->pushHandler($this->testHandler);
        
        // Set up clean database state
        $this->setUpDatabase();
        
        // Seed basic test data
        $this->seedBasicTestData();
    }
    
    /**
     * Clean up after each test
     */
    protected function tearDown(): void
    {
        // Clean up database
        $this->tearDownDatabase();
        
        // Clear logger handlers
        $this->logger->reset();
        
        parent::tearDown();
    }
    
    /**
     * Set up database for testing
     */
    protected function setUpDatabase(): void
    {
        // Begin transaction for test isolation
        $this->database->beginTransaction();
    }
    
    /**
     * Clean up database after testing
     */
    protected function tearDownDatabase(): void
    {
        // Roll back transaction to clean state
        if ($this->database->inTransaction()) {
            $this->database->rollback();
        }
    }
    
    /**
     * Seed basic test data
     */
    protected function seedBasicTestData(): void
    {
        // Create test users
        $this->database->execute("INSERT INTO users_new (username, email, password_hash, email_verified, status) VALUES (?, ?, ?, ?, ?)", [
            'testuser',
            'test@example.com',
            password_hash('password123', PASSWORD_DEFAULT),
            true,
            'active'
        ]);
        
        $this->database->execute("INSERT INTO users_new (username, email, password_hash, email_verified, status) VALUES (?, ?, ?, ?, ?)", [
            'admin',
            'admin@example.com',
            password_hash('admin123', PASSWORD_DEFAULT),
            true,
            'active'
        ]);
    }
    
    /**
     * Create a test user
     */
    protected function createTestUser($data = []): array
    {
        $userData = array_merge([
            'username' => 'testuser_' . uniqid(),
            'email' => 'test_' . uniqid() . '@example.com',
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'email_verified' => true,
            'status' => 'active'
        ], $data);
        
        $this->database->execute(
            "INSERT INTO users_new (username, email, password_hash, email_verified, status) VALUES (?, ?, ?, ?, ?)",
            [
                $userData['username'],
                $userData['email'],
                $userData['password_hash'],
                $userData['email_verified'],
                $userData['status']
            ]
        );
        
        $userId = $this->database->lastInsertId();
        $userData['id'] = $userId;
        
        return $userData;
    }
    
    /**
     * Create test story
     */
    protected function createTestStory($userId, $data = []): array
    {
        $storyData = array_merge([
            'title' => 'Test Story ' . uniqid(),
            'content' => 'This is test story content.',
            'author_id' => $userId,
            'status' => 'published',
            'featured' => false,
            'language' => 'en'
        ], $data);
        
        // Insert story (assuming stories table exists)
        $this->database->execute(
            "INSERT INTO stories (title, content, author_id, status, featured, language) VALUES (?, ?, ?, ?, ?, ?)",
            [
                $storyData['title'],
                $storyData['content'],
                $storyData['author_id'],
                $storyData['status'],
                $storyData['featured'],
                $storyData['language']
            ]
        );
        
        $storyId = $this->database->lastInsertId();
        $storyData['id'] = $storyId;
        
        return $storyData;
    }
    
    /**
     * Assert that database has record
     */
    protected function assertDatabaseHas(string $table, array $data): void
    {
        $conditions = [];
        $params = [];
        
        foreach ($data as $column => $value) {
            $conditions[] = "`$column` = ?";
            $params[] = $value;
        }
        
        $sql = "SELECT COUNT(*) as count FROM `$table` WHERE " . implode(' AND ', $conditions);
        $result = $this->database->selectOne($sql, $params);
        
        $this->assertGreaterThan(0, $result['count'], "Failed asserting that database table '$table' contains record.");
    }
    
    /**
     * Assert that database does not have record
     */
    protected function assertDatabaseMissing(string $table, array $data): void
    {
        $conditions = [];
        $params = [];
        
        foreach ($data as $column => $value) {
            $conditions[] = "`$column` = ?";
            $params[] = $value;
        }
        
        $sql = "SELECT COUNT(*) as count FROM `$table` WHERE " . implode(' AND ', $conditions);
        $result = $this->database->selectOne($sql, $params);
        
        $this->assertEquals(0, $result['count'], "Failed asserting that database table '$table' does not contain record.");
    }
    
    /**
     * Assert that database table has specific count
     */
    protected function assertDatabaseCount(string $table, int $expectedCount, array $conditions = []): void
    {
        $sql = "SELECT COUNT(*) as count FROM `$table`";
        $params = [];
        
        if (!empty($conditions)) {
            $conditionStrings = [];
            foreach ($conditions as $column => $value) {
                $conditionStrings[] = "`$column` = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $conditionStrings);
        }
        
        $result = $this->database->selectOne($sql, $params);
        
        $this->assertEquals($expectedCount, $result['count'], "Failed asserting that database table '$table' has $expectedCount records.");
    }
    
    /**
     * Assert JSON response structure
     */
    protected function assertJsonResponse(string $response, int $expectedStatus = 200): array
    {
        $data = json_decode($response, true);
        
        $this->assertNotNull($data, 'Response is not valid JSON');
        
        if ($expectedStatus >= 400) {
            $this->assertArrayHasKey('error', $data);
            $this->assertTrue($data['error']);
        } else {
            $this->assertArrayNotHasKey('error', $data);
        }
        
        return $data;
    }
    
    /**
     * Assert API success response
     */
    protected function assertApiSuccess(string $response, array $expectedKeys = []): array
    {
        $data = $this->assertJsonResponse($response, 200);
        
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $data);
        }
        
        return $data;
    }
    
    /**
     * Assert API error response
     */
    protected function assertApiError(string $response, int $expectedStatus = 400, string $expectedMessage = null): array
    {
        $data = $this->assertJsonResponse($response, $expectedStatus);
        
        $this->assertArrayHasKey('error', $data);
        $this->assertTrue($data['error']);
        $this->assertArrayHasKey('message', $data);
        
        if ($expectedMessage) {
            $this->assertEquals($expectedMessage, $data['message']);
        }
        
        return $data;
    }
    
    /**
     * Mock HTTP request
     */
    protected function mockHttpRequest(string $method, string $uri, array $data = [], array $headers = []): void
    {
        $_SERVER['REQUEST_METHOD'] = strtoupper($method);
        $_SERVER['REQUEST_URI'] = $uri;
        
        foreach ($headers as $key => $value) {
            $_SERVER['HTTP_' . strtoupper(str_replace('-', '_', $key))] = $value;
        }
        
        if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $_POST = $data;
        } else {
            $_GET = $data;
        }
    }
    
    /**
     * Create temporary file for testing
     */
    protected function createTempFile(string $content, string $filename = null): string
    {
        $tempDir = sys_get_temp_dir();
        $filename = $filename ?: 'test_' . uniqid() . '.tmp';
        $filepath = $tempDir . DIRECTORY_SEPARATOR . $filename;
        
        file_put_contents($filepath, $content);
        
        return $filepath;
    }
    
    /**
     * Clean up temporary files
     */
    protected function cleanupTempFiles(array $files): void
    {
        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
    
    /**
     * Get test fixture content
     */
    protected function getFixture(string $filename): string
    {
        $fixturePath = TESTS_ROOT . '/fixtures/' . $filename;
        
        if (!file_exists($fixturePath)) {
            throw new Exception("Fixture file not found: $fixturePath");
        }
        
        return file_get_contents($fixturePath);
    }
    
    /**
     * Assert that log contains specific message
     */
    protected function assertLogContains(string $message, string $level = 'info'): void
    {
        $records = $this->testHandler->getRecords();
        
        foreach ($records as $record) {
            if ($record['level_name'] === strtoupper($level) && 
                strpos($record['message'], $message) !== false) {
                $this->assertTrue(true);
                return;
            }
        }
        
        $this->fail("Log does not contain expected message: $message");
    }
    
    /**
     * Get all log records
     */
    protected function getLogRecords(): array
    {
        return $this->testHandler->getRecords();
    }
    
    /**
     * Clear log records
     */
    protected function clearLogRecords(): void
    {
        $this->testHandler->clear();
    }
}
