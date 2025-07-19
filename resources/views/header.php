<!DOCTYPE html>
<html lang="{{ lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ title }} - {{ app_name }}</title>
    <link rel="stylesheet" href="{{ asset_url }}/css/main.css">
</head>
<body>
    <header>
        <nav >
            <h1 class="logo">{{ app_name }}</h1>
            <div class="nav-links">
                <a href="/">{{ home_text }}</a>
                <a href="/about">{{ about_text }}</a>
                <div >
                    <?= $language_switcher ?>
                </div>
            </div>
        </nav>
    </header>
    <main>

