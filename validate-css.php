<?php
/**
 * CSS Validation Script
 */

$errors = 0;
$warnings = 0;
$passes = 0;

function checkResult($condition, $passMsg, $failMsg)
{
    global $errors, $passes;
    if ($condition) {
        echo "✓ $passMsg\n";
        $passes++;
    } else {
        echo "✗ $failMsg\n";
        $errors++;
    }
}

function checkWarning($condition, $passMsg, $warnMsg)
{
    global $warnings, $passes;
    if ($condition) {
        echo "✓ $passMsg\n";
        $passes++;
    } else {
        echo "⚠ $warnMsg\n";
        $warnings++;
    }
}

echo "=== CSS Structure Validation ===\n\n";

// Test 1: Check main CSS file exists
$mainCss = __DIR__ . '/main.css';
checkResult(file_exists($mainCss), "main.css exists", "main.css not found");

if (file_exists($mainCss)) {
    $content = file_get_contents($mainCss);

    // Test 2: Check essential imports
    $requiredImports = [
        'themes.css' => "url('public/assets/css/themes.css')",
        'variables.css' => "url('core/variables.css')",
        'reset.css' => "url('core/reset.css')",
        'typography.css' => "url('core/typography.css')",
        'responsive.css' => "url('public/assets/css/base/responsive.css')"
    ];

    echo "\n--- Testing imports ---\n";
    foreach ($requiredImports as $file => $importPath) {
        checkResult(strpos($content, $importPath) !== false, "$file import found", "$file import missing");
    }
}

// Test 3: Check CSS files exist
echo "\n--- Testing file existence ---\n";
$cssFiles = [
    'public/assets/css/themes.css',
    'public/assets/css/basic.css',
    'public/assets/css/layout.css',
    'public/assets/css/base/responsive.css',
    'core/variables.css',
    'core/reset.css',
    'core/typography.css',
    'components/buttons.css',
    'components/forms.css',
    'layout/spacing.css'
];

foreach ($cssFiles as $file) {
    $fullPath = __DIR__ . '/' . $file;
    checkResult(file_exists($fullPath), "$file exists", "$file missing");
}

// Test 4: Basic syntax check
echo "\n--- Testing syntax ---\n";
foreach ($cssFiles as $file) {
    $fullPath = __DIR__ . '/' . $file;
    if (file_exists($fullPath)) {
        $content = file_get_contents($fullPath);
        $openBraces = substr_count($content, '{');
        $closeBraces = substr_count($content, '}');
        checkResult($openBraces === $closeBraces, "$file syntax valid", "$file brace mismatch");
    }
}

// Test 5: Check theme variables
echo "\n--- Testing theme variables ---\n";
$themesFile = __DIR__ . '/public/assets/css/themes.css';
if (file_exists($themesFile)) {
    $content = file_get_contents($themesFile);
    $requiredVars = ['primary-color', 'secondary-color', 'background-color', 'text-color'];

    foreach ($requiredVars as $var) {
        checkResult(strpos($content, "--$var") !== false, "Variable --$var found", "Variable --$var missing");
    }
}

echo "\n=== SUMMARY ===\n";
echo "Passes: $passes\n";
echo "Warnings: $warnings\n";
echo "Errors: $errors\n";

if ($errors === 0) {
    echo "\n✅ CSS VALIDATION PASSED!\n";
} else {
    echo "\n❌ CSS VALIDATION FAILED - Fix $errors errors\n";
}
?>

