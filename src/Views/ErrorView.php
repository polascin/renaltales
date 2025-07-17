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
    <link rel="stylesheet" href="/assets/css/basic.css">
    <link rel="stylesheet" href="/assets/css/layout.css">
    <link rel="stylesheet" href="/assets/css/components.css">
    <link rel="stylesheet" href="/assets/css/error.css">
    <link rel="stylesheet" href="/assets/css/responsive.css">
</head>
<body>
    <div class="container">
        <div class="error-container card">
            <div class="card-body">
                <div class="error-icon">⚠️</div>
                <h1 class="error-title">{$errorTitle}</h1>
                <p class="error-message">{$errorMessage}</p>

                <div class="error-buttons d-flex justify-content-center">
                    <a href="javascript:history.back()" class="btn btn-secondary mr-2">{$backButton}</a>
                    <a href="/" class="btn btn-primary">{$homeButton}</a>
                </div>

                <div class="error-code mt-3">
                    <small class="text-muted">Error Code: {$this->exception->getCode()}</small>
                </div>

                {$debugInfo}
            </div>
        </div>
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
