<?php

declare(strict_types=1);

namespace RenalTales\Views;

use RenalTales\Contracts\ViewInterface;
use RenalTales\Services\LanguageService;
use RenalTales\Models\LanguageModel;

/**
 * Abstract Base View
 *
 * Provides common functionality for all view components including:
 * - Language translation
 * - Data handling
 * - Template rendering utilities
 * - Security helpers
 *
 * @package RenalTales\Views
 * @version 2025.3.1.dev
 * @author Ľubomír Polaščín
 */
abstract class AbstractView implements ViewInterface
{
    protected array $data = [];
    protected ?LanguageModel $languageModel = null;
    protected string $currentLanguage = 'en';
    protected string $appName = 'RenalTales';

    /**
     * Constructor
     *
     * @param LanguageModel|string|null $language Language model or language code
     * @param string $appName Application name
     */
    public function __construct($language = null, string $appName = 'RenalTales')
    {
        $this->appName = $appName;
        $this->initializeLanguage($language);
    }

    /**
     * Initialize language handling
     *
     * @param LanguageModel|string|null $language
     */
    protected function initializeLanguage($language): void
    {
        try {
            if ($language instanceof LanguageModel) {
                $this->languageModel = $language;
                $this->currentLanguage = $language->getCurrentLanguage();
            } elseif (is_string($language)) {
                $this->languageModel = new LanguageModel();
                $this->languageModel->setLanguage($language);
                $this->currentLanguage = $language;
            } else {
                $this->languageModel = new LanguageModel();
                $this->currentLanguage = $this->languageModel->getCurrentLanguage();
            }
        } catch (\Exception $e) {
            error_log("AbstractView: Failed to initialize language - " . $e->getMessage());
            $this->languageModel = null;
            $this->currentLanguage = 'en';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function with(array $data): ViewInterface
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function exists(): bool
    {
        return true; // Override in subclasses if needed
    }

    /**
     * Get translated text
     *
     * @param string $key Translation key
     * @param string $fallback Fallback text
     * @param array<string, string|int|float> $parameters Parameters for replacement
     * @return string Translated text
     */
    protected function trans(string $key, string $fallback = '', array $parameters = []): string
    {
        if ($this->languageModel && method_exists($this->languageModel, 'getText')) {
            try {
                return $this->escapeHtml($this->languageModel->getText($key, $parameters, $fallback));
            } catch (\Exception $e) {
                error_log("AbstractView: Error getting text for key '{$key}' - " . $e->getMessage());
                return $this->escapeHtml($fallback);
            }
        }

        return $this->escapeHtml($fallback);
    }

    /**
     * Escape HTML content
     *
     * @param string $content Content to escape
     * @return string Escaped content
     */
    protected function escapeHtml(string $content): string
    {
        return htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Escape HTML attributes
     *
     * @param string $attribute Attribute to escape
     * @return string Escaped attribute
     */
    protected function escapeAttr(string $attribute): string
    {
        return htmlspecialchars($attribute, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Get current language
     *
     * @return string Current language code
     */
    protected function getCurrentLanguage(): string
    {
        return $this->currentLanguage;
    }

    /**
     * Get application name
     *
     * @return string Application name
     */
    protected function getAppName(): string
    {
        return $this->escapeHtml($this->appName);
    }

    /**
     * Get data value
     *
     * @param string $key Data key
     * @param mixed $default Default value
     * @return mixed Data value
     */
    protected function getData(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Check if data key exists
     *
     * @param string $key Data key
     * @return bool True if exists, false otherwise
     */
    protected function hasData(string $key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * Generate a CSRF token input field
     *
     * @return string CSRF token input HTML
     */
    protected function csrfField(): string
    {
        $token = $this->getData('csrf_token', '');
        return "<input type=\"hidden\" name=\"csrf_token\" value=\"{$this->escapeAttr($token)}\">";
    }

    /**
     * Generate asset URL
     *
     * @param string $path Asset path
     * @return string Asset URL
     */
    protected function asset(string $path): string
    {
        return '/' . ltrim($path, '/');
    }

    /**
     * Generate route URL
     *
     * @param string $path Route path
     * @param array<string, string> $params Query parameters
     * @return string Route URL
     */
    protected function route(string $path, array $params = []): string
    {
        $url = '/' . ltrim($path, '/');

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }

    /**
     * Include a partial view
     *
     * @param string $partialName Partial view name
     * @param array<string, mixed> $data Data for partial
     * @return string Rendered partial
     */
    protected function partial(string $partialName, array $data = []): string
    {
        // This is a placeholder implementation
        // In a real application, you would load and render the partial
        return "<!-- Partial: {$partialName} -->";
    }

    /**
     * Format date for display
     *
     * @param \DateTime|string $date Date to format
     * @param string $format Date format
     * @return string Formatted date
     */
    protected function formatDate($date, string $format = 'Y-m-d H:i:s'): string
    {
        if ($date instanceof \DateTime) {
            return $date->format($format);
        }

        if (is_string($date)) {
            try {
                $dateTime = new \DateTime($date);
                return $dateTime->format($format);
            } catch (\Exception $e) {
                return $date;
            }
        }

        return '';
    }

    /**
     * Get supported languages with names
     *
     * @return array<string, string> Language codes and names
     */
    protected function getSupportedLanguages(): array
    {
        if ($this->languageModel) {
            $languages = $this->languageModel->getSupportedLanguages();
            $result = [];

            foreach ($languages as $code) {
                $result[$code] = $this->languageModel->getLanguageName($code);
            }

            return $result;
        }

        return [
            'en' => 'English',
            'sk' => 'Slovak',
            'la' => 'Latin',
        ];
    }

    /**
     * Create error template for safe error display
     *
     * @param string $title Error title
     * @param string $message Error message
     * @return string Error HTML template
     */
    protected function createErrorTemplate(string $title, string $message): string
    {
        $safeTitle = $this->escapeHtml($title);
        $safeMessage = $this->escapeHtml($message);
        $safeAppName = $this->getAppName();

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$safeTitle} - {$safeAppName}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .error-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 40px;
            text-align: center;
            margin-top: 50px;
        }
        .error-title {
            font-size: 28px;
            color: #e74c3c;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .error-message {
            font-size: 18px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.5;
        }
        .btn {
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            background-color: #3498db;
            color: white;
            margin: 0 10px;
            transition: all 0.3s ease;
        }
        .btn:hover {
            background-color: #2980b9;
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
}
