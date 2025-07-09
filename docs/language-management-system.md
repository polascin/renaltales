# Language Management System Documentation

## Overview

The Language Management System is a comprehensive internationalization (i18n) solution for the Renal Tales application. It provides automatic language detection, translation management, and multi-language support for over 150 languages with intelligent fallback mechanisms and advanced security features.

## Features

### Core Features
- **Automatic Language Detection**: Multi-method detection from URL, session, cookies, browser headers, and geolocation
- **Translation Management**: File-based translation system with fallback support
- **Multi-Language Support**: Support for 150+ languages with proper character encoding
- **RTL Language Support**: Right-to-left language support for Arabic, Hebrew, Persian, etc.
- **Flag Management**: Automatic flag detection and caching for language switcher UI
- **Security Features**: Input validation, sanitization, and secure cookie handling

### Advanced Features
- **Intelligent Fallback**: English fallback for missing translations
- **Performance Optimization**: Caching system for flag paths and file existence checks
- **Geographic Detection**: CloudFlare and IP-based country detection
- **Session Management**: Secure session-based language persistence
- **Cookie Security**: Secure, HttpOnly, SameSite cookie implementation
- **Debug Mode**: Comprehensive debugging information for development

## System Architecture

### File Structure
```
models/
└── LanguageModel.php        # Main language model with translation loading

core/
└── LanguageDetector.php     # Language detection and management engine

resources/
└── lang/                    # Language files directory
    ├── en.php              # English (base language)
    ├── sk.php              # Slovak
    ├── cs.php              # Czech
    ├── de.php              # German
    ├── [150+ language files]
    └── ...
```

### Class Hierarchy
```
LanguageModel
├── Translation loading and management
├── Text retrieval with fallback
└── Integration with LanguageDetector

LanguageDetector
├── Language detection logic
├── Flag management
├── Security features
└── Performance optimization
```

## Quick Start Guide

### 1. Basic Usage

```php
// Initialize language system
$languageModel = new LanguageModel();

// Get current language
$currentLang = $languageModel->getCurrentLanguage();

// Get translated text
$welcomeText = $languageModel->getText('welcome', 'Welcome');

// Get all available languages
$languages = $languageModel->getSupportedLanguages();
```

### 2. Language Detection

```php
// Initialize language detector
$detector = new LanguageDetector();

// Detect user's preferred language
$detectedLang = $detector->detectLanguage();

// Check if language is supported
$isSupported = $detector->isSupported('sk');

// Set user's language preference
$detector->setLanguage('sk');
```

### 3. Adding New Languages

1. Create a new language file in `resources/lang/`
2. Copy the structure from `en.php`
3. Translate all strings
4. The system will automatically detect and load the new language

Example language file (`resources/lang/fr.php`):
```php
<?php
return [
    'app_title' => 'Histoires Rénales',
    'welcome' => 'Bienvenue!',
    'current_language' => 'Langue actuelle',
    // ... more translations
];
```

## Language Detection Methods

The system uses multiple detection methods in order of priority:

### 1. URL Parameter (Highest Priority)
```
https://example.com/page?lang=sk
```

### 2. Session Storage
```php
$_SESSION['language'] = 'sk';
```

### 3. Cookie Storage
```php
$_COOKIE['language'] = 'sk';
```

### 4. Browser Accept-Language Header
```
Accept-Language: sk,en-US;q=0.9,en;q=0.8
```

### 5. Geolocation (CloudFlare/IP)
```php
$_SERVER['HTTP_CF_IPCOUNTRY'] = 'SK';
```

### 6. Default Fallback
```php
// Falls back to English if no preference detected
return 'en';
```

## Supported Languages

The system supports over 150 languages organized by regions:

### European Languages
- **Western Europe**: English, German, French, Spanish, Portuguese, Italian, Dutch
- **Eastern Europe**: Slovak, Czech, Polish, Hungarian, Ukrainian, Russian
- **Nordic**: Swedish, Norwegian, Danish, Finnish, Icelandic
- **Baltic**: Estonian, Latvian, Lithuanian
- **Balkan**: Serbian, Croatian, Bosnian, Albanian, Macedonian, Bulgarian

### Asian Languages
- **East Asian**: Chinese (Simplified/Traditional), Japanese, Korean
- **South Asian**: Hindi, Bengali, Tamil, Telugu, Gujarati, Marathi, Punjabi
- **Southeast Asian**: Thai, Vietnamese, Indonesian, Malay, Filipino
- **Central Asian**: Kazakh, Uzbek, Kyrgyz, Turkmen, Tajik
- **Middle Eastern**: Arabic, Persian, Hebrew, Turkish, Kurdish

