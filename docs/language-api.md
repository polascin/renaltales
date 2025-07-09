# Language Management API Documentation

## Overview

The Language Management API provides RESTful endpoints for managing languages, translations, and user language preferences in the Renal Tales application.

## Base URL

```
/api/languages
```

## Authentication

Currently uses session-based authentication. Include session cookies in requests.

## Endpoints

### Get Available Languages

**GET** `/api/languages`

Returns a list of all available languages with their metadata.

#### Response

```json
{
  "success": true,
  "data": [
    {
      "code": "en",
      "name": "English",
      "native_name": "English",
      "flag": "gb",
      "flag_path": "/assets/flags/gb.webp",
      "rtl": false,
      "supported": true
    },
    {
      "code": "sk",
      "name": "Slovak",
      "native_name": "Slovenčina",
      "flag": "sk",
      "flag_path": "/assets/flags/sk.webp",
      "rtl": false,
      "supported": true
    }
  ]
}
```

### Get Language Details

**GET** `/api/languages/{code}`

Returns details for a specific language.

#### Parameters

- `code` (string): Language code (e.g., 'en', 'sk')

#### Response

```json
{
  "success": true,
  "data": {
    "code": "en",
    "name": "English",
    "native_name": "English",
    "flag": "gb",
    "flag_path": "/assets/flags/gb.webp",
    "rtl": false,
    "supported": true,
    "variants": ["en", "en-us", "en-gb", "en-ca"],
    "countries": ["US", "GB", "CA", "AU", "NZ"],
    "translation_stats": {
      "total_keys": 138,
      "translated_keys": 138,
      "completion_percentage": 100
    }
  }
}
```

### Get Translations

**GET** `/api/languages/{code}/translations`

Returns all translations for a specific language.

#### Parameters

- `code` (string): Language code
- `keys` (string, optional): Comma-separated list of specific keys to retrieve

#### Response

```json
{
  "success": true,
  "data": {
    "language": "en",
    "translations": {
      "app_title": "Kidney Stories",
      "welcome": "Welcome!",
      "current_language": "Current language",
      "error": "Error"
    }
  }
}
```

### Set User Language

**POST** `/api/languages/set`

Sets the user's preferred language.

#### Request Body

```json
{
  "language": "sk"
}
```

#### Response

```json
{
  "success": true,
  "message": "Language set successfully",
  "data": {
    "language": "sk",
    "language_name": "Slovenčina",
    "flag_path": "/assets/flags/sk.webp"
  }
}
```

### Detect User Language

**GET** `/api/languages/detect`

Detects the user's preferred language using various methods.

#### Response

```json
{
  "success": true,
  "data": {
    "detected_language": "sk",
    "detection_method": "browser_header",
    "confidence": 0.95,
    "fallback_used": false,
    "available_methods": [
      "url_parameter",
      "session",
      "cookie",
      "browser_header",
      "geolocation"
    ]
  }
}
```

### Get Language Statistics

**GET** `/api/languages/stats`

Returns statistics about language usage and translations.

#### Response

```json
{
  "success": true,
  "data": {
    "total_languages": 150,
    "supported_languages": 4,
    "translation_stats": {
      "most_complete": {
        "language": "en",
        "completion": 100
      },
      "least_complete": {
        "language": "test",
        "completion": 45
      },
      "average_completion": 87.5
    },
    "usage_stats": {
      "most_used": "en",
      "language_distribution": {
        "en": 65,
        "sk": 20,
        "de": 10,
        "cs": 5
      }
    }
  }
}
```

### Get Flag Information

**GET** `/api/languages/{code}/flag`

Returns flag information for a specific language.

#### Response

```json
{
  "success": true,
  "data": {
    "language": "en",
    "flag_code": "gb",
    "flag_path": "/assets/flags/gb.webp",
    "alternative_formats": [
      "/assets/flags/gb.png",
      "/assets/flags/gb.jpg"
    ],
    "exists": true
  }
}
```

### Validate Language Code

**GET** `/api/languages/validate/{code}`

Validates a language code and returns its status.

#### Response

```json
{
  "success": true,
  "data": {
    "code": "sk",
    "valid": true,
    "supported": true,
    "format_valid": true,
    "exists": true
  }
}
```

## Error Responses

All endpoints return standardized error responses:

```json
{
  "success": false,
  "error": {
    "code": "LANGUAGE_NOT_FOUND",
    "message": "The requested language was not found",
    "details": "Language code 'xyz' is not supported"
  }
}
```

### Common Error Codes

- `LANGUAGE_NOT_FOUND`: Requested language doesn't exist
- `INVALID_LANGUAGE_CODE`: Language code format is invalid
- `TRANSLATION_NOT_FOUND`: Translation key not found
- `UNSUPPORTED_LANGUAGE`: Language exists but is not supported
- `DETECTION_FAILED`: Language detection failed
- `INVALID_REQUEST`: Request format is invalid

