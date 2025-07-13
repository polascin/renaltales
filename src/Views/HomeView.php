<?php
// src/Views/HomeView.php

declare(strict_types=1);

namespace RenalTales\Views;

class HomeView {
    private string $language;
    private string $appName;

    public function __construct(string $language, string $appName = 'RenalTales') {
        $this->language = $language;
        $this->appName = $appName;
    }

    public function render(): string {
        // Prepare available languages (could be dynamic in a real app)
        $availableLanguages = [
            'en' => 'English',
            'sk' => 'Slovak',
            'la' => 'Latin',
        ];
        // Render the language switcher component
        ob_start();
        $currentLanguage = $this->language;
        $availableLanguages = $availableLanguages;
        include __DIR__ . '/../../resources/views/components/language-switcher.php';
        $languageSwitcher = ob_get_clean();

        return <<<HTML
<!DOCTYPE html>
<html lang="{$this->language}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$this->appName} - Home</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <header>
        <h1>Welcome to {$this->appName}!</h1>
    </header>
    <main>
        {$languageSwitcher}
        <p>Your current language is: <strong>{$this->language}</strong></p>
        <p>This is the homepage of RenalTales, a modern PHP application for renal health management.</p>
    </main>
    <footer>
        <small>&copy; 2025 RenalTales</small>
    </footer>
</body>
</html>
HTML;
    }
}
