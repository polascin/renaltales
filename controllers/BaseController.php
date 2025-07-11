<?php

declare(strict_types=1);

/**
 * BaseController - Base class for all controllers
 * 
 * Provides common functionality for all controllers
 * 
 * @version 2025.v1.0test
 */

abstract class BaseController {
    
    protected mixed $view;
    
    /**
     * Render a view with optional data
     * 
     * @param string $viewPath
     * @param array $data
     */
    public function render(string $viewPath, array $data = []): void {
        extract($data);
        include "views/{$viewPath}.php";
    }
}

