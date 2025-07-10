<?php

declare(strict_types=1);

/**
 * BaseView - Base class for all views
 * 
 * Provides common functionality for rendering views
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0test
 */

abstract class BaseView {
    
    protected array $data = [];
    protected mixed $languageModel;
    protected mixed $sessionManager;
    protected mixed $authenticationManager;
    
    /**
     * Constructor
     * 
     * @param mixed $languageModel
     * @param mixed $sessionManager
     * @param mixed $authenticationManager
     */
    public function __construct(mixed $languageModel = null, mixed $sessionManager = null, mixed $authenticationManager = null) {
        $this->languageModel = $languageModel;
        $this->sessionManager = $sessionManager;
        $this->authenticationManager = $authenticationManager;
    }
    
    /**
     * Set data for the view
     * 
     * @param array $data
     */
    public function setData(array $data): void {
        $this->data = $data;
    }
    
    /**
     * Get data value
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }
    
    /**
     * Get translated text with fallback
     * 
     * @param string $key
     * @param string $fallback
     * @return string
     */
    public function getText(string $key, string $fallback = ''): string {
        if ($this->languageModel) {
            return $this->languageModel->getText($key, $fallback);
        }
        return $fallback;
    }
    
    /**
     * Safely get server variable
     * 
     * @param string $key
     * @param string $default
     * @return string
     */
    protected function getServerVar(string $key, string $default = 'N/A'): string {
        return isset($_SERVER[$key]) ? htmlspecialchars($_SERVER[$key], ENT_QUOTES, 'UTF-8') : $default;
    }
    
    /**
     * Escape HTML output
     * 
     * @param string $string
     * @return string
     */
    protected function escape(string $string): string {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Include a partial view
     * 
     * @param string $partial
     * @param array $data
     */
    protected function partial(string $partial, array $data = []): void {
        $oldData = $this->data;
        $this->data = array_merge($this->data, $data);
        include "partials/{$partial}.php";
        $this->data = $oldData;
    }
    
    /**
     * Render the complete view
     * 
     * @return string
     */
    public function render(): string {
        ob_start();
        $this->renderContent();
        return ob_get_clean();
    }
    
    /**
     * Abstract method to be implemented by child classes
     */
    abstract protected function renderContent(): void;
}

?>
