# Contributing to Renal Tales

Thank you for your interest in contributing to Renal Tales! This document provides guidelines for contributing to the project.

## 📋 Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Coding Standards](#coding-standards)
- [Development Workflow](#development-workflow)
- [Testing](#testing)
- [Documentation](#documentation)
- [Submitting Changes](#submitting-changes)

## 🤝 Code of Conduct

By participating in this project, you agree to maintain a respectful and inclusive environment for all contributors.

## 🚀 Getting Started

### Prerequisites

- PHP 8.1 or higher
- MySQL 5.7 or higher
- Composer
- Git

### Setting Up Development Environment

1. **Fork and Clone**
   ```bash
   git clone https://github.com/yourusername/renaltales.git
   cd renaltales
   ```

2. **Install Dependencies**
   ```bash
   composer install
   ```

3. **Set Up Environment**
   ```bash
   cp .env.example .env
   # Edit .env with your local database credentials
   ```

4. **Database Setup**
   - Create database: `renaltales`
   - Import: `database/setup_database.sql`

## 🧑‍💻 Coding Standards

This project strictly follows **PSR-12** coding standards. All code must comply with these standards before being merged.

### PSR Standards

- **PSR-1**: Basic Coding Standard
- **PSR-12**: Extended Coding Style

### Key Rules

#### 1. **File Structure**
- Use `<?php` opening tag only
- Files MUST end with a newline
- Use Unix line endings (LF)
- No trailing whitespace

#### 2. **Namespaces and Classes**
```php
<?php

declare(strict_types=1);

namespace RenalTales\Controllers;

use RenalTales\Core\BaseController;
use RenalTales\Models\UserModel;

class UserController extends BaseController
{
    // Class content
}
```

#### 3. **Method and Function Naming**
- Use camelCase for method names
- Use descriptive names

```php
public function getUserById(int $id): ?User
{
    // Method implementation
}
```

#### 4. **Constants**
- Use UPPER_CASE with underscores

```php
const DEFAULT_LANGUAGE = 'en';
const MAX_LOGIN_ATTEMPTS = 3;
```

#### 5. **Properties**
- Use camelCase
- Declare visibility explicitly

```php
class User
{
    private string $firstName;
    protected int $id;
    public bool $isActive;
}
```

#### 6. **Arrays**
- Use short array syntax `[]`
- Proper indentation for multi-line arrays

```php
$config = [
    'database' => [
        'host' => 'localhost',
        'port' => 3306,
    ],
    'cache' => [
        'driver' => 'redis',
        'ttl' => 3600,
    ],
];
```

#### 7. **Control Structures**
- Space after control structure keywords
- Braces on new lines

```php
if ($condition) {
    // Code here
} elseif ($anotherCondition) {
    // Code here
} else {
    // Code here
}

foreach ($items as $item) {
    // Process item
}
```

#### 8. **Type Declarations**
- Use strict typing: `declare(strict_types=1);`
- Use type hints for parameters and return types

```php
declare(strict_types=1);

public function calculateAge(DateTime $birthDate): int
{
    return $birthDate->diff(new DateTime())->y;
}
```

### Code Quality Tools

#### Running Code Style Checks

```bash
# Check for coding standard violations
composer phpcs

# Automatically fix violations
composer phpcbf
```

#### Static Analysis

```bash
# Run PHPStan for static analysis
composer phpstan
```

#### Code Coverage

```bash
# Generate code coverage report
composer test-coverage
```

## 🔄 Development Workflow

### Branch Naming

- `feature/feature-name` - New features
- `bugfix/bug-description` - Bug fixes
- `hotfix/critical-fix` - Critical fixes
- `docs/documentation-update` - Documentation updates

### Commit Messages

Follow conventional commit format:

```
type(scope): description

[optional body]

[optional footer]
```

Examples:
```
feat(auth): add two-factor authentication
fix(database): resolve connection timeout issue
docs(readme): update installation instructions
```

### Development Process

1. **Create Feature Branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. **Make Changes**
   - Write code following PSR-12 standards
   - Add tests for new functionality
   - Update documentation

3. **Test Your Changes**
   ```bash
   composer test
   composer phpcs
   composer phpstan
   ```

4. **Commit Changes**
   ```bash
   git add .
   git commit -m "feat(module): add new feature"
   ```

5. **Push and Create Pull Request**
   ```bash
   git push origin feature/your-feature-name
   ```

## 🧪 Testing

### Writing Tests

- Place tests in the `tests/` directory
- Follow the same namespace structure as source code
- Use descriptive test method names

```php
<?php

namespace RenalTales\Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use RenalTales\Controllers\UserController;

class UserControllerTest extends TestCase
{
    public function testCanCreateUser(): void
    {
        // Test implementation
    }
    
    public function testCannotCreateUserWithInvalidEmail(): void
    {
        // Test implementation
    }
}
```

### Running Tests

```bash
# Run all tests
composer test

# Run specific test file
vendor/bin/phpunit tests/Unit/Controllers/UserControllerTest.php

# Run with coverage
composer test-coverage
```

## 📚 Documentation

### Code Documentation

- Use PHPDoc blocks for classes, methods, and properties
- Include parameter and return type descriptions
- Add examples where helpful

```php
/**
 * Calculate user's age based on birth date
 * 
 * @param DateTime $birthDate The user's birth date
 * @return int The calculated age in years
 * 
 * @throws InvalidArgumentException If birth date is in the future
 */
public function calculateAge(DateTime $birthDate): int
{
    if ($birthDate > new DateTime()) {
        throw new InvalidArgumentException('Birth date cannot be in the future');
    }
    
    return $birthDate->diff(new DateTime())->y;
}
```

### Documentation Updates

- Update relevant documentation when making changes
- Keep README.md current
- Add/update API documentation for new endpoints

## 🚀 Submitting Changes

### Pre-submission Checklist

- [ ] Code follows PSR-12 standards (`composer phpcs`)
- [ ] All tests pass (`composer test`)
- [ ] Static analysis passes (`composer phpstan`)
- [ ] Documentation is updated
- [ ] Commit messages follow conventional format
- [ ] Feature is properly tested

### Pull Request Process

1. **Create Pull Request**
   - Use descriptive title
   - Reference related issues
   - Include testing instructions

2. **Code Review**
   - Address reviewer feedback
   - Update code as needed
   - Ensure CI checks pass

3. **Merge Requirements**
   - All CI checks must pass
   - At least one approval from maintainer
   - No merge conflicts

### Pull Request Template

```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
- [ ] Unit tests added/updated
- [ ] Integration tests added/updated
- [ ] Manual testing completed

## Checklist
- [ ] Code follows PSR-12 standards
- [ ] All tests pass
- [ ] Documentation updated
- [ ] No breaking changes (or documented)
```

## 🏗️ Architecture Guidelines

### MVC Pattern

Follow the established MVC pattern:

- **Models**: Handle data access and business logic
- **Views**: Handle presentation and HTML generation
- **Controllers**: Handle request processing and application flow

### File Organization

```
src/
├── Controllers/     # Request handlers
├── Models/         # Data access layer
├── Views/          # Presentation layer
├── Core/           # Core framework components
└── Contracts/      # Interfaces
```

### Dependency Injection

Use dependency injection for better testability:

```php
class UserController
{
    public function __construct(
        private UserModel $userModel,
        private Logger $logger
    ) {}
}
```

## 🔒 Security Guidelines

### Input Validation

- Validate all user input
- Use prepared statements for database queries
- Sanitize output to prevent XSS

### Security Best Practices

- Never commit sensitive data
- Use environment variables for configuration
- Implement proper authentication and authorization
- Follow OWASP guidelines

## 📧 Getting Help

- Check existing documentation in `docs/`
- Review similar implementations in codebase
- Create an issue for questions or clarifications

## 🙏 Recognition

Contributors will be recognized in:
- Project README
- Release notes
- Contributors page (if applicable)

---

Thank you for contributing to Renal Tales! Your contributions help make this project better for everyone.
