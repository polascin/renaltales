<?php
/**
 * Test responsive design by checking CSS media queries
 */

echo "=== Responsive Design Test ===\n\n";

// Test responsive.css content
$responsiveCss = __DIR__ . '/public/assets/css/base/responsive.css';

if (file_exists($responsiveCss)) {
    $content = file_get_contents($responsiveCss);

    echo "--- Media Query Analysis ---\n";

    // Find all media queries
    preg_match_all('/@media[^{]+\{/', $content, $matches);

    echo "Found " . count($matches[0]) . " media queries:\n\n";

    foreach ($matches[0] as $query) {
        $query = trim(str_replace(['{', '@media'], '', $query));
        echo "• $query\n";
    }

    echo "\n--- Breakpoint Analysis ---\n";

    $breakpoints = [
        '320px' => 'Mobile Small',
        '480px' => 'Mobile Large',
        '768px' => 'Tablet',
        '1024px' => 'Desktop',
        '1200px' => 'Large Desktop'
    ];

    foreach ($breakpoints as $size => $description) {
        if (strpos($content, $size) !== false) {
            echo "✓ $description breakpoint ($size) found\n";
        } else {
            echo "⚠ $description breakpoint ($size) not found\n";
        }
    }

    echo "\n--- Responsive Features Check ---\n";

    $features = [
        'max-width' => 'Max-width constraints',
        'min-width' => 'Min-width constraints',
        'flex-direction' => 'Flex direction changes',
        'grid-template-columns' => 'Grid column adjustments',
        'display: none' => 'Element hiding',
        'display: block' => 'Element showing',
        'font-size' => 'Font size adjustments'
    ];

    foreach ($features as $css => $description) {
        $count = substr_count($content, $css);
        if ($count > 0) {
            echo "✓ $description ($count instances)\n";
        } else {
            echo "○ $description (not used)\n";
        }
    }

} else {
    echo "✗ responsive.css not found\n";
}

// Check main.css for responsive imports
echo "\n--- Main CSS Responsive Structure ---\n";
$mainCss = __DIR__ . '/main.css';
if (file_exists($mainCss)) {
    $content = file_get_contents($mainCss);

    if (strpos($content, 'responsive.css') !== false) {
        echo "✓ Responsive CSS imported in main.css\n";
    } else {
        echo "✗ Responsive CSS not imported in main.css\n";
    }

    // Check order of responsive import (should be near end)
    $lines = explode("\n", $content);
    $totalLines = count($lines);
    $responsiveLine = 0;

    foreach ($lines as $lineNum => $line) {
        if (strpos($line, 'responsive.css') !== false) {
            $responsiveLine = $lineNum + 1;
            break;
        }
    }

    if ($responsiveLine > 0) {
        $position = round(($responsiveLine / $totalLines) * 100);
        echo "✓ Responsive import positioned at $position% of file (line $responsiveLine of $totalLines)\n";

        if ($position > 70) {
            echo "  → Good: Responsive styles loaded after base styles\n";
        } else {
            echo "  ⚠ Consider moving responsive import later in cascade\n";
        }
    }
}

echo "\n--- Component Responsive Features ---\n";

// Check component files for responsive features
$componentFiles = [
    'components/buttons.css',
    'components/forms.css',
    'components/cards.css',
    'components/navigation.css'
];

foreach ($componentFiles as $file) {
    $fullPath = __DIR__ . '/' . $file;
    if (file_exists($fullPath)) {
        $content = file_get_contents($fullPath);
        $hasMedia = strpos($content, '@media') !== false;
        $hasFlexible = strpos($content, 'flex') !== false || strpos($content, 'grid') !== false;

        echo "• " . basename($file) . ": ";
        if ($hasMedia) {
            echo "Media queries ✓ ";
        }
        if ($hasFlexible) {
            echo "Flexible layout ✓";
        }
        if (!$hasMedia && !$hasFlexible) {
            echo "No responsive features";
        }
        echo "\n";
    }
}

echo "\n=== Responsive Test Summary ===\n";
echo "All tests completed. For full responsive testing:\n";
echo "1. Open the test pages in a browser\n";
echo "2. Use browser developer tools to test different screen sizes\n";
echo "3. Test on actual mobile devices\n";
echo "4. Check touch interactions and mobile navigation\n";

?>

