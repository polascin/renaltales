<?php

declare(strict_types=1);

/**
 * View Helper Functions
 *
 * Simple utility functions to replace AbstractView functionality
 *
 * @package RenalTales\Components
 * @version 2025.v3.1.dev
 * @author Ľubomír Polaščín
 */

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

/**
 * Generate asset URL
 *
 * @param string $path Asset path
 * @return string Asset URL
 */
function asset_url(string $path): string
{
    return '/' . ltrim($path, '/');
}

/**
 * Generate route URL
 *
 * @param string $path Route path
 * @param array $params Query parameters
 * @return string Route URL
 */
function route_url(string $path, array $params = []): string
{
    $url = '/' . ltrim($path, '/');

    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }

    return $url;
}

/**
 * Format date for display
 *
 * @param DateTime|string $date Date to format
 * @param string $format Date format
 * @return string Formatted date
 */
function format_date($date, string $format = 'Y-m-d H:i:s'): string
{
    if ($date instanceof DateTime) {
        return $date->format($format);
    }

    if (is_string($date)) {
        try {
            $dateTime = new DateTime($date);
            return $dateTime->format($format);
        } catch (Exception $e) {
            return $date;
        }
    }

    return '';
}

/**
 * Include template partial with data
 *
 * @param string $partialPath Path to partial file
 * @param array $data Data to pass to partial
 * @return string Rendered partial
 */
function render_partial(string $partialPath, array $data = []): string
{
    if (!file_exists($partialPath)) {
        return "<!-- Partial not found: {$partialPath} -->";
    }

    // Extract data to variables
    extract($data, EXTR_SKIP);

    // Start output buffering
    ob_start();

    // Include the partial
    include $partialPath;

    // Return the captured output
    return ob_get_clean();
}

/**
 * Generate CSRF token field
 *
 * @param string $token CSRF token
 * @return string CSRF field HTML
 */
function csrf_field(string $token = ''): string
{
    $safeToken = esc_attr($token);
    return "<input type=\"hidden\" name=\"csrf_token\" value=\"{$safeToken}\">";
}

/**
 * Check if string is empty or null
 *
 * @param mixed $value Value to check
 * @return bool True if empty, false otherwise
 */
function is_empty($value): bool
{
    return empty($value);
}

/**
 * Get array value with default
 *
 * @param array $array Array to search
 * @param string $key Key to find
 * @param mixed $default Default value
 * @return mixed Value or default
 */
function array_get(array $array, string $key, $default = null)
{
    return $array[$key] ?? $default;
}
