# Language System Test Suite

This test suite provides comprehensive browser-based testing for the language system functionality.

## Files Created

1. **test_language_system.php** - Main test interface
2. **language_status.php** - API endpoint for language status
3. **switch_language.php** - API endpoint for language switching

## Features

### 🌍 Real-Time Language Status
- Current language display with flag
- Session and cookie information
- Browser detection details
- Language persistence indicators

### 🔧 Manual Language Testing
- Quick test buttons for common languages
- Dynamic buttons for all supported languages
- Visual feedback for language switches
- Active language highlighting

### 💾 Persistence Status Monitoring
- Session status indicators
- Cookie status indicators
- Session/cookie synchronization status
- Color-coded status indicators (green=good, red=error, orange=warning)

### 🔍 Detailed Information Display
- **Session Information**: Current language, session ID, timestamp
- **Cookie Information**: Language setting, expiration status
- **Detection Information**: Browser headers, user agent, IP address

### 🧪 Automated Testing
- Sequential language switching tests
- Success/failure reporting
- Test result logging
- Visual test progress

### ⚡ Real-Time Features
- Auto-refresh option (every 2 seconds)
- Manual refresh button
- Live status updates
- Toast notifications for actions

## Usage

### Accessing the Test Page

1. **Web Browser**: Navigate to `http://localhost/renaltales/test_language_system.php`
2. **Local Development**: Ensure your web server is running (Laragon/XAMPP/etc.)

### Testing Language Switching

1. **Quick Test**: Use the preset language buttons (English, Slovak, German, French, Spanish)
2. **Full Test**: Use the dynamically generated buttons for all supported languages
3. **Automated Test**: Click "Run All Tests" to test multiple languages sequentially

### Monitoring Status

- **Auto-Refresh**: Enable the checkbox to automatically refresh status every 2 seconds
- **Manual Refresh**: Click the "Refresh Status" button to update information
- **Status Indicators**: 
  - 🟢 Green circle = Good/Active
  - 🔴 Red circle = Error/Inactive  
  - 🟠 Orange circle = Warning/Mismatch

### Visual Feedback

- **Success Messages**: Green toast notifications in top-right corner
- **Error Messages**: Red toast notifications in top-right corner
- **Active Language**: Highlighted with green button color
- **Flag Display**: Country flags next to language names

## API Endpoints

### language_status.php
```
GET /language_status.php
Returns: JSON object with current language status
```

### switch_language.php
```
GET /switch_language.php?lang=<language_code>
Returns: JSON object with switch result and updated status
```

## Troubleshooting

### Common Issues

1. **No Languages Showing**: Check if language files exist in `resources/lang/`
2. **Session Issues**: Ensure PHP sessions are working properly
3. **Cookie Issues**: Check browser cookie settings
4. **Flag Images**: Flags are loaded from external CDN (flagcdn.com)

### Debug Information

The test page displays detailed information for debugging:
- Browser Accept-Language headers
- User agent information
- IP address
- Session and cookie values
- Language detection results

## Browser Compatibility

- Modern browsers (Chrome, Firefox, Safari, Edge)
- Requires JavaScript enabled
- Responsive design for mobile devices
- Flag images require internet connection

## Security Notes

- All language inputs are sanitized
- Session security is handled by LanguageManager
- Cookie security follows secure practices
- No sensitive information is exposed in debug mode

## Customization

### Adding New Languages

1. Add language files to `resources/lang/`
2. Update LanguageManager configuration
3. Test page will automatically detect new languages

### Modifying Test Cases

Edit the `testLanguages` array in `test_language_system.php` to change which languages are tested in automated mode.

### Styling

The test page uses embedded CSS that can be customized:
- Color scheme
- Layout (grid-based, responsive)
- Flag display settings
- Status indicator colors
