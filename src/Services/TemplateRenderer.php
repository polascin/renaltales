<?php

declare(strict_types=1);

namespace RenalTales\Services;

use RenalTales\Contracts\TemplateRendererInterface;
use Exception;

/**
 * Template Renderer Service
 *
 * A lightweight template rendering system with support for:
 * - Variable substitution using {{variable}} syntax
 * - Partial inclusion using {{>partial}} syntax
 * - Conditional sections using {{#condition}} {{/condition}}
 * - Loops using {{#items}} {{/items}}
 *
 * @author Ľubomír Polaščín
 * @package RenalTales\Services
 * @version 2025.v3.1.dev
 */
class TemplateRenderer implements TemplateRendererInterface
{
    private string $templateDirectory;
    private array $partials = [];
    private array $cache = [];

    /**
     * TemplateRenderer constructor
     *
     * @param string $templateDirectory The directory containing templates
     */
    public function __construct(string $templateDirectory = '')
    {
        $this->templateDirectory = $templateDirectory ?: __DIR__ . '/../../resources/templates';
    }

    /**
     * {@inheritdoc}
     */
    public function render(string $template, array $data = []): string
    {
        $templateContent = $this->loadTemplate($template);
        return $this->compileTemplate($templateContent, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function registerPartial(string $name, string $template): void
    {
        $this->partials[$name] = $template;
    }

    /**
     * {@inheritdoc}
     */
    public function setTemplateDirectory(string $directory): void
    {
        $this->templateDirectory = $directory;
        $this->cache = []; // Clear cache when directory changes
    }

    /**
     * {@inheritdoc}
     */
    public function templateExists(string $template): bool
    {
        $templatePath = $this->getTemplatePath($template);
        return file_exists($templatePath);
    }

    /**
     * Load a template from file
     *
     * @param string $template The template name
     * @return string The template content
     * @throws Exception When template cannot be found
     */
    private function loadTemplate(string $template): string
    {
        // Check cache first
        if (isset($this->cache[$template])) {
            return $this->cache[$template];
        }

        $templatePath = $this->getTemplatePath($template);
        
        if (!file_exists($templatePath)) {
            throw new Exception("Template not found: {$template} (looked in: {$templatePath})");
        }

        $content = file_get_contents($templatePath);
        if ($content === false) {
            throw new Exception("Cannot read template: {$template}");
        }

        // Cache the content
        $this->cache[$template] = $content;
        return $content;
    }

    /**
     * Get the full path to a template
     *
     * @param string $template The template name
     * @return string The full template path
     */
    private function getTemplatePath(string $template): string
    {
        // If it's already a path, return as-is
        if (str_contains($template, '/') || str_contains($template, '\\')) {
            return $this->templateDirectory . '/' . $template;
        }

        // Otherwise, assume it's in the root templates directory
        return $this->templateDirectory . '/' . $template . '.html';
    }

    /**
     * Compile a template with data
     *
     * @param string $template The template content
     * @param array $data The data for substitution
     * @return string The compiled template
     */
    private function compileTemplate(string $template, array $data): string
    {
        // Process partials first
        $template = $this->processPartials($template, $data);
        
        // Process loops
        $template = $this->processLoops($template, $data);
        
        // Process conditions
        $template = $this->processConditions($template, $data);
        
        // Process variable substitutions
        $template = $this->processVariables($template, $data);
        
        return $template;
    }

    /**
     * Process partial inclusions ({{>partial}})
     *
     * @param string $template The template content
     * @param array $data The data context
     * @return string The processed template
     */
    private function processPartials(string $template, array $data): string
    {
        return preg_replace_callback('/\{\{\s*>\s*([a-zA-Z0-9_-]+)\s*\}\}/', function ($matches) use ($data) {
            $partialName = $matches[1];
            
            // Check if it's a registered partial
            if (isset($this->partials[$partialName])) {
                $partialContent = $this->partials[$partialName];
            } else {
                // Try to load from components directory
                try {
                    $partialContent = $this->loadTemplate("components/{$partialName}");
                } catch (Exception $e) {
                    // If component doesn't exist, return empty string
                    error_log("Partial not found: {$partialName}");
                    return '';
                }
            }
            
            // Recursively compile the partial
            return $this->compileTemplate($partialContent, $data);
        }, $template);
    }

    /**
     * Process loops ({{#items}} {{/items}})
     *
     * @param string $template The template content
     * @param array $data The data context
     * @return string The processed template
     */
    private function processLoops(string $template, array $data): string
    {
        return preg_replace_callback('/\{\{\s*#\s*([a-zA-Z0-9_]+)\s*\}\}(.*?)\{\{\s*\/\s*\1\s*\}\}/s', 
            function ($matches) use ($data) {
                $key = $matches[1];
                $loopTemplate = $matches[2];
                
                if (!isset($data[$key]) || !is_array($data[$key])) {
                    return '';
                }
                
                $output = '';
                foreach ($data[$key] as $item) {
                    $itemData = is_array($item) ? array_merge($data, $item) : array_merge($data, ['item' => $item]);
                    $output .= $this->compileTemplate($loopTemplate, $itemData);
                }
                
                return $output;
            }, 
            $template
        );
    }

    /**
     * Process conditional sections
     *
     * @param string $template The template content
     * @param array $data The data context
     * @return string The processed template
     */
    private function processConditions(string $template, array $data): string
    {
        // Simple condition processing - show content if variable exists and is truthy
        return preg_replace_callback('/\{\{\s*#\s*([a-zA-Z0-9_]+)\s*\}\}(.*?)\{\{\s*\/\s*\1\s*\}\}/s',
            function ($matches) use ($data) {
                $key = $matches[1];
                $content = $matches[2];
                
                if (isset($data[$key]) && $data[$key]) {
                    return $this->compileTemplate($content, $data);
                }
                
                return '';
            },
            $template
        );
    }

    /**
     * Process variable substitutions ({{variable}})
     *
     * @param string $template The template content
     * @param array $data The data context
     * @return string The processed template
     */
    private function processVariables(string $template, array $data): string
    {
        return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', function ($matches) use ($data) {
            $key = $matches[1];
            
            if (isset($data[$key])) {
                return htmlspecialchars((string)$data[$key], ENT_QUOTES, 'UTF-8');
            }
            
            // Return empty string for missing variables
            return '';
        }, $template);
    }
}
