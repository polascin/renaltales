<?php

/**
 * Script to set up pre-commit hooks for PSR compliance
 */

declare(strict_types=1);

function setupPreCommitHooks(): void
{
    $rootDir = dirname(__DIR__);
    $gitHooksDir = $rootDir . '/.git/hooks';
    $preCommitScript = $rootDir . '/scripts/pre-commit';
    $preCommitBatScript = $rootDir . '/scripts/pre-commit.bat';
    $preCommitHook = $gitHooksDir . '/pre-commit';

    echo "Setting up pre-commit hooks for PSR compliance...\n";

    // Check if .git directory exists
    if (!is_dir($rootDir . '/.git')) {
        echo "Error: This is not a Git repository.\n";
        echo "Please run 'git init' first.\n";
        exit(1);
    }

    // Create hooks directory if it doesn't exist
    if (!is_dir($gitHooksDir)) {
        if (!mkdir($gitHooksDir, 0755, true)) {
            echo "Error: Could not create hooks directory.\n";
            exit(1);
        }
    }

    // Determine which script to use based on OS
    $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

    if ($isWindows) {
        // Windows setup
        $hookContent = "@echo off\n";
        $hookContent .= "php \"" . str_replace('/', '\\', $preCommitBatScript) . "\"\n";
        $hookContent .= "exit /b %errorlevel%\n";

        echo "Setting up Windows pre-commit hook...\n";
    } else {
        // Unix/Linux/Mac setup
        $hookContent = "#!/bin/bash\n";
        $hookContent .= "\"$preCommitScript\"\n";
        $hookContent .= "exit \$?\n";

        echo "Setting up Unix/Linux pre-commit hook...\n";
    }

    // Write the hook file
    if (file_put_contents($preCommitHook, $hookContent) === false) {
        echo "Error: Could not write pre-commit hook file.\n";
        exit(1);
    }

    // Make the hook executable (Unix/Linux/Mac only)
    if (!$isWindows) {
        if (!chmod($preCommitHook, 0755)) {
            echo "Warning: Could not make pre-commit hook executable.\n";
        }

        // Also make the script executable
        if (file_exists($preCommitScript)) {
            chmod($preCommitScript, 0755);
        }
    }

    echo "✓ Pre-commit hook installed successfully!\n";
    echo "\nThe hook will now run before each commit to check:\n";
    echo "  - PHP syntax errors\n";
    echo "  - PSR-12 coding standards\n";
    echo "  - Static analysis (PHPStan)\n";
    echo "  - Forbidden functions (var_dump, print_r, etc.)\n";
    echo "  - Merge conflict markers\n";
    echo "  - File permissions\n";
    echo "\nTo bypass the hook (not recommended), use:\n";
    echo "  git commit --no-verify\n";
    echo "\nTo uninstall the hook, simply delete:\n";
    echo "  " . $preCommitHook . "\n";
}

function testHook(): void
{
    echo "\nTesting pre-commit hook...\n";

    $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    $rootDir = dirname(__DIR__);

    if ($isWindows) {
        $command = 'cd /d "' . $rootDir . '" && scripts\\pre-commit.bat';
    } else {
        $command = 'cd "' . $rootDir . '" && scripts/pre-commit';
    }

    $output = [];
    $returnCode = 0;
    exec($command, $output, $returnCode);

    foreach ($output as $line) {
        echo $line . "\n";
    }

    if ($returnCode === 0) {
        echo "\n✓ Pre-commit hook test passed!\n";
    } else {
        echo "\n✗ Pre-commit hook test failed (exit code: $returnCode)\n";
    }
}

// Main execution
if (php_sapi_name() !== 'cli') {
    echo "This script must be run from the command line.\n";
    exit(1);
}

// Parse command line arguments
$options = getopt('ht', ['help', 'test']);

if (isset($options['h']) || isset($options['help'])) {
    echo "Usage: php setup-hooks.php [options]\n";
    echo "\nOptions:\n";
    echo "  -h, --help    Show this help message\n";
    echo "  -t, --test    Test the pre-commit hook\n";
    echo "\nThis script sets up pre-commit hooks to check PSR compliance.\n";
    exit(0);
}

if (isset($options['t']) || isset($options['test'])) {
    testHook();
    exit(0);
}

// Default action: setup hooks
setupPreCommitHooks();
