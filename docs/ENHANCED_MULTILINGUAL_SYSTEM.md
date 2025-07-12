# Enhanced Multilingual System Documentation

## Overview

The enhanced multilingual system provides a comprehensive, modern architecture for managing multiple languages in the application. It replaces the previous `LanguageDetector` and `LanguageModel` classes with a more robust, feature-rich system.

## Architecture

### Core Components

1. **MultilingualServiceProvider** - Central service provider (Singleton pattern)
2. **LanguageManager** - Enhanced language detection and management
3. **TranslationManager** - Advanced translation management with caching
4. **LanguageInterface** - Contract for language management implementations
5. **TranslationInterface** - Contract for translation management implementations

### Key Features

- ✅ **Enhanced Language Detection** - Multiple detection methods with priority
- ✅ **Advanced Caching** - Improved performance with intelligent caching
- ✅ **Security Improvements** - Input validation and secure cookie handling
- ✅ **Pluralization Support** - Proper plural form handling
- ✅ **Context-Aware Translations** - Context-specific translation keys
- ✅ **Parameter Interpolation** - Dynamic parameter replacement
- ✅ **RTL Language Support** - Full right-to-left language support
- ✅ **Health Monitoring** - System health checks and statistics
- ✅ **Legacy Compatibility** - Backward compatibility with existing code

## Quick Start

### 1. Basic Usage

```php
// Initialize the multilingual system
$config = require 'config/multilingual.php';
$multilingual = MultilingualServiceProvider::getInstance($config);
$multilingual->initialize();

// Basic translation
echo $multilingual->getText('common.welcome', 'Welcome');

// Translation with parameters
echo $multilingual->getText('common.hello_user', 'Hello {name}!', ['name' => 'John']);

// Pluralization
echo $multilingual->getPlural('common.item', 5, ['count' => 5], '{count} items');
```

### 2. Language Management

```php
// Get current language
$currentLang = $multilingual->getCurrentLanguage();

// Set language
$multilingual->setLanguage('sk');

// Check if language is RTL
if ($multilingual->isRTL('ar')) {
    echo 'This is a right-to-left language';
}

// Get language information
$info = $multilingual->getLanguageInfo('en');
print_r($info);
```

### 3. Language Selector

```php
// Render language selector with flags
echo $multilingual->renderLanguageSelector([
    'show_flags' => true,
    'show_native_names' => true,
    'css_class' => 'language-selector',
    'current_first' => true
]);
```

## Configuration

The system is configured through `config/multilingual.php`:

```php
return [
    'fallback_language' => 'en',
    'auto_detect' => true,
    'cache_enabled' => true,
    'security_enabled' => true,
    
    // Detection methods
    'detection_methods' => [
        'url_parameter' => true,
        'session' => true,
        'cookie' => true,
        'browser' => true,
        'geolocation' => true,
    ],
    
    // UI settings
    'show_flags' => true,
    'flag_base_path' => 'assets/flags/',
    
    // Performance
    'cache_lifetime' => 1800,
    'lazy_loading' => true,
    
    // ... more options
];
```

## Language Detection

### Detection Priority

1. **URL Parameter** - `?lang=en`
2. **Session Storage** - User session preference
3. **Cookie** - Persistent user preference
4. **Browser** - Accept-Language header
5. **Geolocation** - IP-based country detection
6. **Fallback** - Default language

### Detection Methods

```php
// Manual detection
$detected = $multilingual->getCurrentLanguage();

// Force detection refresh
$multilingual->refresh();
```

## Translation System

### Basic Translations

```php
// Simple translation
$text = $multilingual->getText('common.save', 'Save');

// With fallback
$text = $multilingual->getText('missing.key', 'Default Text');
```

### Parameter Interpolation

```php
// Parameters in curly braces
$text = $multilingual->getText('welcome.message', 'Hello {name}, you have {count} messages', [
    'name' => 'John',
    'count' => 5
]);
```

### Pluralization

```php
// Automatic plural handling
$text = $multilingual->getPlural('item.count', $count, [
    'count' => $count
]);

// Plural forms: item.count_zero, item.count_one, item.count_other
```

### Context-Aware Translations

```php
// Context-specific translations
$text = $multilingual->getTextWithContext('save', 'form', 'Save Form');
$text = $multilingual->getTextWithContext('save', 'document', 'Save Document');
```

## Language Files

### File Structure

```txt
resources/lang/
├── en.php          # English translations
├── sk.php          # Slovak translations
├── cs.php          # Czech translations
└── contexts/       # Context-specific translations
    ├── en/
    │   ├── form.php
    │   └── admin.php
    └── sk/
        ├── form.php
        └── admin.php
```

### Translation File Format

```php
// resources/lang/en.php
return [
    'common' => [
        'welcome' => 'Welcome',
        'hello_user' => 'Hello {name}!',
        'item_zero' => 'No items',
        'item_one' => '1 item',
        'item_other' => '{count} items'
    ],
    'forms' => [
        'save' => 'Save',
        'cancel' => 'Cancel',
        'submit' => 'Submit'
    ]
];
```

## RTL Language Support

### Automatic RTL Detection

```php
// Check if language is RTL
if ($multilingual->isRTL()) {
    echo '<html dir="rtl">';
} else {
    echo '<html dir="ltr">';
}

// Get direction string
$direction = $multilingual->getLanguageDirection(); // 'ltr' or 'rtl'
```

### RTL Languages

Supported RTL languages: Arabic (ar), Persian (fa), Hebrew (he), Urdu (ur), Pashto (ps), Sindhi (sd), Uyghur (ug), Divehi (dv)

