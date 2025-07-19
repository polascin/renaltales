<?php

declare(strict_types=1);

namespace RenalTales\Views;

/**
 * Login View Class
 * 
 * Refactored to use simple component function
 * 
 * @package RenalTales\Views
 * @version 2025.v3.1.dev
 * @deprecated Use component function: render_login_form
 */
class LoginView
{
    public function render(array $data = []): string
    {
        return render_login_form($data);
    }
}

/**
 * Render login form
 * 
 * Simple function-based login component
 * 
 * @param array $data Form data
 * @return string Rendered HTML
 */
function render_login_form(array $data = []): string
{
    $usernameLabel = htmlspecialchars($data['usernameLabel'] ?? 'Username');
    $passwordLabel = htmlspecialchars($data['passwordLabel'] ?? 'Password');
    $loginButton = htmlspecialchars($data['loginButton'] ?? 'Login');
    $actionUrl = htmlspecialchars($data['actionUrl'] ?? '/login');
    $title = htmlspecialchars($data['title'] ?? 'Login');
    
    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title} - RenalTales</title>
    <link rel="stylesheet" href="/assets/css/main.css?v=" . time() . ">">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-form-wrapper">
            <h1 class="login-title">{$title}</h1>
            <form action="{$actionUrl}" method="post" class="login-form">
                <div >
                    <label for="username" >{$usernameLabel}</label>
                    <input type="text" name="username" id="username" class="form-input" required>
                </div>
                
                <div >
                    <label for="password" >{$passwordLabel}</label>
                    <input type="password" name="password" id="password" class="form-input" required>
                </div>
                
                <button type="submit" >{$loginButton}</button>
            </form>
        </div>
    </div>
</body>
</html>
HTML;
}

