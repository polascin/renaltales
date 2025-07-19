# **Renal Tales**

> *Web Application by* ***Lumpe Paskuden von Lumpenen*** *aka* ***Walter Kyo*** *or* ***Walter Csoelle Kyo***  
> *Author:* Lubomir Polascin (Ľubomír Polaščín)  
> *Technology used:* **PHP**, **HTML**, **CSS**, **JavaScript**, **MySQL**

---

## Project Overview

Renal Tales is a multilingual web application for sharing kidney disorder stories, built with PHP using modern architectural patterns including Dependency Injection, Service Layer pattern, and Repository pattern.

## Features

- **Multilingual Support**: Full internationalization with language switching
- **Modern Architecture**: Dependency injection, service layers, and repository patterns
- **Responsive Design**: Mobile-first responsive CSS architecture
- **Theme System**: Light/dark mode support with system preference detection
- **Performance Optimized**: CSS optimization, critical path loading, and caching
- **Accessibility**: ARIA support, keyboard navigation, and screen reader friendly

## Architecture

### Backend (PHP)
- **Dependency Injection Container**: PSR-11 compliant container for service management
- **Service Layer**: Business logic separation with dedicated service classes
- **Repository Pattern**: Data access abstraction layer
- **PSR Standards**: Following PSR-7, PSR-11, PSR-15 standards
- **Error Handling**: Comprehensive error handling and logging

### Frontend (CSS/JS)
- **Modern CSS Architecture**: BEM methodology with CSS custom properties
- **Component-Based**: Modular CSS components with clear separation
- **Responsive Design**: Mobile-first approach with comprehensive breakpoints
- **Performance**: Critical CSS, lazy loading, and optimization strategies

## Getting Started

### Prerequisites
- PHP 8.4+
- MySQL/MariaDB
- Composer
- Node.js (for CSS build tools)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-repo/renaltales.git
   cd renaltales
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Configure environment**
   ```bash
   cp .env.example .env
   # Edit .env with your database credentials
   ```

5. **Build CSS assets**
   ```bash
   npm run build:prod
   ```

6. **Set up database**
   - Create database and configure connection in `.env`
   - Run any required migrations

### Development

#### CSS Development
```bash
# Watch for CSS changes
npm run watch:css

# Build for development
npm run build:dev

# Build for production
npm run build:prod
```

#### Testing
```bash
# Run all tests
composer test:all

# Run specific test suites
composer test:unit
composer test:integration
composer test:feature

# Run with coverage
composer test:coverage

# Behavior-driven development tests
composer behat
```

#### Code Quality
```bash
# Static analysis
composer phpstan

# Code style check
composer phpcs

# Fix code style
composer phpcbf

# All quality checks
composer quality
```

## Documentation

This project's documentation is organized into focused directories for better navigation:

### 📋 Core Documentation
- [Architecture Overview](ARCHITECTURE.md) - System architecture and patterns
- [Refactoring Plan](REFACTORING_PLAN.md) - Strategic refactoring roadmap
- [Team Guidelines](TEAM_NOTIFICATION.md) - Development team guidelines
- [Scripts Documentation](SCRIPTS_README.md) - Build and utility scripts

### 🎨 CSS Documentation
See [css/](css/) directory for comprehensive CSS documentation:
- Architecture guides and patterns
- Optimization and cleanup strategies
- File references and mapping
- Variable naming conventions

### 📊 Project Summaries
See [summaries/](summaries/) directory for activity summaries:
- Cleanup and migration summaries
- Component and layout work summaries
- Upgrade and consolidation reports

### ✅ Validation & Testing
See [validation/](validation/) directory for quality assurance:
- Testing guidelines and procedures
- Validation reports and audits
- Quality improvement documentation

## Project Structure

```
renaltales/
├── src/                    # PHP source code
│   ├── Controllers/        # HTTP controllers
│   ├── Services/          # Business logic services
│   ├── Repositories/      # Data access layer
│   ├── Core/              # Core application services
│   ├── Views/             # View components
│   └── ...
├── public/                # Public web assets
│   ├── assets/css/        # Compiled CSS files
│   ├── assets/js/         # JavaScript files
│   └── index.php          # Application entry point
├── docs/                  # Documentation
│   ├── css/              # CSS-related documentation
│   ├── summaries/        # Project activity summaries
│   ├── validation/       # Testing and quality docs
│   └── README.md         # Main documentation index
├── tests/                 # Test suites
├── components/            # CSS component source
├── core/                  # CSS core styles
└── layout/                # CSS layout utilities
```

## Contributing

1. **Follow coding standards**
   - PSR-12 for PHP code
   - BEM methodology for CSS
   - Use provided linting configurations

2. **Write tests**
   - Unit tests for services and repositories
   - Integration tests for controllers
   - BDD tests for user features

3. **Update documentation**
   - Keep architecture docs current
   - Document new CSS components
   - Update this README when adding features

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Contact

- **Author**: Lubomir Polascin (Ľubomír Polaščín)
- **Project**: Renal Tales
- **Technology Stack**: PHP 8.4, MySQL, Modern CSS, JavaScript

---

**Last Updated**: July 2025  
**Version**: 2.0.0  
**Status**: Active Development
