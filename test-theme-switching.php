<?php

/**
 * Test theme switching functionality and variable consistency
 */

echo "=== Theme Switching Test ===\n\n";

// Check themes.css structure
$themesFile = __DIR__ . '/public/assets/css/themes.css';

if (file_exists($themesFile)) {
    $content = file_get_contents($themesFile);

    echo "--- Theme Structure Analysis ---\n";

    // Check for :root selector
    if (strpos($content, ':root') !== false) {
        echo "✓ :root selector found (global variables)\n";
    } else {
        echo "✗ :root selector not found\n";
    }

    // Check for data-theme selectors
    $themeSelectors = [
        '[data-theme="light"]',
        '[data-theme="dark"]',
        'body[data-theme="light"]',
        'body[data-theme="dark"]'
    ];

    $foundSelectors = 0;
    foreach ($themeSelectors as $selector) {
        if (strpos($content, $selector) !== false) {
            echo "✓ Theme selector found: $selector\n";
            $foundSelectors++;
        }
    }

    if ($foundSelectors === 0) {
        echo "⚠ No theme-specific selectors found\n";
    }

    echo "\n--- CSS Variable Coverage ---\n";

    // Essential theme variables
    $themeVariables = [
        // Core colors
        '--color-primary',
        '--color-secondary',
        '--color-background',
        '--color-text',
        '--color-border',

        // Legacy compatibility
        '--primary-color',
        '--secondary-color',
        '--background-color',
        '--text-color',
        '--border-color',

        // Surface colors
        '--color-surface',
        '--card-bg-color',
        '--button-text-color'
    ];

    foreach ($themeVariables as $variable) {
        $count = substr_count($content, $variable);
        if ($count > 0) {
            echo "✓ $variable defined ($count times)\n";
        } else {
            echo "⚠ $variable not found\n";
        }
    }

    echo "\n--- Theme Completeness Check ---\n";

    // Check if both light and dark themes have same variables
    preg_match_all('/--([a-zA-Z0-9\-]+)\s*:\s*([^;]+);/', $content, $matches);
    $allVars = array_unique($matches[1]);

    echo "Found " . count($allVars) . " unique CSS variables\n";

    // Look for theme-specific sections
    $lightSection = strpos($content, 'light') !== false;
    $darkSection = strpos($content, 'dark') !== false;

    if ($lightSection && $darkSection) {
        echo "✓ Both light and dark theme sections present\n";
    } else {
        echo "⚠ Missing theme sections (light: " . ($lightSection ? "yes" : "no") . ", dark: " . ($darkSection ? "yes" : "no") . ")\n";
    }

} else {
    echo "✗ themes.css not found\n";
}

// Check for theme switcher JavaScript
echo "\n--- Theme Switcher JavaScript ---\n";

$jsFiles = [
    'public/assets/js/theme-switcher.js',
    'assets/js/theme-switcher.js',
    'js/theme-switcher.js'
];

$jsFound = false;
foreach ($jsFiles as $jsFile) {
    $fullPath = __DIR__ . '/' . $jsFile;
    if (file_exists($fullPath)) {
        echo "✓ Theme switcher JS found: $jsFile\n";
        $jsContent = file_get_contents($fullPath);

        // Check for essential JS functions
        $jsFunctions = [
            'toggle' => 'Theme toggle functionality',
            'localStorage' => 'Theme persistence',
            'data-theme' => 'Theme attribute manipulation',
            'prefers-color-scheme' => 'System theme detection'
        ];

        foreach ($jsFunctions as $func => $description) {
            if (strpos($jsContent, $func) !== false) {
                echo "  ✓ $description detected\n";
            } else {
                echo "  ⚠ $description not found\n";
            }
        }

        $jsFound = true;
        break;
    }
}

if (!$jsFound) {
    echo "⚠ Theme switcher JavaScript not found\n";
}

// Test HTML pages for theme integration
echo "\n--- HTML Template Integration ---\n";

$htmlFiles = [
    'test-theme-system.html',
    'theme-test.html',
    'homepage.html'
];

foreach ($htmlFiles as $htmlFile) {
    $fullPath = __DIR__ . '/' . $htmlFile;
    if (file_exists($fullPath)) {
        $htmlContent = file_get_contents($fullPath);

        echo "• Testing $htmlFile:\n";

        // Check for theme-related attributes
        if (strpos($htmlContent, 'data-theme') !== false) {
            echo "  ✓ data-theme attribute found\n";
        }

        // Check for CSS import
        if (strpos($htmlContent, 'main.css') !== false || strpos($htmlContent, 'themes.css') !== false) {
            echo "  ✓ CSS files linked\n";
        }

        // Check for theme switcher button
        if (strpos($htmlContent, 'theme-toggle') !== false || strpos($htmlContent, 'Toggle theme') !== false) {
            echo "  ✓ Theme toggle button found\n";
        }

        // Check for JS integration
        if (strpos($htmlContent, 'theme-switcher.js') !== false) {
            echo "  ✓ Theme switcher JS linked\n";
        }
    }
}

echo "\n--- Browser Compatibility ---\n";

if (file_exists($themesFile)) {
    $content = file_get_contents($themesFile);

    // Check for CSS custom property support
    if (strpos($content, 'var(--') !== false) {
        echo "✓ CSS custom properties used (IE11+ support)\n";
    }

    // Check for modern CSS features
    if (strpos($content, 'prefers-color-scheme') !== false) {
        echo "✓ System theme preference support (modern browsers)\n";
    }

    // Check for fallbacks
    $fallbackPattern = '/color:\s*[^;]+;\s*color:\s*var\(/';
    if (preg_match($fallbackPattern, $content)) {
        echo "✓ CSS custom property fallbacks detected\n";
    } else {
        echo "⚠ Consider adding fallbacks for CSS custom properties\n";
    }
}

echo "\n=== Theme Test Summary ===\n";
echo "Theme switching tests completed.\n\n";
echo "To fully test theme functionality:\n";
echo "1. Open test-theme-system.html in a browser\n";
echo "2. Click the theme toggle button\n";
echo "3. Verify colors change between light and dark\n";
echo "4. Check that theme preference persists on page reload\n";
echo "5. Test system preference detection\n";
