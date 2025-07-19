<?php

declare(strict_types=1);

/**
 * Error Component
 * 
 * Simple function-based component for error page rendering.
 * Replaces heavy ErrorView class with lightweight function.
 * 
 * @package RenalTales\Components
 * @version 2025.v3.1.dev
 * @author Ľubomír Polaščín
 */

use RenalTales\Services\ErrorDataService;

// Include helper functions if not already loaded
if (!function_exists('esc_html')) {
    require_once __DIR__ . '/view_helpers.php';
}

/**
 * Render error page
 *
 * @param Throwable $exception Exception to display
 * @param array $options Options for rendering
 * @return string Rendered HTML
 */
function render_error_page(Throwable $exception, array $options = []): string
{
    // Prepare data
    $debugMode = $options['debug'] ?? false;
    $dataService = new ErrorDataService($debugMode);
    $data = $dataService->getErrorData($exception);
    
    // Start output buffering
    ob_start();
    
    // Include error template
    include __DIR__ . '/../../resources/components/error_layout.php';
    
    return ob_get_clean();
}

/**
 * Render simple error message
 *
 * @param string $title Error title
 * @param string $message Error message
 * @param int $code Error code
 * @return string Rendered HTML
 */
function render_simple_error(string $title, string $message, int $code = 500): string
{
    $safeTitle = esc_html($title);
    $safeMessage = esc_html($message);
    
    return <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{$safeTitle} - RenalTales</title>
        <style>
            body { 
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
                margin: 0; padding: 20px; background: #f5f5f5; 
            }
            .error-container { 
                max-width: 600px; margin: 50px auto; padding: 40px;
                background: white; border-radius: 8px; text-align: center; 
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            .error-title { 
                color: #e74c3c; font-size: 28px; margin-bottom: 20px; 
            }
            .error-message { 
                color: #666; font-size: 18px; margin-bottom: 30px; 
            }
            .btn { 
                padding: 12px 24px; margin: 0 10px; text-decoration: none;
                background: #3498db; color: white; border-radius: 6px; 
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <h1 class="error-title">{$safeTitle}</h1>
            <p class="error-message">{$safeMessage}</p>
            <a href="javascript:history.back()" class="btn">Go Back</a>
            <a href="/" class="btn">Go Home</a>
        </div>
    </body>
    </html>
    HTML;
}

/**
 * Render debug information
 *
 * @param array $debugInfo Debug information
 * @return string Rendered HTML
 */
function render_debug_info(array $debugInfo): string
{
    if (empty($debugInfo)) {
        return '';
    }
    
    $message = esc_html($debugInfo['message'] ?? '');
    $file = esc_html($debugInfo['file'] ?? '');
    $line = (int)($debugInfo['line'] ?? 0);
    $trace = esc_html($debugInfo['trace'] ?? '');
    
    return <<<HTML
    <div class="debug-info" style="
        margin-top: 30px; padding: 20px; background: #f8f9fa; 
        border-radius: 6px; text-align: left; font-size: 14px;
    ">
        <h3 style="color: #e74c3c; margin-bottom: 15px;">Debug Information</h3>
        <p><strong>Message:</strong> {$message}</p>
        <p><strong>File:</strong> {$file}</p>
        <p><strong>Line:</strong> {$line}</p>
        <details style="margin-top: 15px;">
            <summary style="cursor: pointer; font-weight: bold;">Stack Trace</summary>
            <pre style="
                margin-top: 10px; padding: 15px; background: white; 
                border: 1px solid #ddd; border-radius: 4px; 
                overflow-x: auto; font-size: 12px;
            ">{$trace}</pre>
        </details>
    </div>
    HTML;
}