### African Languages
- **Major Languages**: Swahili, Amharic, Hausa, Yoruba, Zulu, Afrikaans
- **Regional Languages**: Shona, Luganda, Kinyarwanda, Somali, Oromo

### Other Languages
- **Constructed**: Esperanto
- **American Indigenous**: Quechua, Guaraní, Aymara
- **Creole**: Haitian Creole

## API Reference

### LanguageModel Class

#### Constructor
```php
public function __construct()
```
Initializes the language model with automatic language detection.

#### Methods

**getText($key, $fallback = '')**
```php
public function getText($key, $fallback = '')
```
Retrieves translated text with fallback support.

**getCurrentLanguage()**
```php
public function getCurrentLanguage()
```
Returns the current language code.

**getCurrentLanguageName()**
```php
public function getCurrentLanguageName()
```
Returns the current language name in its native script.

**getSupportedLanguages()**
```php
public function getSupportedLanguages()
```
Returns array of supported language codes.

### LanguageDetector Class

#### Constructor
```php
public function __construct()
```
Initializes the detector and loads available languages.

#### Methods

**detectLanguage()**
```php
public function detectLanguage()
```
Detects user's preferred language using multiple methods.

**isSupported($lang)**
```php
public function isSupported($lang)
```
Checks if a language is supported.

**setLanguage($lang)**
```php
public function setLanguage($lang)
```
Sets user's language preference with security validation.

**getLanguageName($lang)**
```php
public function getLanguageName($lang)
```
Returns the display name of a language.

**isRTL($lang)**
```php
public function isRTL($lang)
```
Checks if a language uses right-to-left writing.

**getFlagPath($lang)**
```php
public function getFlagPath($lang, $basePath = 'assets/flags/', $extension = '.webp')
```
Returns the flag file path for a language.

**getBestFlagPath($lang)**
```php
public function getBestFlagPath($lang, $basePath = 'assets/flags/', $documentRoot = null)
```
Returns the best available flag format with fallback.

## Configuration

### Language Constants
Define in your bootstrap file:
```php
define('LANGUAGE_PATH', 'resources/lang/');
```

### Security Configuration
```php
// Cookie security settings
$cookieOptions = [
    'expires' => time() + (86400 * 30), // 30 days
    'path' => '/',
    'domain' => '',
    'secure' => true,      // HTTPS only
    'httponly' => true,    // No JavaScript access
    'samesite' => 'Lax'    // CSRF protection
];
```

### Performance Settings
```php
// Cache settings (built-in)
private static $fileExistenceCache = [];
private static $flagPathCache = [];
```

## Security Features

### Input Validation
- Language code format validation (ISO 639-1/639-2)
- Length limits and character restrictions
- XSS prevention through proper sanitization

### Secure Cookie Handling
- HttpOnly cookies to prevent XSS
- Secure flag for HTTPS connections
- SameSite attribute for CSRF protection
- Proper expiration handling

### Session Security
- Session availability checks
- Safe session variable handling
- Error handling for session operations

### Debug Mode Protection
- Debug information only on localhost
- IP-based access control
- Filtered sensitive information

## Translation Management

### File Structure
Each language file follows this structure:
```php
<?php
return [
    // Application general
    'app_title' => 'Application Title',
    'welcome' => 'Welcome',
    
    // Error messages
    'error' => 'Error',
    'access_denied' => 'Access Denied',
    
    // Feature-specific translations
    'login' => 'Login',
    'logout' => 'Logout',
    
    // ... more translations
];
```

### Translation Keys
Use hierarchical keys for organization:
```php
// Good
'user_profile_name' => 'Name',
'user_profile_email' => 'Email',
'user_settings_language' => 'Language',

// Or use arrays for grouping
'user' => [
    'profile' => [
        'name' => 'Name',
        'email' => 'Email'
    ],
    'settings' => [
        'language' => 'Language'
    ]
]
```

### Fallback System
1. **Primary**: Requested language file
2. **Secondary**: English translation
3. **Tertiary**: Hard-coded fallback string
4. **Final**: Empty string or key name

## Flag Management

### Flag File Organization
```
public/assets/flags/
├── en.webp        # English flag
├── sk.webp        # Slovak flag
├── de.webp        # German flag
├── [country].webp
└── un.webp        # United Nations flag (fallback)
```

### Supported Formats
The system checks for flags in order of preference:
1. `.webp` (preferred for smaller size)
2. `.png` (good quality)
3. `.jpg` (fallback)
4. `.gif` (animated flags)

### Flag Caching
- File existence caching to reduce I/O operations
- Flag path caching for performance
- Automatic cache invalidation

