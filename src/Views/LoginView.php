<?php

declare(strict_types=1);

namespace RenalTales\Views;

use RenalTales\Contracts\ViewInterface;

/**
 * Login View Class
 *
 * Handles the display and rendering of the login page for the application.
 *
 * @package RenalTales
 * @version 2025.3.1.dev
 */
class LoginView implements ViewInterface
{
    /**
     * Render the view content
     *
     * @param array<string, mixed> $data Data to be passed to the view
     * @return string The rendered view content
     */
    public function render(array $data = []): string
    {
        // Create a basic login form template
        $usernameLabel = $data['usernameLabel'] ?? 'Username';
        $passwordLabel = $data['passwordLabel'] ?? 'Password';
        $loginButton = $data['loginButton'] ?? 'Login';
        $actionUrl = $data['actionUrl'] ?? '/login';

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <form action="{$actionUrl}" method="post">
        <label for="username">{$usernameLabel}</label>
        <input type="text" name="username" id="username" required>

        <label for="password">{$passwordLabel}</label>
        <input type="password" name="password" id="password" required>

        <button type="submit">{$loginButton}</button>
    </form>
</body>
</html>
HTML;
    }

    /**
     * Set view data
     *
     * @param array<string, mixed> $data Data to be set
     * @return ViewInterface
     */
    public function with(array $data): ViewInterface
    {
        // For simplicity, we won't store additional data
        return $this;
    }

    /**
     * Get view name/identifier
     *
     * @return string The view name
     */
    public function getName(): string
    {
        return 'login';
    }

    /**
     * Check if view exists
     *
     * @return bool True if view exists, false otherwise
     */
    public function exists(): bool
    {
        return true;
    }
}
