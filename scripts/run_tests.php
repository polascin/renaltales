<?php
/**
 * Test Execution Script
 * 
 * Comprehensive test runner for all test suites including
 * unit tests, integration tests, security tests, and API tests
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

// Set up environment
define('APP_ROOT', dirname(__DIR__));
define('TESTING', true);

// Load configuration
require_once APP_ROOT . '/bootstrap.php';

class TestRunner
{
    private $results = [];
    private $startTime;
    private $totalTests = 0;
    private $passedTests = 0;
    private $failedTests = 0;
    private $skippedTests = 0;
    
    public function __construct()
    {
        $this->startTime = microtime(true);
        
        // Ensure testing environment
        $_ENV['APP_ENV'] = 'testing';
        $_ENV['DB_DATABASE'] = 'renaltales_test';
        
        echo "=== RenalTales Test Suite Runner ===\n";
        echo "Environment: " . $_ENV['APP_ENV'] . "\n";
        echo "Database: " . $_ENV['DB_DATABASE'] . "\n";
        echo "Started at: " . date('Y-m-d H:i:s') . "\n\n";
    }
    
    /**
     * Run all test suites
     */
    public function runAllTests(): void
    {
        $this->setupTestEnvironment();
        
        $testSuites = [
            'Unit Tests' => 'tests/Unit',
            'Integration Tests' => 'tests/Integration',
            'Security Tests' => 'tests/Security',
            'API Tests' => 'tests/API',
            'Feature Tests' => 'tests/Feature',
            'Database Tests' => 'tests/Database'
        ];
        
        foreach ($testSuites as $suiteName => $suitePath) {
            $this->runTestSuite($suiteName, $suitePath);
        }
        
        $this->generateReport();
        $this->cleanupTestEnvironment();
    }
    
    /**
     * Run specific test suite
     */
    public function runTestSuite(string $suiteName, string $suitePath): void
    {
        echo "Running $suiteName...\n";
        echo str_repeat('-', 50) . "\n";
        
        if (!is_dir(APP_ROOT . '/' . $suitePath)) {
            echo "⚠ Test suite directory not found: $suitePath\n";
            $this->skippedTests++;
            return;
        }
        
        $testFiles = glob(APP_ROOT . '/' . $suitePath . '/*Test.php');
        
        if (empty($testFiles)) {
            echo "⚠ No test files found in $suitePath\n";
            $this->skippedTests++;
            return;
        }
        
        $suiteResults = [];
        $suiteStartTime = microtime(true);
        
        foreach ($testFiles as $testFile) {
            $result = $this->runTestFile($testFile);
            $suiteResults[] = $result;
            
            if ($result['status'] === 'passed') {
                $this->passedTests += $result['tests'];
            } elseif ($result['status'] === 'failed') {
                $this->failedTests += $result['tests'];
            } else {
                $this->skippedTests += $result['tests'];
            }
            
            $this->totalTests += $result['tests'];
        }
        
        $suiteTime = microtime(true) - $suiteStartTime;
        
        $this->results[$suiteName] = [
            'results' => $suiteResults,
            'time' => $suiteTime,
            'passed' => array_sum(array_column($suiteResults, 'passed')),
            'failed' => array_sum(array_column($suiteResults, 'failed')),
            'skipped' => array_sum(array_column($suiteResults, 'skipped'))
        ];
        
        echo "\n$suiteName completed in " . number_format($suiteTime, 2) . "s\n\n";
    }
    
    /**
     * Run individual test file
     */
    private function runTestFile(string $testFile): array
    {
        $fileName = basename($testFile);
        echo "  Running $fileName... ";
        
        $startTime = microtime(true);
        
        // Use PHPUnit if available, otherwise simple test execution
        if (class_exists('PHPUnit\TextUI\Command')) {
            return $this->runPHPUnitTest($testFile);
        } else {
            return $this->runSimpleTest($testFile);
        }
    }
    
    /**
     * Run test using PHPUnit
     */
    private function runPHPUnitTest(string $testFile): array
    {
        $command = "vendor/bin/phpunit --colors=never --no-progress $testFile 2>&1";
        $output = shell_exec($command);
        
        // Parse PHPUnit output
        $passed = preg_match('/OK \((\d+) tests?/', $output, $matches) ? (int)$matches[1] : 0;
        $failed = preg_match('/FAILURES!\s*Tests: \d+, Assertions: \d+, Failures: (\d+)/', $output, $matches) ? (int)$matches[1] : 0;
        $errors = preg_match('/ERRORS!\s*Tests: \d+, Assertions: \d+, Errors: (\d+)/', $output, $matches) ? (int)$matches[1] : 0;
        $skipped = preg_match('/skipped: (\d+)/', $output, $matches) ? (int)$matches[1] : 0;
        
        $totalTests = $passed + $failed + $errors + $skipped;
        
        if ($failed > 0 || $errors > 0) {
            echo "✗ FAILED\n";
            echo "    Output: " . substr($output, 0, 200) . "...\n";
            $status = 'failed';
        } elseif ($passed > 0) {
            echo "✓ PASSED\n";
            $status = 'passed';
        } else {
            echo "- SKIPPED\n";
            $status = 'skipped';
        }
        
        return [
            'file' => basename($testFile),
            'status' => $status,
            'tests' => $totalTests,
            'passed' => $passed,
            'failed' => $failed + $errors,
            'skipped' => $skipped,
            'output' => $output
        ];
    }
    
    /**
     * Run simple test execution
     */
    private function runSimpleTest(string $testFile): array
    {
        try {
            // Capture output
            ob_start();
            $error = false;
            
            try {
                require_once $testFile;
                
                // Try to instantiate and run test class
                $className = $this->getTestClassName($testFile);
                if (class_exists($className)) {
                    $testInstance = new $className();
                    
                    // Run test methods
                    $methods = get_class_methods($testInstance);
                    $testMethods = array_filter($methods, function($method) {
                        return strpos($method, 'test') === 0;
                    });
                    
                    foreach ($testMethods as $method) {
                        $testInstance->$method();
                    }
                    
                    echo "✓ PASSED\n";
                    $status = 'passed';
                    $tests = count($testMethods);
                    $passed = $tests;
                    $failed = 0;
                } else {
                    echo "- SKIPPED (No test class found)\n";
                    $status = 'skipped';
                    $tests = 0;
                    $passed = 0;
                    $failed = 0;
                }
                
            } catch (Exception $e) {
                echo "✗ FAILED (" . $e->getMessage() . ")\n";
                $status = 'failed';
                $tests = 1;
                $passed = 0;
                $failed = 1;
                $error = true;
            }
            
            $output = ob_get_clean();
            
            return [
                'file' => basename($testFile),
                'status' => $status,
                'tests' => $tests ?? 0,
                'passed' => $passed ?? 0,
                'failed' => $failed ?? 0,
                'skipped' => 0,
                'output' => $output,
                'error' => $error
            ];
            
        } catch (Exception $e) {
            ob_end_clean();
            
            echo "✗ FAILED (" . $e->getMessage() . ")\n";
            
            return [
                'file' => basename($testFile),
                'status' => 'failed',
                'tests' => 1,
                'passed' => 0,
                'failed' => 1,
                'skipped' => 0,
                'output' => $e->getMessage(),
                'error' => true
            ];
        }
    }
    
    /**
     * Get test class name from file
     */
    private function getTestClassName(string $testFile): string
    {
        $fileName = basename($testFile, '.php');
        return $fileName;
    }
    
    /**
     * Setup test environment
     */
    private function setupTestEnvironment(): void
    {
        echo "Setting up test environment...\n";
        
        // Create test database
        $this->setupTestDatabase();
        
        // Create test directories
        $testDirs = [
            'storage/testing',
            'storage/testing/cache',
            'storage/testing/logs',
            'storage/testing/sessions',
            'storage/testing/uploads'
        ];
        
        foreach ($testDirs as $dir) {
            $fullPath = APP_ROOT . '/' . $dir;
            if (!is_dir($fullPath)) {
                mkdir($fullPath, 0755, true);
            }
        }
        
        echo "✓ Test environment setup complete\n\n";
    }
    
    /**
     * Setup test database
     */
    private function setupTestDatabase(): void
    {
        try {
            // Connect to MySQL and create test database
            $config = $GLOBALS['config']['database'];
            $dsn = "mysql:host={$config['host']};charset={$config['charset']}";
            $pdo = new PDO($dsn, $config['user'], $config['password']);
            
            $testDb = $_ENV['DB_DATABASE'];
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$testDb` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            echo "✓ Test database created/verified\n";
            
        } catch (Exception $e) {
            echo "✗ Failed to setup test database: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Generate test report
     */
    private function generateReport(): void
    {
        $totalTime = microtime(true) - $this->startTime;
        
        echo "\n" . str_repeat('=', 70) . "\n";
        echo "TEST RESULTS SUMMARY\n";
        echo str_repeat('=', 70) . "\n";
        
        foreach ($this->results as $suiteName => $suiteData) {
            echo "\n$suiteName:\n";
            echo "  Time: " . number_format($suiteData['time'], 2) . "s\n";
            echo "  Passed: " . $suiteData['passed'] . "\n";
            echo "  Failed: " . $suiteData['failed'] . "\n";
            echo "  Skipped: " . $suiteData['skipped'] . "\n";
            
            if ($suiteData['failed'] > 0) {
                echo "  Status: ✗ FAILED\n";
            } elseif ($suiteData['passed'] > 0) {
                echo "  Status: ✓ PASSED\n";
            } else {
                echo "  Status: - SKIPPED\n";
            }
        }
        
        echo "\n" . str_repeat('-', 70) . "\n";
        echo "OVERALL RESULTS:\n";
        echo "  Total Tests: $this->totalTests\n";
        echo "  Passed: $this->passedTests\n";
        echo "  Failed: $this->failedTests\n";
        echo "  Skipped: $this->skippedTests\n";
        echo "  Success Rate: " . ($this->totalTests > 0 ? number_format(($this->passedTests / $this->totalTests) * 100, 1) : 0) . "%\n";
        echo "  Total Time: " . number_format($totalTime, 2) . "s\n";
        
        if ($this->failedTests > 0) {
            echo "\n❌ TESTS FAILED\n";
            exit(1);
        } else {
            echo "\n✅ ALL TESTS PASSED\n";
        }
        
        // Generate detailed report file
        $this->generateDetailedReport();
    }
    
    /**
     * Generate detailed test report
     */
    private function generateDetailedReport(): void
    {
        $reportPath = APP_ROOT . '/tests/results/test_report_' . date('Y-m-d_H-i-s') . '.html';
        
        // Create results directory if it doesn't exist
        $resultsDir = dirname($reportPath);
        if (!is_dir($resultsDir)) {
            mkdir($resultsDir, 0755, true);
        }
        
        $html = $this->generateReportHTML();
        file_put_contents($reportPath, $html);
        
        echo "📄 Detailed report saved to: $reportPath\n";
    }
    
    /**
     * Generate HTML report
     */
    private function generateReportHTML(): string
    {
        $totalTime = microtime(true) - $this->startTime;
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <title>RenalTales Test Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { background: #f5f5f5; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .suite { margin-bottom: 30px; }
        .suite h3 { background: #e0e0e0; padding: 10px; margin: 0; }
        .test-file { margin: 10px 0; padding: 10px; border-left: 4px solid #ddd; }
        .passed { border-left-color: #4CAF50; }
        .failed { border-left-color: #f44336; }
        .skipped { border-left-color: #FF9800; }
        .summary { background: #f9f9f9; padding: 15px; border-radius: 5px; }
        .stats { display: flex; gap: 20px; margin: 10px 0; }
        .stat { padding: 10px; border-radius: 3px; text-align: center; }
        .stat-passed { background: #4CAF50; color: white; }
        .stat-failed { background: #f44336; color: white; }
        .stat-skipped { background: #FF9800; color: white; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="header">
        <h1>RenalTales Test Report</h1>
        <p><strong>Generated:</strong> ' . date('Y-m-d H:i:s') . '</p>
        <p><strong>Environment:</strong> ' . $_ENV['APP_ENV'] . '</p>
        <p><strong>Database:</strong> ' . $_ENV['DB_DATABASE'] . '</p>
    </div>
    
    <div class="summary">
        <h2>Summary</h2>
        <div class="stats">
            <div class="stat stat-passed">
                <div>Passed</div>
                <div>' . $this->passedTests . '</div>
            </div>
            <div class="stat stat-failed">
                <div>Failed</div>
                <div>' . $this->failedTests . '</div>
            </div>
            <div class="stat stat-skipped">
                <div>Skipped</div>
                <div>' . $this->skippedTests . '</div>
            </div>
        </div>
        <p><strong>Total Tests:</strong> ' . $this->totalTests . '</p>
        <p><strong>Success Rate:</strong> ' . ($this->totalTests > 0 ? number_format(($this->passedTests / $this->totalTests) * 100, 1) : 0) . '%</p>
        <p><strong>Total Time:</strong> ' . number_format($totalTime, 2) . 's</p>
    </div>';
        
        foreach ($this->results as $suiteName => $suiteData) {
            $html .= '<div class="suite">
                <h3>' . $suiteName . '</h3>
                <p><strong>Time:</strong> ' . number_format($suiteData['time'], 2) . 's</p>';
            
            foreach ($suiteData['results'] as $result) {
                $statusClass = $result['status'];
                $html .= '<div class="test-file ' . $statusClass . '">
                    <h4>' . $result['file'] . '</h4>
                    <p><strong>Status:</strong> ' . strtoupper($result['status']) . '</p>
                    <p><strong>Tests:</strong> ' . $result['tests'] . ' | 
                       <strong>Passed:</strong> ' . $result['passed'] . ' | 
                       <strong>Failed:</strong> ' . $result['failed'] . ' | 
                       <strong>Skipped:</strong> ' . $result['skipped'] . '</p>';
                
                if (!empty($result['output'])) {
                    $html .= '<pre>' . htmlspecialchars($result['output']) . '</pre>';
                }
                
                $html .= '</div>';
            }
            
            $html .= '</div>';
        }
        
        $html .= '</body></html>';
        
        return $html;
    }
    
    /**
     * Cleanup test environment
     */
    private function cleanupTestEnvironment(): void
    {
        echo "\nCleaning up test environment...\n";
        
        // Clean up test files
        $testDirs = [
            'storage/testing/cache',
            'storage/testing/logs',
            'storage/testing/sessions',
            'storage/testing/uploads'
        ];
        
        foreach ($testDirs as $dir) {
            $fullPath = APP_ROOT . '/' . $dir;
            if (is_dir($fullPath)) {
                $files = glob($fullPath . '/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }
        }
        
        echo "✓ Test environment cleanup complete\n";
    }
}

// Command line interface
if (php_sapi_name() === 'cli') {
    $runner = new TestRunner();
    
    if (isset($argv[1])) {
        switch ($argv[1]) {
            case 'unit':
                $runner->runTestSuite('Unit Tests', 'tests/Unit');
                break;
            case 'integration':
                $runner->runTestSuite('Integration Tests', 'tests/Integration');
                break;
            case 'security':
                $runner->runTestSuite('Security Tests', 'tests/Security');
                break;
            case 'api':
                $runner->runTestSuite('API Tests', 'tests/API');
                break;
            case 'feature':
                $runner->runTestSuite('Feature Tests', 'tests/Feature');
                break;
            case 'database':
                $runner->runTestSuite('Database Tests', 'tests/Database');
                break;
            case 'all':
            default:
                $runner->runAllTests();
                break;
        }
    } else {
        $runner->runAllTests();
    }
} else {
    echo "This script must be run from the command line.\n";
    exit(1);
}
?>
