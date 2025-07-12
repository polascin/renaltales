<?php

declare(strict_types=1);

require_once 'BaseView.php';

/**
 * ErrorView - Error display view
 * 
 * Handles rendering of error pages
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0test
 */

class ErrorView extends BaseView {
    
    private Exception $exception;
    private bool $isDebugMode;
    
    /**
     * Constructor
     * 
     * @param Exception $exception
     * @param bool $isDebugMode
     * @param object|null $languageModel
     */
    public function __construct(Exception $exception, bool $isDebugMode = false, ?object $languageModel = null) {
        $this->exception = $exception;
        $this->isDebugMode = $isDebugMode;
        parent::__construct($languageModel, null, null);
    }
    
    /**
     * Render the error page content
     */
    protected function renderContent(): void {
        $errorTitle = $this->getText('error_title', 'Application Error');
        $errorMessage = $this->exception->getMessage();
        $errorFile = $this->exception->getFile();
        $errorLine = $this->exception->getLine();
        $errorType = get_class($this->exception);
        
        // Sanitize error details for display
        $errorMessage = htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8');
        $errorFile = htmlspecialchars($errorFile, ENT_QUOTES, 'UTF-8');
        $errorType = htmlspecialchars($errorType, ENT_QUOTES, 'UTF-8');
        
        echo '<!DOCTYPE html>';
        echo '<html lang="en">';
        echo '<head>';
        echo '<meta charset="utf-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
        echo '<title>' . htmlspecialchars($errorTitle, ENT_QUOTES, 'UTF-8') . '</title>';
        echo '<style>';
        echo 'body { font-family: Arial, sans-serif; margin: 20px; }';
        echo '.error { color: #d9534f; border: 1px solid #d9534f; padding: 20px; border-radius: 5px; margin-bottom: 20px; }';
        echo 'pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow: auto; max-height: 400px; }';
        echo '.error-details { margin-bottom: 10px; }';
        echo '</style>';
        echo '</head>';
        echo '<body>';
        echo '<div class="error">';
        echo '<h1>' . htmlspecialchars($errorTitle, ENT_QUOTES, 'UTF-8') . '</h1>';
        echo '<div class="error-details">';
        echo '<p><strong>Error:</strong> ' . $errorMessage . '</p>';
        echo '<p><strong>File:</strong> ' . $errorFile . '</p>';
        echo '<p><strong>Line:</strong> ' . $errorLine . '</p>';
        echo '<p><strong>Exception Type:</strong> ' . $errorType . '</p>';
        echo '</div>';
        
        if ($this->isDebugMode) {
            echo '<h3>Stack Trace</h3>';
            echo '<pre>' . htmlspecialchars($this->exception->getTraceAsString(), ENT_QUOTES, 'UTF-8') . '</pre>';
        }
        
        echo '</div>';
        echo '</body>';
        echo '</html>';
    }
}
