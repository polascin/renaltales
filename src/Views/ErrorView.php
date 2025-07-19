<?php

declare(strict_types=1);

namespace RenalTales\Views;

use RenalTales\Components\error_component;
use Throwable;

/**
 * Error View Class
 * 
 * Refactored to use component-based function
 * 
 * @package RenalTales\Views
 * @version 2025.v3.1.dev
 * @deprecated Use component function: render_error_page
 */
class ErrorView
{
    private Throwable $exception;

    public function __construct(Throwable $exception)
    {
        $this->exception = $exception;
    }

    public function render(array $data = []): string
    {
        return render_error_page($this->exception, ['debug' => $data['debug'] ?? false]);
    }
}
