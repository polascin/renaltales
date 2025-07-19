<?php

declare(strict_types=1);

/**
 * Comprehensive test runner script
 *
 * This script runs all types of tests:
 * - Unit tests
 * - Integration tests
 * - Feature tests
 * - Behat BDD tests
 * - Generates coverage reports
 */

$scriptDir = __DIR__;
$projectRoot = dirname($scriptDir);

// Change to project root
chdir($projectRoot);

echo "🧪 RenalTales Comprehensive Test Suite\n";
echo "=====================================\n\n";

// Check if vendor directory exists
if (!is_dir('vendor')) {
    echo "❌ Error: vendor directory not found. Run 'composer install' first.\n";
    exit(1);
}

// Functions
function runCommand(string $command, string $description): bool
{
    echo "📋 $description...\n";
    echo "   Running: $command\n";

    $output = [];
    $returnCode = 0;
    exec($command . ' 2>&1', $output, $returnCode);

    if ($returnCode === 0) {
        echo "   ✅ Success\n\n";
        return true;
    } else {
        echo "   ❌ Failed (Exit code: $returnCode)\n";
        foreach ($output as $line) {
            echo "   $line\n";
        }
        echo "\n";
        return false;
    }
}

function createDirectoryIfNotExists(string $dir): void
{
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "📁 Created directory: $dir\n";
    }
}

// Ensure test directories exist
$testDirs = [
    'tests/logs',
    'coverage',
    'coverage/html',
];

foreach ($testDirs as $dir) {
    createDirectoryIfNotExists($dir);
}

// Test execution plan
$testPlan = [
    [
        'command' => 'composer phpstan',
        'description' => 'Running PHPStan static analysis',
        'required' => false
    ],
    [
        'command' => 'composer phpcs',
        'description' => 'Running PHP CodeSniffer',
        'required' => false
    ],
    [
        'command' => 'composer test:unit',
        'description' => 'Running unit tests',
        'required' => true
    ],
    [
        'command' => 'composer test:integration',
        'description' => 'Running integration tests',
        'required' => true
    ],
    [
        'command' => 'composer test:feature',
        'description' => 'Running feature tests',
        'required' => false
    ],
    [
        'command' => 'composer test:coverage',
        'description' => 'Running tests with coverage',
        'required' => true
    ],
    [
        'command' => 'composer behat',
        'description' => 'Running Behat BDD tests',
        'required' => false
    ]
];

// Execute tests
$results = [];
$totalTests = count($testPlan);
$successCount = 0;
$failureCount = 0;

foreach ($testPlan as $test) {
    $success = runCommand($test['command'], $test['description']);
    $results[] = [
        'command' => $test['command'],
        'description' => $test['description'],
        'success' => $success,
        'required' => $test['required']
    ];

    if ($success) {
        $successCount++;
    } else {
        $failureCount++;
        if ($test['required']) {
            echo "❌ Critical test failed: {$test['description']}\n";
        }
    }
}

// Generate summary
echo "\n📊 Test Results Summary\n";
echo "======================\n";
echo "Total tests: $totalTests\n";
echo "Passed: $successCount\n";
echo "Failed: $failureCount\n\n";

// Detailed results
echo "📋 Detailed Results:\n";
foreach ($results as $result) {
    $status = $result['success'] ? '✅' : '❌';
    $required = $result['required'] ? ' (Required)' : ' (Optional)';
    echo "$status {$result['description']}$required\n";
}

// Coverage report location
if (is_dir('coverage/html')) {
    echo "\n📈 Coverage Report: coverage/html/index.html\n";
}

// Behat report location
if (is_dir('tests/logs')) {
    echo "📋 Behat Reports: tests/logs/\n";
}

// Final status
$requiredFailures = 0;
foreach ($results as $result) {
    if (!$result['success'] && $result['required']) {
        $requiredFailures++;
    }
}

echo "\n" . str_repeat('=', 50) . "\n";
if ($requiredFailures > 0) {
    echo "❌ TESTS FAILED: $requiredFailures critical test(s) failed\n";
    exit(1);
} else {
    echo "✅ ALL CRITICAL TESTS PASSED\n";
    if ($failureCount > 0) {
        echo "⚠️  Note: $failureCount optional test(s) failed\n";
    }
    exit(0);
}
