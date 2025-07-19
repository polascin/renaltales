<?php

declare(strict_types=1);

namespace RenalTales\Contracts;

/**
 * Template Renderer Interface
 *
 * Defines the contract for template rendering systems that can compile
 * HTML templates with variable substitution and partial inclusion
 *
 * @author Ľubomír Polaščín
 * @package RenalTales\Contracts
 * @version 2025.v3.1.dev
 */
interface TemplateRendererInterface
{
    /**
     * Render a template with the provided data
     *
     * @param string $template The template name or path
     * @param array $data The data to pass to the template
     * @return string The rendered HTML
     * @throws \Exception When template cannot be found or rendered
     */
    public function render(string $template, array $data = []): string;

    /**
     * Register a partial template for inclusion in other templates
     *
     * @param string $name The partial name
     * @param string $template The template path or content
     * @return void
     */
    public function registerPartial(string $name, string $template): void;

    /**
     * Set the template directory
     *
     * @param string $directory The directory containing templates
     * @return void
     */
    public function setTemplateDirectory(string $directory): void;

    /**
     * Check if a template exists
     *
     * @param string $template The template name
     * @return bool True if template exists
     */
    public function templateExists(string $template): bool;
}
