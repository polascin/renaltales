<?php

/**
 * BaseController - Base class for all controllers
 * 
 * Provides common functionality for all controllers
 * 
 * @version 2025.v1.0test
 */

abstract class BaseController {
    
    protected $view;
    
    /**
     * Render a view with optional data
     * 
     * @param string $viewPath
     * @param array $data
     */
    public function render($viewPath, $data = []) {
        extract($data);
        include "views/{$viewPath}.php";
    }
}

?>

