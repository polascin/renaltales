# Multilingual Support Guide

## Overview

RenalTales now includes comprehensive multilingual support with translation files, helper functions, and session/cookie-based locale persistence.

## Features Implemented

### 1. Translation Files
- **English**: `i18n/en.php` (fallback language)
- **Slovak**: `i18n/sk.php` (default language)
- **Spanish**: `i18n/es.php` (newly added)

### 2. Helper Functions

#### `__($key, $parameters = [])`
The main translation helper function that retrieves translated strings.

```php
// Basic usage
echo __('nav.home'); // Returns "Home", "Domov", or "Inicio" based on current language

// With parameters
echo __('msg.welcome', ['name' => 'John']); // Returns "Welcome, John!" with :name replaced
```

#### `lang($key, $parameters = [])`
Alias for the `__()` function for convenience.

```php
echo lang('auth.login.title'); // Same as __('auth.login.title')
```

### 3. Language Management

#### Configuration
Languages are configured in `config/config.php`:

```php
'languages' => [
    'default' => 'sk',
    'fallback' => 'en',
    'detect_from_browser' => true,
    'supported' => ['en', 'sk', 'es'],
],
```

#### Language Detection Priority
1. Session (`$_SESSION['language']`)
2. Cookie (`$_COOKIE['language']`)
3. Browser language detection (if enabled)
4. Default language from config

### 4. Language Switching

#### URL Route
Users can switch languages via: `/lang/{code}`

Examples:
- `/lang/en` - Switch to English
- `/lang/sk` - Switch to Slovak  
- `/lang/es` - Switch to Spanish

#### Persistence
- **Session**: Stored in `$_SESSION['language']`
- **Cookie**: Stored for 30 days in `language` cookie
- **Database**: Can be stored per-user (implementation ready)

### 5. Using Translations in Views

#### Traditional Array Access (backward compatible)
```php
<h1><?= $t['auth.login.title'] ?? 'Login' ?></h1>
```

#### New Helper Function (recommended)
```php
<h1><?= __('auth.login.title') ?></h1>
<p><?= __('welcome.message', ['username' => $user['name']]) ?></p>
```

### 6. Using Translations in Controllers

```php
// In controller methods
$this->flashError(__('auth.login.invalid_credentials'));
$this->flashSuccess(__('msg.success.saved'));

// Validation messages
$messages = [
    'email.required' => __('form.email.required'),
    'email.email' => __('form.email.invalid'),
];
```

## Available Translation Keys

### Navigation
- `nav.home`, `nav.stories`, `nav.about`, `nav.contact`
- `nav.login`, `nav.register`, `nav.logout`, `nav.profile`

### Authentication
- `auth.login.title`, `auth.login.email`, `auth.login.password`
- `auth.login.invalid_credentials`, `auth.login.email_not_verified`
- `auth.register.title`, `auth.register.name`, `auth.register.email`

### Forms
- `form.required`, `form.email.required`, `form.email.invalid`
- `form.password.required`, `form.password.min`, `form.password.mismatch`

### Messages
- `msg.success.saved`, `msg.success.deleted`
- `msg.error.generic`, `msg.error.unauthorized`, `msg.error.not_found`

### Buttons
- `btn.save`, `btn.cancel`, `btn.delete`, `btn.edit`, `btn.view`, `btn.submit`

### Home Page
- `home.hero.title`, `home.hero.subtitle`
- `home.stats.stories_shared`, `home.stats.community_members`
- `home.featured_stories.title`, `home.recent_stories.title`

## Adding New Languages

### 1. Create Translation File
Create a new file in `i18n/{language_code}.php`:

```php
<?php
return [
    'nav.home' => 'Translation in new language',
    // ... all translation keys
];
```

### 2. Update Configuration
Add the language code to the supported languages array in `config/config.php`:

```php
'languages' => [
    'supported' => ['en', 'sk', 'es', 'fr'], // Add 'fr' for French
],
```

### 3. Add Language Name
Update the `getLanguageName()` method in `LanguageManager.php` if needed.

## Adding New Translation Keys

### 1. Add to All Language Files
```php
// In i18n/en.php
'new.key' => 'English translation',

// In i18n/sk.php
'new.key' => 'Slovak translation',

// In i18n/es.php
'new.key' => 'Spanish translation',
```

### 2. Use in Views/Controllers
```php
echo __('new.key');
```

## Language Switching in Templates

The layout includes a language dropdown that automatically shows all supported languages:

```php
<?php foreach ($supportedLanguages as $code => $name): ?>
    <a class="dropdown-item" href="/lang/<?= urlencode($code) ?>">
        <img src="<?= Router::asset("images/flags/{$code}.png") ?>" class="flag">
        <?= htmlspecialchars($name) ?>
    </a>
<?php endforeach; ?>
```

## Best Practices

### 1. Consistent Key Naming
Use dot notation with logical grouping:
- `section.subsection.item`
- `auth.login.title`
- `form.validation.email`

### 2. Parameter Replacement
Use `:parameter` syntax in translations:
```php
// Translation: "Welcome back, :username!"
echo __('welcome.message', ['username' => $user['name']]);
```

### 3. Fallback Handling
Always provide fallback text for missing translations:
```php
echo __('some.key') ?: 'Default English text';
```

### 4. Pluralization
For complex pluralization, consider using parameters:
```php
// Translation: "You have :count :items"
echo __('items.count', [
    'count' => $count,
    'items' => $count === 1 ? 'item' : 'items'
]);
```

## Implementation Examples

### Language Switching Component
```php
<!-- Language selector dropdown -->
<div class="dropdown">
    <button class="btn btn-secondary dropdown-toggle">
        <?= strtoupper($lang) ?>
    </button>
    <ul class="dropdown-menu">
        <?php foreach ($supportedLanguages as $code => $name): ?>
            <li>
                <a class="dropdown-item" href="/lang/<?= $code ?>">
                    <?= $name ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
```

### Form with Translations
```php
<form method="POST">
    <div class="form-group">
        <label><?= __('form.email.label') ?></label>
        <input type="email" name="email" required>
        <small class="text-danger"><?= __('form.email.required') ?></small>
    </div>
    <button type="submit"><?= __('btn.submit') ?></button>
</form>
```

This multilingual system provides a solid foundation for internationalization while maintaining backward compatibility with existing code.
