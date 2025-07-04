# RenalTales Project Setup

This document describes the project configuration and dependencies that have been set up.

## ✅ Completed Setup Tasks

### 1. Composer Autoloading
- ✅ Composer dependencies installed (production only)
- ✅ PSR-4 autoloading configured for `RenalTales\` namespace
- ✅ Autoload file includes helper functions from `src/helpers.php`

### 2. Environment Variables Configuration
- ✅ `.env` file updated with `APP_KEY`
- ✅ Configuration system updated to use environment variables
- ✅ Bootstrap autoloader handles environment variable loading

### 3. Database Configuration
- ✅ Created `DatabaseConnection` class with singleton pattern
- ✅ PDO connection with proper settings (error handling, charset, etc.)
- ✅ Updated existing `Model` class to use new `DatabaseConnection`
- ✅ Environment-based database configuration

### 4. Internationalization (i18n)
- ✅ Created `i18n/` directory for language files
- ✅ English (`en.php`) and Slovak (`sk.php`) language files
- ✅ Structured language keys for navigation, forms, messages, etc.

## 📁 Directory Structure

```
renaltales/
├── bootstrap/
│   └── autoload.php          # Bootstrap file for autoloading and environment
├── config/
│   └── config.php            # Updated configuration with environment variables
├── i18n/                     # Internationalization files
│   ├── en.php               # English translations
│   └── sk.php               # Slovak translations
├── src/
│   ├── Database/
│   │   ├── DatabaseConnection.php  # New PDO connection singleton
│   │   └── MigrationManager.php    # Existing migration manager
│   ├── Model/
│   │   └── Model.php        # Updated to use DatabaseConnection
│   └── ...
├── vendor/                   # Composer dependencies
├── .env                     # Environment variables (updated with APP_KEY)
└── composer.json            # Composer configuration
```

## 🔧 Key Components

### DatabaseConnection Class
- **Location**: `src/Database/DatabaseConnection.php`
- **Pattern**: Singleton
- **Features**: 
  - Automatic environment-based configuration
  - Proper PDO attributes for security and performance
  - Reconnection capability
  - UTF-8 charset handling

### Environment Configuration
- **File**: `.env`
- **New Variables**: `APP_KEY` for application encryption
- **Config Structure**: Converted from constants to array-based configuration
- **Usage**: All config values now support environment variable overrides

### Bootstrap System
- **File**: `bootstrap/autoload.php`
- **Purpose**: Initialize autoloading, environment variables, and basic settings
- **Usage**: Include this file at the start of any script

## 🚀 Usage Examples

### Database Connection
```php
require_once 'bootstrap/autoload.php';
use RenalTales\Database\DatabaseConnection;

$pdo = DatabaseConnection::getInstance();
$result = $pdo->query("SELECT * FROM users")->fetchAll();
```

### Configuration Access
```php
use RenalTales\Core\Config;

$config = new Config(__DIR__ . '/config/config.php');
$dbHost = $config->get('database.host'); // Uses environment variables
```

### Language Files
```php
$en = require 'i18n/en.php';
echo $en['nav.home']; // "Home"

$sk = require 'i18n/sk.php';
echo $sk['nav.home']; // "Domov"
```

## 📋 Environment Variables

The following environment variables are now supported:

### Application
- `APP_NAME` - Application name
- `APP_ENV` - Environment (development/production)
- `APP_DEBUG` - Debug mode (true/false)
- `APP_URL` - Application URL
- `APP_KEY` - Encryption key (base64 encoded)
- `APP_TIMEZONE` - Application timezone

### Database
- `DB_DRIVER` - Database driver (mysql)
- `DB_HOST` - Database host
- `DB_DATABASE` - Database name
- `DB_USERNAME` - Database username
- `DB_PASSWORD` - Database password
- `DB_CHARSET` - Database charset
- `DB_PORT` - Database port

### Mail
- `MAIL_HOST` - SMTP host
- `MAIL_PORT` - SMTP port
- `MAIL_USERNAME` - SMTP username
- `MAIL_PASSWORD` - SMTP password
- `MAIL_ENCRYPTION` - SMTP encryption (tls/ssl)
- `MAIL_FROM_ADDRESS` - Default sender email
- `MAIL_FROM_NAME` - Default sender name

## 🔒 Security Features

1. **Proper PDO Configuration**: Error mode set to exceptions, prepared statements enabled
2. **Environment Variables**: Sensitive data moved to environment variables
3. **Error Reporting**: Environment-based error reporting configuration
4. **Charset Handling**: UTF-8 charset properly configured for database connections

## 🌐 Internationalization

The i18n system includes:
- Structured language keys with dot notation
- Support for multiple languages (English and Slovak included)
- Categories for different types of content (navigation, forms, messages, etc.)
- Easy to extend with additional languages

## 📦 Installed Dependencies

### Production Dependencies
- `paragonie/password_lock` - Secure password hashing
- `paragonie/anti-csrf` - CSRF protection
- `respect/validation` - Input validation
- `phpmailer/phpmailer` - Email sending
- `defuse/php-encryption` - Encryption utilities
- `symfony/http-foundation` - HTTP abstraction
- `symfony/routing` - URL routing
- `monolog/monolog` - Logging

### Development Dependencies (not installed)
- `phpunit/phpunit` - Unit testing
- `phpstan/phpstan` - Static analysis
- `squizlabs/php_codesniffer` - Code style checking

To install development dependencies:
```bash
composer install --dev
```

## ⚡ Next Steps

1. Set up database tables using the migration system
2. Configure web server to use `public/` as document root
3. Update existing controllers and services to use new configuration system
4. Implement language switching functionality using the i18n files
5. Set up proper production environment variables
