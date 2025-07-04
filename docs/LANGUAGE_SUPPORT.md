# Language Support Documentation

## Overview

RenalTales supports **45 languages** to provide accessibility to kidney disorder communities worldwide. Each language has a comprehensive translation file containing all user interface strings.

## Supported Languages

### European Languages
- **English** (en) - Default language
- **Slovak** (sk) - Primary language for the project
- **Czech** (cs) - Čeština
- **German** (de) - Deutsch
- **French** (fr) - Français
- **Spanish** (es) - Español
- **Italian** (it) - Italiano
- **Dutch** (nl) - Nederlands
- **Portuguese** (pt) - Português
- **Polish** (pl) - Polski
- **Hungarian** (hu) - Magyar
- **Romanian** (ro) - Română
- **Bulgarian** (bg) - Български
- **Croatian** (hr) - Hrvatski
- **Serbian** (sr) - Српски
- **Slovenian** (sl) - Slovenščina
- **Macedonian** (mk) - Македонски
- **Albanian** (sq) - Shqip
- **Greek** (el) - Ελληνικά

### Nordic Languages
- **Danish** (da) - Dansk
- **Norwegian** (no) - Norsk
- **Swedish** (sv) - Svenska
- **Finnish** (fi) - Suomi
- **Icelandic** (is) - Íslenska

### Baltic Languages
- **Estonian** (et) - Eesti
- **Latvian** (lv) - Latviešu
- **Lithuanian** (lt) - Lietuvių

### Other European
- **Turkish** (tr) - Türkçe
- **Russian** (ru) - Русский
- **Ukrainian** (uk) - Українська

### Asian Languages
- **Japanese** (ja) - 日本語
- **Chinese Simplified** (zh) - 中文
- **Korean** (ko) - 한국어
- **Arabic** (ar) - العربية
- **Hindi** (hi) - हिन्दी
- **Thai** (th) - ไทย
- **Vietnamese** (vi) - Tiếng Việt
- **Indonesian** (id) - Bahasa Indonesia
- **Malay** (ms) - Bahasa Melayu
- **Filipino** (tl) - Filipino

### African Languages
- **Swahili** (sw) - Kiswahili
- **Amharic** (am) - አማርኛ
- **Yoruba** (yo) - Yorùbá
- **Zulu** (zu) - isiZulu

### Special Languages
- **Esperanto** (eo) - Esperanto

## Translation Structure

Each language file (`i18n/{language_code}.php`) contains translations organized into sections:

### Navigation
- Menu items and navigation elements
- Categories, user management, moderation links

### Common Actions
- Button labels (Save, Cancel, Delete, Edit, etc.)
- Form actions and controls

### Authentication
- Login and registration forms
- User account management
- Error messages

### Stories
- Story-related interface elements
- Categories, tags, publishing options

### Forms
- Form validation messages
- Field labels and placeholders
- Required field indicators

### Messages
- Success and error notifications
- System messages
- User feedback

### Footer
- Copyright information
- Legal links (Privacy Policy, Terms of Service)
- Support information

### Home Page
- Hero section content
- Statistics display
- Featured content sections

## Usage

### In Controllers
```php
// Access translation in controllers
$translatedText = $this->languageManager->translate('auth.login.title');
```

### In Views
```php
// Access translation in views using the $t array
echo $t['auth.login.title'];

// Or use the language manager directly
echo $this->languageManager->translate('stories.title');
```

### Language Detection
The system automatically detects user language preferences from:
1. User account settings (logged-in users)
2. Browser language headers
3. Falls back to default language (Slovak)

## Adding New Languages

1. Create a new language file in `i18n/{language_code}.php`
2. Copy the structure from `en.php` as a template
3. Translate all strings while maintaining the same keys
4. Add the language to the configuration file
5. Test the language switching functionality

## Translation Guidelines

1. **Maintain Key Structure**: Never change the translation keys, only the values
2. **Context Awareness**: Consider the medical context of the platform
3. **Cultural Sensitivity**: Ensure translations are appropriate for kidney disorder communities
4. **Consistency**: Use consistent terminology throughout each language
5. **Length Considerations**: Some languages may require more space for translations

## File Organization

```
i18n/
├── en.php          # English (base language)
├── sk.php          # Slovak (primary)
├── cs.php          # Czech
├── de.php          # German
├── fr.php          # French
├── es.php          # Spanish
└── ...             # Other languages
```

## Configuration

Language settings are defined in `config/config.php`:

```php
'languages' => [
    'default' => 'sk',                    // Default language
    'fallback' => 'en',                   // Fallback language
    'detect_from_browser' => true,        // Auto-detect from browser
    'supported' => ['en', 'sk', 'es']     // UI language switching
],

'story_languages' => [
    'en' => 'English',
    'sk' => 'Slovenčina',
    // ... all 45 languages for story content
]
```

## Maintenance

- Language files should be updated whenever new UI elements are added
- Regular review of translations for accuracy and cultural appropriateness
- Community feedback integration for improving translations
- Automated tools can be used to detect missing translations

## Future Enhancements

1. **Community Translation System**: Allow community members to contribute translations
2. **Translation Management Interface**: Admin panel for managing translations
3. **Pluralization Support**: Handle plural forms for different languages
4. **RTL Language Support**: Support for right-to-left languages like Arabic
5. **Dynamic Language Loading**: Load language files on-demand for better performance
