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
    
    protected ?object $view;
    
    /**
     * Render a view with optional data
     * 
     * @param string $viewPath
     * @param array $data
     */
    public function render(string $viewPath, array $data = []): void {
        extract($data);
        $fullPath = "views/{$viewPath}.php";
        
        if (file_exists($fullPath)) {
            include $fullPath;
        } else {
            error_log("BaseController: View file not found: " . $fullPath);
            throw new \RuntimeException("View file not found: " . $viewPath);
        }
    }
}

