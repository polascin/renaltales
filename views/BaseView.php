<?php

/**
 * BaseView - Base class for all views
 * 
 * Provides common functionality for rendering views
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0test
 */

abstract class BaseView {
    
    protected $data = [];
    protected $languageModel;
    protected $sessionManager;
    
    /**
     * Constructor
     * 
     * @param LanguageModel $languageModel
     * @param SessionManager $sessionManager
     */
    public function __construct($languageModel = null, $sessionManager = null) {
        $this->languageModel = $languageModel;
        $this->sessionManager = $sessionManager;
    }
    
    /**
     * Set data for the view
     * 
     * @param array $data
     */
    public function setData($data) {
        $this->data = $data;
    }
    
    /**
     * Get data value
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null) {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }
    
    /**
     * Get translated text with fallback
     * 
     * @param string $key
     * @param string $fallback
     * @return string
     */
    public function getText($key, $fallback = '') {
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
    protected function getServerVar($key, $default = 'N/A') {
        return isset($_SERVER[$key]) ? htmlspecialchars($_SERVER[$key], ENT_QUOTES, 'UTF-8') : $default;
    }
    
    /**
     * Escape HTML output
     * 
     * @param string $string
     * @return string
     */
    protected function escape($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Include a partial view
     * 
     * @param string $partial
     * @param array $data
     */
    protected function partial($partial, $data = []) {
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
    public function render() {
        ob_start();
        $this->renderContent();
        return ob_get_clean();
    }
    
    /**
     * Abstract method to be implemented by child classes
     */
    abstract protected function renderContent();
}

?>