## Right-to-Left (RTL) Support

### RTL Languages
The system automatically detects RTL languages:
- Arabic (ar)
- Persian/Farsi (fa)
- Hebrew (he)
- Urdu (ur)
- Dhivehi (dv)
- Sindhi (sd)

### CSS Integration
```php
$direction = $languageDetector->getDirection($currentLang);
echo '<html dir="' . $direction . '">';
```

```css
/* RTL-specific styles */
[dir="rtl"] {
    text-align: right;
}

[dir="rtl"] .menu {
    float: right;
}
```

## Performance Optimization

### Caching Strategies
1. **File Existence Cache**: Reduces filesystem calls
2. **Flag Path Cache**: Speeds up flag URL generation
3. **Language File Cache**: Consider implementing for production

### Memory Management
- Efficient array operations
- Minimal object instantiation
- Garbage collection friendly

### Database Optimization
Consider migrating to database-based translations for:
- Dynamic translation updates
- Translation versioning
- User-contributed translations
- Better performance with large translation sets

## Error Handling

### Common Error Scenarios
1. **Missing Language Files**: Falls back to English
2. **Invalid Language Codes**: Sanitized and validated
3. **Corrupted Language Files**: Graceful degradation
4. **Session Failures**: Cookie-based fallback

### Error Logging
```php
error_log('LanguageDetector: Language detection failed: ' . $e->getMessage());
```

### Debug Information
```php
$debugInfo = $languageDetector->getDebugInfo();
// Returns comprehensive debugging information
```

## Testing

### Unit Tests
```php
// Test language detection
public function testLanguageDetection() {
    $detector = new LanguageDetector();
    
    // Test URL parameter
    $_GET['lang'] = 'sk';
    $this->assertEquals('sk', $detector->detectLanguage());
    
    // Test fallback
    unset($_GET['lang']);
    $this->assertEquals('en', $detector->detectLanguage());
}

// Test translation loading
public function testTranslationLoading() {
    $model = new LanguageModel();
    $this->assertNotEmpty($model->getText('welcome'));
}
```

### Integration Tests
- Browser header parsing
- Cookie handling
- Session management
- Flag file detection

## Deployment

### Production Checklist
- [ ] Verify all language files are properly encoded (UTF-8)
- [ ] Test flag file accessibility
- [ ] Configure secure cookie settings
- [ ] Enable production error handling
- [ ] Set up proper caching headers for static assets
- [ ] Test RTL languages display correctly

### Performance Monitoring
- Monitor translation loading times
- Track cache hit rates
- Monitor memory usage
- Log language detection failures

## Common Issues and Solutions

### Issue: Language Not Detected
**Solution**: Check file permissions, validate language codes, verify cookie settings

### Issue: Missing Translations
**Solution**: Implement proper fallback mechanism, add missing keys to language files

### Issue: Flag Images Not Loading
**Solution**: Verify file paths, check web server configuration, ensure proper MIME types

### Issue: RTL Layout Problems
**Solution**: Test CSS with RTL languages, use logical CSS properties, implement proper text direction

## Maintenance

### Regular Tasks
- Update language files with new translations
- Add new languages as needed
- Monitor and update flag images
- Review and update security settings
- Clean up unused translation keys

### Version Control
- Track language file changes
- Maintain translation history
- Document breaking changes
- Use semantic versioning for language updates

## Contributing

### Adding New Languages
1. Create language file using ISO 639-1/639-2 code
2. Translate all required keys
3. Test with actual users if possible
4. Add flag image to assets
5. Update documentation

### Translation Guidelines
- Use native language names
- Maintain consistent terminology
- Consider cultural context
- Test with native speakers
- Keep translations concise but clear

## API Integration

### REST API Support
For external applications:
```php
// Get available languages
GET /api/languages

// Get translations for language
GET /api/languages/{code}/translations

// Set user language
POST /api/user/language
```

### JavaScript Integration
```javascript
// Client-side language switching
function switchLanguage(langCode) {
    document.cookie = `language=${langCode}; path=/; secure; samesite=lax`;
    window.location.reload();
}
```

## Future Enhancements

### Planned Features
- Database-based translations
- Translation management interface
- Pluralization support
- Date/time localization
- Number formatting
- Currency localization

### Extensibility
- Plugin system for custom detectors
- Event hooks for language changes
- Custom translation providers
- Integration with external translation services

## License

This language management system is part of the Renal Tales project and follows the same licensing terms.

## Support

For issues, questions, or contributions:
- Check the documentation first
- Review common issues section
- Test with debug mode enabled
- Report issues with full context and error logs

---

*Last updated: 2025-01-09*
*Version: 2025.v1.0test*
