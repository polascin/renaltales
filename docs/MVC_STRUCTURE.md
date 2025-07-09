# MVC Architecture Documentation

## Overview

The Renal Tales application has been refactored to follow the Model-View-Controller (MVC) design pattern to improve modularity, testability, and maintainability.

## Directory Structure

```
renaltales/
├── config/              # Configuration files
│   ├── app.php          # Main application config
│   ├── database.php     # Database configuration
│   └── .prettierrc      # Code formatting rules
├── controllers/         # Controller classes
│   ├── BaseController.php
│   └── ApplicationController.php
├── core/               # Core framework components
│   ├── Application.php
│   ├── Database.php
│   ├── EmailVerificationManager.php
│   ├── LanguageDetector.php
│   ├── Logger.php
│   ├── PasswordResetManager.php
│   └── SessionManager.php
├── database/           # Database scripts and migrations
│   ├── schema/         # Table schema files
│   ├── setup_database.sql
│   └── logging_system_setup.sql
├── docs/               # Project documentation
│   ├── README.md
│   ├── MVC_STRUCTURE.md
│   ├── refaktoring.md
│   └── database_README.md
├── models/             # Model classes
│   ├── BaseModel.php
│   └── LanguageModel.php
├── public/             # Public web-accessible files
│   ├── index.php       # Front controller
│   └── assets/         # CSS, JS, images, flags
│       ├── css/        # Stylesheets
│       ├── js/         # JavaScript files
│       ├── images/     # Images and illustrations
│       ├── flags/      # Country flag assets
│       └── templates/  # HTML templates
├── resources/          # Application resources
│   ├── lang/          # Language translation files
│   └── views/         # View templates (future)
├── storage/           # Application storage
│   ├── cache/         # Cache files
│   ├── logs/          # Log files
│   ├── sessions/      # Session files
│   ├── temp/          # Temporary files
│   └── uploads/       # User uploaded files
├── views/             # View classes
│   ├── BaseView.php
│   ├── ApplicationView.php
│   └── ErrorView.php
├── .env.example       # Environment configuration template
├── .gitignore         # Git ignore rules
└── .htaccess          # Apache configuration
```

## Components

### Models
- **BaseModel**: Abstract base class providing common database operations (CRUD)
- **LanguageModel**: Handles language detection, loading, and management

### Views  
- **BaseView**: Abstract base class for all views with common rendering functionality
- **ApplicationView**: Main application page view
- **ErrorView**: Error page view with debug/production modes

### Controllers
- **BaseController**: Base class for all controllers
- **ApplicationController**: Main application controller handling requests and business logic

## Features

### Separation of Concerns
- **Models**: Handle business logic and data operations
- **Views**: Handle presentation and HTML rendering
- **Controllers**: Handle user input and coordinate between models and views

### Security
- CSRF token validation
- Input sanitization and validation
- Secure session management

### Error Handling
- Graceful error handling with appropriate views
- Debug mode for development vs. production error pages
- Comprehensive error logging

### Multilingual Support
- Language detection and management through LanguageModel
- Flexible text rendering in views
- Support for language switching

## Usage

### Adding New Controllers
```php
class NewController extends BaseController {
    public function actionMethod() {
        // Controller logic here
        $this->render('view_name', $data);
    }
}
```

### Adding New Models
```php
class NewModel extends BaseModel {
    protected $table = 'table_name';
    
    protected function validate($data) {
        // Validation logic
        return $errors;
    }
}
```

### Adding New Views
```php
class NewView extends BaseView {
    protected function renderContent() {
        // View rendering logic
    }
}
```

## Benefits

1. **Improved Maintainability**: Clear separation of concerns makes code easier to maintain
2. **Enhanced Testability**: Each component can be tested independently
3. **Better Security**: Centralized validation and sanitization
4. **Scalability**: Easy to add new features and functionality
5. **Code Reusability**: Base classes provide common functionality
6. **Error Handling**: Comprehensive error management system

## Migration Notes

- Original `Application.php` has been moved to `Application.php.backup`
- All presentation logic has been moved to view classes
- Business logic has been separated into model classes
- Request handling is now managed by controller classes
- The front controller (`public/index.php`) now follows MVC pattern

## Future Enhancements

- Add routing system for more complex URL handling
- Implement more models for specific business entities
- Add form handling and validation classes
- Implement caching mechanisms
- Add API endpoints for AJAX functionality