## Performance & Caching

### Caching System

- **Translation Caching** - Translations are cached for improved performance
- **Language Detection Caching** - Detection results cached per session
- **Memory Management** - Automatic memory usage monitoring

### Performance Monitoring

```php
// Get system statistics
$stats = $multilingual->getSystemStats();
print_r($stats);

// Memory usage
$memory = $stats['total_memory'];
echo "Memory usage: " . round($memory / 1024 / 1024, 2) . " MB";
```

## Health Monitoring

### Health Checks

```php
// Perform health check
$health = $multilingual->healthCheck();

if ($health['status'] === 'healthy') {
    echo "System is healthy";
} else {
    echo "Issues found:";
    foreach ($health['issues'] as $issue) {
        echo "- $issue\n";
    }
}
```

### System Statistics

```php
$stats = $multilingual->getSystemStats();
// Returns: current_language, supported_languages, memory_usage, etc.
```

## Legacy Compatibility

### Automatic Migration

The system provides backward compatibility with existing code:

```php
// Old code still works
$detector = new LanguageDetector();
$model = new LanguageModel();

// Global functions available
echo t('common.welcome');
echo tn('item.count', 5);
echo getCurrentLang();
```

### Migration Helper

```bash
# Run migration helper
php multilingual_migration.php
```

This will:

- Test the new system
- Check compatibility with existing code
- Generate usage examples
- Perform health checks
- Create migration log

## API Reference

### MultilingualServiceProvider

#### Core Methods

- `getInstance(array $config = [])` - Get singleton instance
- `initialize(string $defaultLanguage = null)` - Initialize system
- `setLanguage(string $language)` - Set current language
- `getCurrentLanguage()` - Get current language

#### Translation Methods

- `getText(string $key, string $fallback = '', array $parameters = [])` - Get translation
- `getPlural(string $key, int $count, array $parameters = [], string $fallback = '')` - Get plural translation
- `getTextWithContext(string $key, string $context, string $fallback = '', array $parameters = [])` - Get contextual translation

#### Language Information

- `getAvailableLanguages()` - Get all available languages
- `getLanguageInfo(string $language)` - Get language information
- `isRTL(string $language = null)` - Check if RTL
- `getLanguageDirection(string $language = null)` - Get direction (ltr/rtl)
- `getLanguageFlag(string $language)` - Get flag URL
- `getLanguageNativeName(string $language)` - Get native name

#### UI Methods

- `renderLanguageSelector(array $options = [])` - Render language selector HTML

#### System Methods

- `getSystemStats()` - Get system statistics
- `healthCheck()` - Perform health check
- `clearCache()` - Clear all caches
- `refresh()` - Refresh system

## Best Practices

### 1. Translation Keys

Use hierarchical keys with dots:

```php
// Good
'user.profile.save'
'forms.validation.required'
'dashboard.widgets.weather'

// Avoid
'userprofilesave'
'save_button'
```

### 2. Parameter Naming

Use clear, descriptive parameter names:

```php
// Good
'Hello {username}, you have {messageCount} new messages'

// Avoid
'Hello {a}, you have {b} new {c}'
```

### 3. Fallback Text

Always provide meaningful fallback text:

```php
// Good
$multilingual->getText('user.welcome', 'Welcome to our application')

// Avoid
$multilingual->getText('user.welcome', '')
```

### 4. Context Usage

Use contexts for similar translations with different meanings:

```php
// Different contexts for "Save"
$multilingual->getTextWithContext('save', 'document', 'Save Document')
$multilingual->getTextWithContext('save', 'settings', 'Save Settings')
```

### 5. Performance

- Initialize the service once and reuse
- Use caching for frequently accessed translations
- Monitor memory usage with system stats

## Troubleshooting

### Common Issues

1. **Translation not found**
   - Check if key exists in language file
   - Verify fallback language file
   - Check file permissions

2. **Language not switching**
   - Verify language is supported
   - Check session/cookie configuration
   - Verify file exists

3. **Performance issues**
   - Enable caching
   - Check memory usage
   - Use lazy loading

4. **RTL not working**
   - Verify language is in RTL list
   - Check CSS direction rules
   - Update HTML lang attribute

### Debug Mode

Enable debug mode in configuration:

```php
'debug_mode' => true,
'log_missing_translations' => true,
'show_missing_key_warnings' => true,
```

## Migration Guide

### From Old System

1. **Update initialization:**

```php
// Old
$detector = new LanguageDetector();
$model = new LanguageModel();

// New
$multilingual = MultilingualServiceProvider::getInstance();
$multilingual->initialize();
```

1. **Update translation calls:**

```php
// Old
$detector->getText('welcome')
$model->getText('hello', ['name' => $user])

// New
$multilingual->getText('common.welcome', 'Welcome')
$multilingual->getText('common.hello', 'Hello {name}!', ['name' => $user])
```

1. **Update language detection:**

```php
// Old
$detector->detectLanguage()
$detector->setLanguage($lang)

// New
$multilingual->getCurrentLanguage()
$multilingual->setLanguage($lang)
```

### Testing Migration

Use the migration helper to test your migration:

```bash
php multilingual_migration.php
```

## Support

For issues and questions:

1. Check the health status: `$multilingual->healthCheck()`
2. Review system statistics: `$multilingual->getSystemStats()`
3. Check migration log: `multilingual_migration_log.txt`
4. Enable debug mode for detailed logging

---

**Version:** 2025.v1.0test  
**Author:** Ľubomír Polaščín  
**Last Updated:** December 2024
