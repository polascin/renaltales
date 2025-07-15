<?php

declare(strict_types=1);

namespace RenalTales\Views;

use Throwable;

/**
 * Error View Class
 *
 * Handles error display and logging for the application
 *
 * @author Ľubomír Polaščín
 * @package RenalTales
 * @version 2025.3.1.dev
 */
class ErrorView {
  private Throwable $exception;
  private bool $debugMode;
  private $languageModel;

  /**
   * ErrorView constructor
   *
   * @param Throwable $exception The exception to display
   * @param bool $debugMode Whether debug mode is enabled
   * @param mixed $languageModel The language model for translations
   */
  public function __construct(Throwable $exception, bool $debugMode = false, $languageModel = null) {
    $this->exception = $exception;
    $this->debugMode = $debugMode;
    $this->languageModel = $languageModel;
  }

  /**
   * Render the error page
   *
   * @return string The rendered error HTML
   */
  public function render(): string {
    $errorTitle = $this->getText('error.title', 'Application Error');
    $errorMessage = $this->getText('error.message', 'An error occurred while processing your request.');
    $backButton = $this->getText('error.back', 'Go Back');
    $homeButton = $this->getText('error.home', 'Go Home');

    $html = $this->getErrorPageTemplate($errorTitle, $errorMessage, $backButton, $homeButton);

    // Log the error
    $this->logError();

    return $html;
  }

  /**
   * Get translated text
   *
   * @param string $key The translation key
   * @param string $fallback The fallback text
   * @return string The translated text
   */
  private function getText(string $key, string $fallback): string {
    if ($this->languageModel && method_exists($this->languageModel, 'getText')) {
      return $this->languageModel->getText($key, [], $fallback);
    }

    return $fallback;
  }

  /**
   * Get the error page template
   *
   * @param string $errorTitle The error title
   * @param string $errorMessage The error message
   * @param string $backButton The back button text
   * @param string $homeButton The home button text
   * @return string The HTML template
   */
  private function getErrorPageTemplate(
    string $errorTitle,
    string $errorMessage,
    string $backButton,
    string $homeButton
  ): string {
    $debugInfo = '';
    if ($this->debugMode) {
      $debugInfo = $this->getDebugInfo();
    }

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$errorTitle} - RenalTales</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
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

        .error-icon {
            font-size: 64px;
            color: #e74c3c;
            margin-bottom: 20px;
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

        .error-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
        }

        .btn {
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }

        .btn-primary {
            background-color: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background-color: #2980b9;
        }

        .btn-secondary {
            background-color: #95a5a6;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #7f8c8d;
        }

        .debug-info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 20px;
            margin-top: 30px;
            text-align: left;
        }

        .debug-title {
            font-size: 16px;
            font-weight: 600;
            color: #495057;
            margin-bottom: 15px;
        }

        .debug-content {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.4;
            color: #6c757d;
            background: white;
            padding: 15px;
            border-radius: 4px;
            border: 1px solid #e9ecef;
            overflow-x: auto;
        }

        .error-code {
            font-size: 14px;
            color: #999;
            margin-top: 20px;
        }

        @media (max-width: 600px) {
            .error-container {
                padding: 20px;
                margin-top: 20px;
            }

            .error-buttons {
                flex-direction: column;
                align-items: center;
            }

            .btn {
                width: 100%;
                max-width: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">⚠️</div>
        <h1 class="error-title">{$errorTitle}</h1>
        <p class="error-message">{$errorMessage}</p>

        <div class="error-buttons">
            <a href="javascript:history.back()" class="btn btn-secondary">{$backButton}</a>
            <a href="/" class="btn btn-primary">{$homeButton}</a>
        </div>

        <div class="error-code">
            Error Code: {$this->exception->getCode()}
        </div>

        {$debugInfo}
    </div>
</body>
</html>
HTML;
  }

  /**
   * Get debug information
   *
   * @return string The debug information HTML
   */
  private function getDebugInfo(): string {
    $trace = $this->exception->getTraceAsString();
    $message = htmlspecialchars($this->exception->getMessage());
    $file = $this->exception->getFile();
    $line = $this->exception->getLine();

    return <<<HTML
<div class="debug-info">
    <div class="debug-title">Debug Information</div>
    <div class="debug-content">
        <strong>Message:</strong> {$message}<br>
        <strong>File:</strong> {$file}<br>
        <strong>Line:</strong> {$line}<br><br>
        <strong>Stack Trace:</strong><br>
        <pre>{$trace}</pre>
    </div>
</div>
HTML;
  }

  /**
   * Log the error
   */
  private function logError(): void {
    $logMessage = sprintf(
      "[%s] %s: %s in %s:%d\nStack trace:\n%s",
      date('Y-m-d H:i:s'),
      get_class($this->exception),
      $this->exception->getMessage(),
      $this->exception->getFile(),
      $this->exception->getLine(),
      $this->exception->getTraceAsString()
    );

    error_log($logMessage);
  }
}
