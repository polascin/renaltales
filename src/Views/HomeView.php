<?php

declare(strict_types=1);

namespace RenalTales\Views;

use RenalTales\Components\home_component;

/**
 * Home View Class
 *
 * Refactored to use component-based function
 *
 * @package RenalTales\Views
 * @version 2025.v3.1.dev
 * @deprecated Use component function: render_home_page
 */
class HomeView
{
    public function render(array $data = []): string
    {
        return render_home_page(['translation' => $data['translation'] ?? null]);
    }
}
