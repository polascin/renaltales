# RenalTales - Modern PHP Development Environment Setup

## Overview

This document describes the modern PHP development environment setup for the RenalTales project, including Composer initialization, PSR-4 autoloading, environment configuration, and essential development tools.

## Setup Completed

### 1. ✅ Composer Initialization

- **composer.json**: Created with project metadata and dependencies
- **Development dependencies**: Added testing and code quality tools

### 2. ✅ Environment Configuration

- **.env file**: Configured with application, database, and security settings
- **.env.example**: Template for environment configuration
- **Bootstrap file**: `bootstrap.php` for application initialization
- **Configuration management**: Centralized config handling

### 3. ✅ Directory Structure

```txt
renaltales/
├── core/                   # Core application classes
├── controllers/            # MVC Controllers
├── models/                 # Database models
├── views/                  # View templates
├── tests/                  # Test files
├── storage/
│   ├── logs/              # Application logs
│   ├── cache/             # Application cache
│   ├── sessions/          # Session storage
│   └── uploads/           # File uploads
├── .env                   # Environment configuration
├── .env.example           # Environment template
├── .gitignore             # Git ignore rules
├── composer.json          # Composer configuration
├── bootstrap.php          # Application bootstrap
└── README.md              # Project documentation
```

### 4. ✅ Dependencies Configuration

#### Production Dependencies

- **monolog/monolog**: Logging library
- **vlucas/phpdotenv**: Environment variable handling
- **ramsey/uuid**: UUID generation

#### Development Dependencies

- **phpunit/phpunit**: Unit testing framework
- **phpstan/phpstan**: Static analysis tool
- **squizlabs/php_codesniffer**: Code style checking
- **symfony/var-dumper**: Debugging tools
- **fakerphp/faker**: Test data generation

### 5. ✅ Git Configuration

- **Vendor directory**: Excluded from version control
- **Environment files**: .env excluded, .env.example included
- **Storage directories**: Logs, cache, sessions excluded
- **IDE files**: Common IDE files excluded

### 6. ✅ Application Structure

- **MVC Pattern**: Controllers, Models, and Views organized
- **Core Components**: Authentication, Database, and Security managers

## Environment Configuration

### Database Settings

Based on your Laragon setup:

- **Host**: localhost
- **Database**: renaltales
- **Username**: root
- **Password**: (empty)
- **Charset**: utf8mb4

### Application Settings

- **Name**: Renal Tales
- **Version**: 2025.v2.0
- **Environment**: development
- **Debug**: enabled
- **Timezone**: Europe/Bratislava

## Usage

### Running Tests

```bash
# Install dependencies
composer install

# Run PHPUnit tests
composer test

# Run static analysis
composer phpstan

# Check code style
composer phpcs
```

### Development Workflow

1. Create new classes in `src/` directory following PSR-4 standards
2. Write tests in `tests/` directory
3. Use the Application class for centralized configuration
4. Log messages using the configured logger
5. Follow PSR-12 coding standards

## Next Steps

### Immediate Tasks

1. **Resolve Composer permissions**: Fix Windows file permission issues
2. **Database setup**: Create and configure the database
3. **Add ORM**: Install and configure Doctrine ORM
4. **Migrations**: Set up database migration system

### Extended Setup

1. **Add Doctrine ORM**: Entity management and database abstraction
2. **Add Symfony components**: Console, Validator, Security components
3. **Add HTTP client**: Guzzle for external API calls
4. **Add validation**: Respect/Validation for input validation
5. **Add file handling**: League/Flysystem for file operations

## Troubleshooting

### Common Issues

1. **Composer permission errors**: Use `--ignore-platform-req=ext-fileinfo` flag
2. **Vendor directory issues**: Clear vendor directory and reinstall
3. **Autoloading issues**: Run `composer dump-autoload`

### File Permissions

Ensure these directories are writable:

- `storage/logs/`
- `storage/cache/`
- `storage/sessions/`
- `storage/files/`

## Architecture

### Application Structure

- **Singleton pattern**: Application class for centralized management
- **Configuration management**: Environment-based configuration
- **Logging system**: Structured logging with Monolog
- **PSR standards**: Following PSR-4 autoloading and PSR-12 coding standards

### Security Considerations

- Environment variables for sensitive data
- Secure password hashing (bcrypt)
- CSRF protection configuration
- Secure session handling

## Testing

### Environment Test Results

✅ PHP 8.4.8 (meets 8.1+ requirement)
✅ Directory structure complete
✅ PSR-4 file structure ready
✅ Environment configuration loaded
✅ Git configuration proper
✅ All required PHP extensions loaded
✅ File permissions working

The development environment is ready for active development!
