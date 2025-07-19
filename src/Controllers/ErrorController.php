<?php

declare(strict_types=1);

namespace RenalTales\Controllers;

use RenalTales\Http\Response;
use RenalTales\Helpers\Translation;
use RenalTales\Core\Template;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Error Controller
 *
 * Simple controller for handling errors and displaying error pages.
 * Direct template rendering without complex service layers.
 *
 * @package RenalTales\Controllers
 * @version 2025.v3.1.dev
 * @author Ľubomír Polaščín
 */
class ErrorController
{
    /**
     * @var Translation Translation helper
     */
    private Translation $translation;

    public function __construct()
    {
        $this->translation = $GLOBALS['translation'] ?? new Translation();
    }

    /**
     * Display error page
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $message = $queryParams['message'] ?? 'An error occurred';
        $statusCode = (int)($queryParams['code'] ?? 500);

        return $this->error($message, $statusCode);
    }

    /**
     * Display error page with specific message and status code
     *
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     * @return ResponseInterface
     */
    public function error(string $message = 'An error occurred', int $statusCode = 500): ResponseInterface
    {
        // Prepare error data
        $data = $this->prepareErrorData($message, $statusCode);

        try {
            // Try to render error template
            $template = new Template();
            $html = $template->render('error', $data, true);
        } catch (\Exception $e) {
            // Fallback to simple HTML if template fails
            $html = $this->getSimpleErrorHtml($message, $statusCode);
        }

        return new Response($statusCode, ['Content-Type' => 'text/html; charset=utf-8'], $html);
    }

    /**
     * Prepare error page data as simple associative array
     *
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     * @return array
     */
    private function prepareErrorData(string $message, int $statusCode): array
    {
        $currentLanguage = $this->translation->getCurrentLanguage();

        return [
            // Meta information
            'page_title' => $this->trans('error.title', 'Error') . ' - RenalTales',
            'app_name' => 'RenalTales',
            'language' => $currentLanguage,
            'year' => date('Y'),

            // Error information
            'error_code' => $statusCode,
            'error_message' => $message,
            'error_title' => $this->getErrorTitle($statusCode),
            'error_description' => $this->getErrorDescription($statusCode),

            // Navigation
            'nav_home' => $this->trans('nav.home', 'Home'),
            'back_to_home' => $this->trans('back_to_home', 'Back to Home'),

            // Footer
            'footer_copyright' => $this->trans('footer_copyright', 'Ľubomír Polaščín'),

            // Language switcher
            'language_label' => $this->trans('current_language', 'Language'),
            'current_language' => $currentLanguage,
            'supported_languages' => $this->getSupportedLanguages(),
        ];
    }

    /**
     * Get error title based on status code
     *
     * @param int $statusCode
     * @return string
     */
    private function getErrorTitle(int $statusCode): string
    {
        switch ($statusCode) {
            case 404:
                return $this->trans('error.404.title', 'Page Not Found');
            case 403:
                return $this->trans('error.403.title', 'Access Forbidden');
            case 401:
                return $this->trans('error.401.title', 'Unauthorized');
            case 500:
                return $this->trans('error.500.title', 'Internal Server Error');
            default:
                return $this->trans('error.generic.title', 'Error');
        }
    }

    /**
     * Get error description based on status code
     *
     * @param int $statusCode
     * @return string
     */
    private function getErrorDescription(int $statusCode): string
    {
        switch ($statusCode) {
            case 404:
                return $this->trans('error.404.description', 'The page you are looking for could not be found.');
            case 403:
                return $this->trans('error.403.description', 'You do not have permission to access this resource.');
            case 401:
                return $this->trans('error.401.description', 'You need to be authenticated to access this resource.');
            case 500:
                return $this->trans('error.500.description', 'An internal server error occurred. Please try again later.');
            default:
                return $this->trans('error.generic.description', 'An unexpected error occurred.');
        }
    }

    /**
     * Get supported languages with current language indication
     *
     * @return array
     */
    private function getSupportedLanguages(): array
    {
        $supportedLanguages = [
            'en' => 'English',
            'sk' => 'Slovak',
            'la' => 'Latin',
        ];

        $languages = [];
        $currentLanguage = $this->translation->getCurrentLanguage();

        foreach ($supportedLanguages as $code => $name) {
            $languages[] = [
                'code' => $code,
                'name' => $name,
                'selected' => $code === $currentLanguage
            ];
        }

        return $languages;
    }

    /**
     * Get simple error HTML fallback
     *
     * @param string $message
     * @param int $statusCode
     * @return string
     */
    private function getSimpleErrorHtml(string $message, int $statusCode): string
    {
        $title = $this->getErrorTitle($statusCode);
        $description = $this->getErrorDescription($statusCode);

        return "<!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>Error {$statusCode} - RenalTales</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .error-container { max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #d32f2f; margin-bottom: 20px; }
        p { color: #666; line-height: 1.6; margin-bottom: 15px; }
        .error-code { font-size: 2em; font-weight: bold; color: #d32f2f; margin-bottom: 10px; }
        a { color: #1976d2; text-decoration: none; padding: 10px 20px; background: #e3f2fd; border-radius: 4px; display: inline-block; margin-top: 20px; }
        a:hover { background: #bbdefb; }
    </style>
</head>
<body>
    <div class=\"error-container\">
        <div class=\"error-code\">Error {$statusCode}</div>
        <h1>{$title}</h1>
        <p>{$description}</p>
        <p><strong>Message:</strong> {$message}</p>
        <a href=\"/\">Back to Home</a>
    </div>
</body>
</html>";
    }

    /**
     * Get translated text with fallback
     *
     * @param string $key Translation key
     * @param string $fallback Fallback text
     * @return string
     */
    private function trans(string $key, string $fallback = ''): string
    {
        return $this->translation->translate($key, $fallback);
    }
}
