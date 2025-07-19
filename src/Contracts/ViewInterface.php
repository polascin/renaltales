<?php

declare(strict_types=1);

namespace RenalTales\Contracts;

/**
 * View Interface
 *
 * Defines the contract for all view components.
 * Views should be responsible for rendering content.
 *
 * @package RenalTales\Contracts
 * @version 2025.v3.1.dev
 * @author Ľubomír Polaščín
 */
interface ViewInterface
{
    /**
     * Render the view content
     *
     * @param array<string, mixed> $data Data to be passed to the view
     * @return string The rendered view content
     */
    public function render(array $data = []): string;

    /**
     * Set view data
     *
     * @param array<string, mixed> $data Data to be set
     * @return self
     */
    public function with(array $data): self;

    /**
     * Get view name/identifier
     *
     * @return string The view name
     */
    public function getName(): string;

    /**
     * Check if view exists
     *
     * @return bool True if view exists, false otherwise
     */
    public function exists(): bool;
}
