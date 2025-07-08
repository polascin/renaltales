<?php

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
    
    private $exception;
    private $isDebugMode;
    
    /**
     * Constructor
     * 
     * @param Exception $exception
     * @param bool $isDebugMode
     * @param LanguageModel $languageModel
     */
    public function __construct($exception, $isDebugMode = false, $languageModel = null) {
        parent::__construct($languageModel);
        $this->exception = $exception;
        $this->isDebugMode = $isDebugMode;
    }
    
    /**
     * Render error page content
     */
    protected function renderContent() {
        if ($this->isDebugMode) {
            $this->renderDebugError();
        } else {
            $this->renderProductionError();
        }
    }
    
    /**
     * Render detailed error page for debug mode
     */
    private function renderDebugError() {
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
                pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow: auto; }
            </style>
        </head>
        <body>
            <div class="error">
                <h1><?php echo $this->escape($this->getText('application_error', 'Application Error')); ?></h1>
                <p><strong><?php echo $this->escape($this->getText('error', 'Error')); ?>:</strong> <?php echo $this->escape($this->exception->getMessage()); ?></p>
                <p><strong><?php echo $this->escape($this->getText('file', 'File')); ?>:</strong> <?php echo $this->escape($this->exception->getFile()); ?></p>
                <p><strong><?php echo $this->escape($this->getText('line', 'Line')); ?>:</strong> <?php echo $this->exception->getLine(); ?></p>
                <h3><?php echo $this->escape($this->getText('stack_trace', 'Stack Trace')); ?></h3>
                <pre><?php echo $this->escape($this->exception->getTraceAsString()); ?></pre>
            </div>
        </body>
        </html>
        <?php
    }
    
    /**
     * Render simple error page for production
     */
    private function renderProductionError() {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title><?php echo $this->escape($this->getText('service_unavailable', 'Service Temporarily Unavailable')); ?></title>
            <style>
                body { font-family: Arial, sans-serif; margin: 50px; text-align: center; }
                .error { color: #d9534f; }
            </style>
        </head>
        <body>
            <div class="error">
                <h1><?php echo $this->escape($this->getText('service_unavailable', 'Service Temporarily Unavailable')); ?></h1>
                <p><?php echo $this->escape($this->getText('try_again_later', 'Please try again later.')); ?></p>
            </div>
        </body>
        </html>
        <?php
    }
}

?>