## Implementation Examples

### JavaScript/jQuery

```javascript
// Get available languages
$.get('/api/languages', function(response) {
    if (response.success) {
        populateLanguageDropdown(response.data);
    }
});

// Set user language
function setLanguage(langCode) {
    $.post('/api/languages/set', {
        language: langCode
    }).done(function(response) {
        if (response.success) {
            location.reload();
        }
    });
}

// Get translations
$.get('/api/languages/en/translations', function(response) {
    if (response.success) {
        window.translations = response.data.translations;
    }
});
```

### PHP

```php
// Get available languages
$languages = json_decode(file_get_contents('/api/languages'), true);

// Set user language
$postData = json_encode(['language' => 'sk']);
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $postData
    ]
]);
$response = file_get_contents('/api/languages/set', false, $context);
```

### Python

```python
import requests

# Get available languages
response = requests.get('/api/languages')
languages = response.json()

# Set user language
response = requests.post('/api/languages/set', json={'language': 'sk'})
result = response.json()
```

## Rate Limiting

The API implements rate limiting to prevent abuse:

- **Translation requests**: 100 requests per minute per IP
- **Language detection**: 10 requests per minute per IP
- **Language setting**: 5 requests per minute per session

Rate limit headers are included in responses:

```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1609459200
```

## Caching

The API uses intelligent caching:

- **Language list**: Cached for 1 hour
- **Translations**: Cached for 24 hours
- **Flag paths**: Cached for 1 week
- **Detection results**: Cached for 1 hour per session

Cache headers are included:

```
Cache-Control: public, max-age=3600
ETag: "abc123"
Last-Modified: Wed, 21 Oct 2015 07:28:00 GMT
```

## WebSocket Support

For real-time language updates:

```javascript
const ws = new WebSocket('ws://localhost:8080/language-updates');

ws.onmessage = function(event) {
    const data = JSON.parse(event.data);
    if (data.type === 'language_changed') {
        updateUILanguage(data.language);
    }
};
```

## Hooks and Events

The API supports event hooks for language changes:

```php
// Register language change hook
LanguageManager::addHook('language_changed', function($oldLang, $newLang) {
    // Custom logic when language changes
    Logger::log("Language changed from {$oldLang} to {$newLang}");
});
```

## Security Considerations

### Input Validation

All language codes are validated using regex patterns:

```php
if (!preg_match('/^[a-z]{2,3}(-[a-z]{2,4})?$/i', $langCode)) {
    throw new InvalidLanguageCodeException();
}
```

### XSS Prevention

All translation strings are escaped when returned:

```php
$translation = htmlspecialchars($translation, ENT_QUOTES, 'UTF-8');
```

### CSRF Protection

Language setting requires CSRF tokens:

```javascript
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
```

## Monitoring and Analytics

### API Metrics

Track important metrics:

- Language detection success rate
- Translation loading times
- Error rates by endpoint
- Most requested languages
- Geographic distribution of language preferences

### Logging

Comprehensive logging for debugging:

```php
// Example log entry
[2025-01-09 19:50:33] INFO: Language detection - IP: 192.168.1.1, Method: browser_header, Result: sk, Time: 0.05s
```

## Version History

### v1.0.0 (2025-01-09)
- Initial API implementation
- Basic CRUD operations
- Language detection
- Translation management

### Future Versions
- v1.1.0: Translation management interface
- v1.2.0: Pluralization support
- v1.3.0: Advanced translation features

## Testing

### Unit Tests

```php
public function testLanguageAPI() {
    $response = $this->get('/api/languages');
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'data' => [
            '*' => [
                'code',
                'name',
                'native_name',
                'flag',
                'flag_path',
                'rtl',
                'supported'
            ]
        ]
    ]);
}
```

### Integration Tests

```javascript
describe('Language API', function() {
    it('should return available languages', function() {
        return fetch('/api/languages')
            .then(response => response.json())
            .then(data => {
                expect(data.success).toBe(true);
                expect(data.data).toBeInstanceOf(Array);
            });
    });
});
```

## Migration Guide

### From v1.0 to v1.1

Changes in v1.1:
- New endpoint: `/api/languages/translations/manage`
- Updated response format for `/api/languages/stats`
- Deprecated: Direct translation file access

Migration steps:
1. Update API client libraries
2. Test new endpoints
3. Update error handling
4. Verify backward compatibility

## Support

For API support:
- Check status page: `/api/status`
- Review error logs
- Test with debug mode: `?debug=1`
- Contact support with request/response details

---

*Last updated: 2025-01-09*
*Version: 1.0.0*
