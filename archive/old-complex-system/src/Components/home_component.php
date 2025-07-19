<?php

declare(strict_types=1);

/**
 * Home Component
 *
 * Simple function-based component for home page rendering.
 * Replaces heavy HomeView class with lightweight function.
 *
 * @package RenalTales\Components
 * @version 2025.v3.1.dev
 * @author Ľubomír Polaščín
 */

use RenalTales\Services\HomeDataService;

// Include helper functions if not already loaded
if (!function_exists('esc_html')) {
    require_once __DIR__ . '/view_helpers.php';
}

/**
 * Render home page
 *
 * @param array $options Options for rendering
 * @return string Rendered HTML
 */
function render_home_page(array $options = []): string
{
    // Prepare data
    $dataService = new HomeDataService($options['translation'] ?? null);
    $data = $dataService->getHomePageData();

    // Extract template variables
    extract($data, EXTR_PREFIX_ALL, 'page');

    // Start output buffering
    ob_start();

    // Include template partials
    include __DIR__ . '/../../resources/components/home_layout.php';

    return ob_get_clean();
}

/**
 * Render hero section
 *
 * @param array $heroData Hero section data
 * @return string Rendered HTML
 */
function render_hero_section(array $heroData): string
{
    $title = htmlspecialchars($heroData['title'] ?? '');
    $intro = htmlspecialchars($heroData['intro'] ?? '');
    $description = htmlspecialchars($heroData['description'] ?? '');

    return <<<HTML
    <section class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">{$title}</h1>
            <p class="hero-intro">{$intro}</p>
            <p class="hero-description">{$description}</p>
        </div>
    </section>
    HTML;
}

/**
 * Render feature cards
 *
 * @param array $features Feature cards data
 * @return string Rendered HTML
 */
function render_feature_cards(array $features): string
{
    $html = '<section class="features-section"><div class="features-grid">';

    foreach ($features as $feature) {
        $title = htmlspecialchars($feature['title'] ?? '');
        $description = htmlspecialchars($feature['description'] ?? '');
        $link = htmlspecialchars($feature['link'] ?? '#');
        $buttonText = htmlspecialchars($feature['button_text'] ?? '');
        $buttonClass = htmlspecialchars($feature['button_class'] ?? 'btn-primary');

        $html .= <<<HTML
        <div class="feature-card">
            <h3 class="feature-title">{$title}</h3>
            <p class="feature-description">{$description}</p>
            <a href="{$link}" class="btn {$buttonClass}">{$buttonText}</a>
        </div>
        HTML;
    }

    $html .= '</div></section>';
    return $html;
}

/**
 * Escape HTML for safe output
 *
 * @param string $string String to escape
 * @return string Escaped string
 */
function esc_html(string $string): string
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Escape attributes for safe output
 *
 * @param string $string Attribute to escape
 * @return string Escaped attribute
 */
function esc_attr(string $string): string
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
