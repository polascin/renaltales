<?php

/**
 * Database Migration Test Script
 * 
 * Tests the new normalized database schema
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/UserProfile.php';
require_once __DIR__ . '/../models/SecurityEvent.php';

class MigrationTest {
    
    private $db;
    private $passed = 0;
    private $failed = 0;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Run all tests
     */
    public function runAllTests() {
        echo "========================================\n";
        echo "Database Migration Test Suite\n";
        echo "========================================\n\n";
        
        $this->testDatabaseConnection();
        $this->testTableStructure();
        $this->testForeignKeys();
        $this->testCharsetSupport();
        $this->testIndexes();
        $this->testModels();
        $this->testStoredProcedures();
        
        echo "\n========================================\n";
        echo "Test Results: {$this->passed} passed, {$this->failed} failed\n";
        echo "========================================\n";
        
        return $this->failed === 0;
    }
    
    /**
     * Test database connection
     */
    private function testDatabaseConnection() {
        echo "Testing database connection...\n";
        
        try {
            $result = $this->db->selectOne("SELECT 1 as test");
            $this->assert($result['test'] === 1, "Database connection test");
        } catch (Exception $e) {
            $this->assert(false, "Database connection test: " . $e->getMessage());
        }
    }
    
    /**
     * Test table structure
     */
    private function testTableStructure() {
        echo "\nTesting table structure...\n";
        
        $requiredTables = [
            'users', 'user_profiles', 'password_resets', 'email_verifications',
            'user_sessions', 'audit_logs', 'security_events', 'system_logs',
            'language_preferences', 'database_migrations'
        ];
        
        foreach ($requiredTables as $table) {
            try {
                $result = $this->db->selectOne("SHOW TABLES LIKE '{$table}'");
                $this->assert($result !== false, "Table '{$table}' exists");
            } catch (Exception $e) {
                $this->assert(false, "Table '{$table}' check failed: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Test foreign key constraints
     */
    private function testForeignKeys() {
        echo "\nTesting foreign key constraints...\n";
        
        try {
            // Test foreign key constraint in user_profiles
            $result = $this->db->select("
                SELECT CONSTRAINT_NAME 
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_NAME = 'user_profiles' 
                AND CONSTRAINT_NAME LIKE 'fk_%'
            ");
            $this->assert(count($result) > 0, "Foreign key constraints exist in user_profiles");
            
            // Test foreign key constraint in password_resets
            $result = $this->db->select("
                SELECT CONSTRAINT_NAME 
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_NAME = 'password_resets' 
                AND CONSTRAINT_NAME LIKE 'fk_%'
            ");
            $this->assert(count($result) > 0, "Foreign key constraints exist in password_resets");
            
        } catch (Exception $e) {
            $this->assert(false, "Foreign key test failed: " . $e->getMessage());
        }
    }
    
    /**
     * Test UTF8MB4 charset support
     */
    private function testCharsetSupport() {
        echo "\nTesting UTF8MB4 charset support...\n";
        
        try {
            // Test database charset
            $result = $this->db->selectOne("
                SELECT DEFAULT_CHARACTER_SET_NAME 
                FROM INFORMATION_SCHEMA.SCHEMATA 
                WHERE SCHEMA_NAME = 'renaltales'
            ");
            $this->assert($result['DEFAULT_CHARACTER_SET_NAME'] === 'utf8mb4', "Database uses UTF8MB4 charset");
            
            // Test table charset
            $result = $this->db->selectOne("
                SELECT TABLE_COLLATION 
                FROM INFORMATION_SCHEMA.TABLES 
                WHERE TABLE_SCHEMA = 'renaltales' 
                AND TABLE_NAME = 'users'
            ");
            $this->assert(
                strpos($result['TABLE_COLLATION'], 'utf8mb4') === 0, 
                "Users table uses UTF8MB4 collation"
            );
            
        } catch (Exception $e) {
            $this->assert(false, "Charset test failed: " . $e->getMessage());
        }
    }
    
    /**
     * Test indexes
     */
    private function testIndexes() {
        echo "\nTesting indexes...\n";
        
        try {
            // Test users table indexes
            $result = $this->db->select("SHOW INDEX FROM users");
            $indexNames = array_column($result, 'Key_name');
            
            $this->assert(in_array('uk_email', $indexNames), "Users table has email unique index");
            $this->assert(in_array('uk_username', $indexNames), "Users table has username unique index");
            
            // Test user_profiles table indexes
            $result = $this->db->select("SHOW INDEX FROM user_profiles");
            $indexNames = array_column($result, 'Key_name');
            
            $this->assert(in_array('uk_user_id', $indexNames), "User_profiles table has user_id unique index");
            
        } catch (Exception $e) {
            $this->assert(false, "Index test failed: " . $e->getMessage());
        }
    }
    
    /**
     * Test model operations
     */
    private function testModels() {
        echo "\nTesting model operations...\n";
        
        try {
            // Test User model
            $userModel = new User();
            $testUser = $userModel->findByEmail('admin@renaltales.local');
            $this->assert($testUser !== false, "User model can find users by email");
            
            // Test UserProfile model
            $profileModel = new UserProfile();
            $testProfile = $profileModel->findByUserId($testUser['id']);
            $this->assert($testProfile !== false, "UserProfile model can find profiles by user_id");
            
            // Test SecurityEvent model
            $securityModel = new SecurityEvent();
            $events = $securityModel->findByUserId($testUser['id'], 5);
            $this->assert(is_array($events), "SecurityEvent model can find events by user_id");
            
        } catch (Exception $e) {
            $this->assert(false, "Model test failed: " . $e->getMessage());
        }
    }
    
    /**
     * Test stored procedures
     */
    private function testStoredProcedures() {
        echo "\nTesting stored procedures...\n";
        
        try {
            // Test GetUserWithProfile procedure
            $result = $this->db->select("CALL GetUserWithProfile(1)");
            $this->assert(count($result) > 0, "GetUserWithProfile procedure works");
            
            // Test CleanExpiredTokens procedure
            $result = $this->db->select("CALL CleanExpiredTokens()");
            $this->assert(count($result) > 0, "CleanExpiredTokens procedure works");
            
        } catch (Exception $e) {
            $this->assert(false, "Stored procedure test failed: " . $e->getMessage());
        }
    }
    
    /**
     * Assert helper
     */
    private function assert($condition, $message) {
        if ($condition) {
            echo "  ✓ {$message}\n";
            $this->passed++;
        } else {
            echo "  ✗ {$message}\n";
            $this->failed++;
        }
    }
}

// Run tests if called directly
if (php_sapi_name() === 'cli') {
    $test = new MigrationTest();
    $success = $test->runAllTests();
    exit($success ? 0 : 1);
}

?>
