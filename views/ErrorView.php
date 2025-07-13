<?php

declare(strict_types=1);

require_once 'BaseView.php';

/**
 * ErrorView - Error page view
 * 
 * Handles rendering of error pages
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0test
 */

class ErrorView extends BaseView {
    
    private \Exception $exception;
    private bool $isDebugMode;
    
    /**
     * Constructor
     * 
     * @param \Exception $exception
     * @param bool $isDebugMode
     * @param mixed $languageModel
     */
    public function __construct(\Exception $exception, bool $isDebugMode = false, mixed $languageModel = null) {
        parent::__construct($languageModel, null); // No session manager needed for error views
        $this->exception = $exception;
        $this->isDebugMode = $isDebugMode;
    }

    /**
     * Get translated text or fallback to default
     *
     * @param string $key
     * @param string $default
     * @return string
     */
    protected function getText(string $key, string $default): string
    {
        if ($this->languageModel && method_exists($this->languageModel, 'getText')) {
            $text = $this->languageModel->getText($key);
            if (is_string($text) && $text !== '') {
                return $text;
            }
        }
        return $default;
    }
    
    /**
     * Render error page content
     */
    protected function renderContent(): void {
        // Log error regardless of debug mode
        $this->logError();
        
        if ($this->isDebugMode) {
            $this->renderDebugError();
        } else {
            $this->renderProductionError();
        }
    }
    
    /**
     * Log error for debugging purposes
     */
    private function logError(): void {
        $errorMessage = sprintf(
            "Error: %s in %s on line %d. Stack trace: %s",
            $this->exception->getMessage(),
            $this->exception->getFile(),
            $this->exception->getLine(),
            $this->exception->getTraceAsString()
        );
        
        error_log($errorMessage);
    }
    
    /**
     * Render detailed error page for debug mode
     */
    private function renderDebugError(): void {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title><?php echo $this->escape($this->getText('application_error', 'Application Error')); ?></title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .error { color: #d9534f; border: 1px solid #d9534f; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
                pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow: auto; max-height: 400px; }
                .error-details { margin-bottom: 10px; }
            </style>
        </head>
        <body>
            <div class="error">
                <h1><?php echo $this->escape($this->getText('application_error', 'Application Error')); ?></h1>
                <div class="error-details">
                    <p><strong><?php echo $this->escape($this->getText('error', 'Error')); ?>:</strong> <?php echo $this->escape($this->exception->getMessage() ?: 'Unknown error'); ?></p>
                    <p><strong><?php echo $this->escape($this->getText('file', 'File')); ?>:</strong> <?php echo $this->escape($this->exception->getFile() ?: 'Unknown file'); ?></p>
                    <p><strong><?php echo $this->escape($this->getText('line', 'Line')); ?>:</strong> <?php echo $this->escape((string)$this->exception->getLine()); ?></p>
                    <p><strong><?php echo $this->escape($this->getText('exception_type', 'Exception Type')); ?>:</strong> <?php echo $this->escape(get_class($this->exception)); ?></p>
                </div>
                <h3><?php echo $this->escape($this->getText('stack_trace', 'Stack Trace')); ?></h3>
                <pre><?php echo $this->escape($this->exception->getTraceAsString() ?: 'No stack trace available'); ?></pre>
            </div>
        </body>
        </html>
        <?php
    }
    
    /**
     * Render simple error page for production
     */
    private function renderProductionError(): void {
        // Get current language or default to English
        $currentLang = 'en';
        if ($this->languageModel && method_exists($this->languageModel, 'getCurrentLanguage')) {
            $lang = $this->languageModel->getCurrentLanguage();
            $currentLang = is_string($lang) ? $lang : 'en';
        }
        
        ?>
        <!DOCTYPE html>
        <html lang="<?php echo $this->escape($currentLang); ?>">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title><?php echo $this->escape($this->getText('service_unavailable', 'Service Temporarily Unavailable')); ?></title>
            <style>
                body { 
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
                    margin: 0; 
                    padding: 50px 20px; 
                    text-align: center; 
                    background-color: #f8f9fa;
                    color: #343a40;
                }
                .error { 
                    max-width: 600px; 
                    margin: 0 auto; 
                    padding: 40px; 
                    background: white; 
                    border-radius: 8px; 
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                }
                .error h1 { 
                    color: #dc3545; 
                    margin-bottom: 20px; 
                    font-size: 28px;
                }
                .error p { 
                    margin-bottom: 20px; 
                    font-size: 16px; 
                    line-height: 1.5;
                }
                .back-link { 
                    display: inline-block; 
                    margin-top: 20px; 
                    padding: 10px 20px; 
                    background: #007bff; 
                    color: white; 
                    text-decoration: none; 
                    border-radius: 4px; 
                    transition: background 0.3s;
                }
                .back-link:hover { 
                    background: #0056b3; 
                }
            </style>
        </head>
        <body>
            <div class="error">
                <h1><?php echo $this->escape($this->getText('service_unavailable', 'Service Temporarily Unavailable')); ?></h1>
                <p><?php echo $this->escape($this->getText('try_again_later', 'We apologize for the inconvenience. Please try again later.')); ?></p>
                <p><?php echo $this->escape($this->getText('error_reference', 'If the problem persists, please contact support.')); ?></p>
                <a href="/" class="back-link"><?php echo $this->escape($this->getText('back_to_home', 'Back to Home')); ?></a>
            </div>
        </body>
        </html>
        <?php
    }
}
