# Multilanguage Management System Setup

This document provides setup instructions and usage examples for the multilanguage management system.

## Features

- ✅ **Language configuration system** with easy addition of new languages
- ✅ **Database-driven translation management** with translations, languages, and translation_cache tables
- ✅ **Translation caching mechanism** for performance with both memory and database caching
- ✅ **Admin interface** for managing translations with full CRUD operations
- ✅ **Language switcher component** with dropdown and AJAX support
- ✅ **Fallback language mechanism** (English as default fallback)
- ✅ **Translation helper functions** for easy use in templates

## Installation

### 1. Database Setup

Run the migration script to create the necessary tables:

```sql
-- Execute the migration file
SOURCE database/migrations/005_create_translations_table.sql;
```

This will create:
- `translations` table for storing translations
- `languages` table for language management
- `translation_cache` table for caching

### 2. Include Helper Functions

Add this to your bootstrap file or autoloader:

```php
require_once 'src/Helpers/TranslationHelper.php';
```

### 3. Start Session

Make sure sessions are started before using the translation system:

```php
session_start();
```

## Usage Examples

### Basic Translation

```php
// In your templates
echo __('welcome'); // Outputs: Welcome! (or localized version)
echo __('app_title', 'app'); // Outputs: Kidney Stories (or localized version)

// With parameters
echo __('hello_user', 'common', ['name' => 'John']); // "Hello, :name" becomes "Hello, John"
```

### Language Switching

```php
// Get current language
$currentLang = current_language(); // Returns: 'en', 'sk', etc.

// Set language
set_language('sk'); // Switch to Slovak

// Get available languages
$languages = available_languages(); // Returns array of language data
```

### Using the Service Directly

```php
use RenalTales\Services\TranslationService;

$translationService = new TranslationService();

// Get translation
$text = $translationService->translate('welcome', 'common');

// Save translation
$translationService->saveTranslation('new_key', 'New text', 'group', 'en');

// Import translations
$translations = [
    'common' => [
        'hello' => 'Hello',
        'goodbye' => 'Goodbye'
    ]
];
$translationService->importTranslations($translations, 'en');
```

### Language Switcher Component

Include the language switcher in your templates:

```php
<?php include 'resources/views/components/language-switcher.php'; ?>
```

Or copy the component code directly into your template.

### Admin Interface

Access the admin interface through the TranslationController:

```php
use RenalTales\Controllers\Admin\TranslationController;

$controller = new TranslationController();

// Dashboard
$controller->index();

// View translations for a language
$controller->show(); // ?lang=en&group=common

// Export translations
$controller->export(); // ?language=en&format=json
```

## Configuration

### Adding New Languages

1. **Through Admin Interface:**
   ```php
   // POST to admin controller
   $controller->addLanguage();
   ```

2. **Directly in Database:**
   ```sql
   INSERT INTO languages (code, name, native_name, flag_icon, direction, is_active, sort_order) 
   VALUES ('ja', 'Japanese', '日本語', 'jp', 'ltr', TRUE, 11);
   ```

### Language Configuration

The system uses the existing `config/app.php` configuration:

```php
'language' => [
    'default' => 'sk',
    'fallback' => 'en',
    'supported' => [
        'sk', 'cs', 'de', 'en', 'es', 'fr', 'it', 'ru', 'pl', 'hu'
    ]
]
```

## Performance Optimization

### Cache Management

```php
// Warm up cache
$translationService->warmUpCache();

// Clear cache
$translationService->clearCache(); // Current language
$translationService->clearAllCache(); // All languages

// Clean expired cache
$translationService->cleanExpiredCache();
```

### Cache Statistics

```php
$stats = $translationService->getCacheStatistics();
// Returns cache usage by language
```

## File Structure

```
src/
├── Models/
│   ├── Translation.php       # Translation model
│   └── Language.php          # Language model
├── Services/
│   ├── TranslationService.php # Main translation service
│   └── TranslationCache.php   # Cache management
├── Controllers/
│   └── Admin/
│       └── TranslationController.php # Admin interface
└── Helpers/
    └── TranslationHelper.php  # Helper functions

resources/
└── views/
    └── components/
        └── language-switcher.php # Language switcher UI

database/
└── migrations/
    └── 005_create_translations_table.sql # Database schema
```

## API Endpoints

### Language Switching
- `POST ?action=change_language` - Change current language
- `GET ?lang=CODE` - URL parameter method

### Admin API
- `POST /admin/translations/save` - Save translation
- `POST /admin/translations/delete` - Delete translation
- `POST /admin/translations/import` - Import translations
- `GET /admin/translations/export` - Export translations
- `POST /admin/translations/cache/clear` - Clear cache
- `POST /admin/translations/cache/warmup` - Warm up cache

## Translation Groups

Translations are organized into groups:
- `app` - Application-specific translations
- `common` - Common UI elements
- `navigation` - Navigation items
- `auth` - Authentication-related
- `default` - Default group

## Helper Functions Reference

### Translation Functions
- `__(string $key, string $group = 'default', array $parameters = [])` - Get translation
- `trans(string $key, string $group = 'default', array $parameters = [])` - Alias for __()

### Language Functions
- `current_language()` - Get current language code
- `available_languages()` - Get available languages
- `set_language(string $code)` - Set current language
- `is_rtl()` - Check if current language is RTL
- `language_direction()` - Get language direction
- `language_name()` - Get current language name
- `language_native_name()` - Get native name
- `language_flag()` - Get flag icon code

## RTL Support

The system supports RTL languages:

```php
// Check if current language is RTL
if (is_rtl()) {
    echo '<html dir="rtl">';
} else {
    echo '<html dir="ltr">';
}

// Get direction
$direction = language_direction(); // 'ltr' or 'rtl'
```

## Import/Export Formats

### Supported Formats
- **JSON** - Standard format
- **PHP** - PHP array format
- **CSV** - Comma-separated values

### JSON Format
```json
{
  "common": {
    "hello": "Hello",
    "goodbye": "Goodbye"
  },
  "navigation": {
    "home": "Home",
    "about": "About"
  }
}
```

### PHP Format
```php
<?php
return [
    'common' => [
        'hello' => 'Hello',
        'goodbye' => 'Goodbye'
    ],
    'navigation' => [
        'home' => 'Home',
        'about' => 'About'
    ]
];
```

### CSV Format
```csv
Group,Key,Translation
common,hello,Hello
common,goodbye,Goodbye
navigation,home,Home
navigation,about,About
```

## Error Handling

The system includes comprehensive error handling:

- **Missing translations** - Falls back to English, then returns the key
- **Invalid language codes** - Returns false, maintains current language
- **Cache failures** - Gracefully falls back to database
- **Database errors** - Proper error responses in admin interface

## Security

- All user inputs are sanitized
- SQL injection protection through prepared statements
- XSS protection in templates
- CSRF protection recommended for admin interface

## Testing

The system has been tested with:
- Multiple language switches
- Cache performance
- Fallback mechanisms
- Import/export functionality
- Admin interface operations

## Troubleshooting

### Common Issues

1. **Translations not loading**
   - Check database connection
   - Verify session is started
   - Check language code in database

2. **Cache not working**
   - Verify database tables exist
   - Check cache expiration settings
   - Clear expired cache

3. **Language switcher not working**
   - Check JavaScript console for errors
   - Verify AJAX endpoint is accessible
   - Check session handling

### Debug Mode

Enable debug mode to see detailed error information:

```php
// In your configuration
'app' => [
    'debug' => true
]
```

This completes the comprehensive multilanguage management system for your RenalTales application!
