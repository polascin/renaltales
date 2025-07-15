# Testing Infrastructure

This document describes the comprehensive testing framework set up for the RenalTales project.

## Testing Stack

### PHPUnit for Unit Testing
- **Version**: 10.x
- **Purpose**: Unit tests, integration tests, and feature tests
- **Configuration**: `phpunit.xml`
- **Coverage**: HTML, Clover, Cobertura, and CRAP4J reports

### Behat for Behavior-Driven Development
- **Version**: 3.x
- **Purpose**: BDD tests written in Gherkin syntax
- **Configuration**: `behat.yml`
- **Contexts**: Web, Database, Language, API, and Multilingual

### Code Quality Tools
- **PHPStan**: Static analysis for bug detection
- **PHP CodeSniffer**: Code style and standards enforcement
- **Slevomat Coding Standard**: Additional coding standards

## Directory Structure

```
tests/
├── bootstrap.php              # Test initialization
├── TestCase.php              # Base test class
├── Unit/                     # Unit tests
├── Integration/              # Integration tests
├── Feature/                  # Feature tests
├── Context/                  # Behat contexts
├── Traits/                   # Testing traits
├── Fixtures/                 # Test fixtures
└── logs/                     # Test logs

features/
├── web/                      # Web-related features
├── api/                      # API-related features
└── multilingual/             # Multilingual features

coverage/                     # Coverage reports
├── html/                     # HTML coverage report
├── clover.xml               # Clover coverage format
└── cobertura.xml            # Cobertura coverage format
```

## Running Tests

### Individual Test Suites

```bash
# Unit tests only
composer test:unit

# Integration tests only
composer test:integration

# Feature tests only
composer test:feature

# With coverage report
composer test:coverage

# Text coverage report
composer test:coverage-text

# Behat BDD tests
composer behat

# Specific Behat suite
composer behat:suite web
```

### All Tests

```bash
# Run all tests with coverage
composer test:all

# Run comprehensive test suite
php scripts/run_all_tests.php
```

### Code Quality

```bash
# Static analysis
composer phpstan

# Code style check
composer phpcs

# Code style fix
composer phpcbf

# All quality checks
composer quality
```

## Test Base Classes and Traits

### TestCase
Base class for all PHPUnit tests providing:
- Application bootstrap
- Database management
- Service container access
- Mock creation utilities
- Custom assertions
- Test data management

### DatabaseTrait
Provides database testing utilities:
- Schema creation/destruction
- Entity management
- Transaction handling
- Custom database assertions

### MockTrait
Provides mocking utilities:
- Service mocks
- Repository mocks
- Method mocking patterns
- Verification helpers

### AssertionTrait
Provides custom assertions:
- Array key assertions
- String content assertions
- Object property assertions
- Validation assertions
- File/directory assertions

## Behat Contexts

### WebContext
- Page navigation
- Form submission
- Content verification
- Response status checking

### DatabaseContext
- Database state management
- Entity creation/deletion
- Data verification
- Schema operations

### LanguageContext
- Language switching
- Translation verification
- Multilingual functionality
- Language preference testing

### ApiContext
- API endpoint testing
- Request/response validation
- JSON verification
- Authentication testing

### MultilingualContext
- Translation management
- Interface language switching
- Fallback language testing
- Preference persistence

## Configuration

### PHPUnit Configuration (`phpunit.xml`)
- Test suites: Unit, Integration, Feature, All
- Code coverage with path coverage
- Multiple report formats
- Test environment variables
- Memory and error reporting settings

### Behat Configuration (`behat.yml`)
- Multiple test suites
- Context configuration
- Formatter settings
- Output paths

## Coverage Reports

### HTML Report
- Interactive coverage report
- Available at: `coverage/html/index.html`
- Shows line, branch, and path coverage

### Clover Report
- XML format for CI/CD integration
- Available at: `coverage/clover.xml`

### Cobertura Report
- XML format for various tools
- Available at: `coverage/cobertura.xml`

## CI/CD Integration

### GitHub Actions
- **CI Workflow**: `.github/workflows/ci.yml`
  - Runs on multiple PHP versions
  - Executes PHPUnit tests with coverage
  - Runs Behat BDD tests
  - Uploads coverage reports

- **Quality Workflow**: `.github/workflows/quality.yml`
  - Runs PHPStan analysis
  - Runs PHP CodeSniffer
  - Performs security checks
  - Generates quality reports

## Best Practices

### Writing Tests
1. Use descriptive test names
2. Follow AAA pattern (Arrange, Act, Assert)
3. Test one thing at a time
4. Use data providers for multiple test cases
5. Mock external dependencies

### Database Testing
1. Use transactions for cleanup
2. Create minimal test data
3. Test database constraints
4. Verify entity relationships

### BDD Testing
1. Write scenarios in plain English
2. Focus on user behavior
3. Use Given/When/Then pattern
4. Keep scenarios independent

### Code Coverage
1. Aim for >90% coverage
2. Focus on critical paths
3. Don't obsess over 100% coverage
4. Review coverage gaps regularly

## Troubleshooting

### Common Issues

1. **Memory Issues**
   - Increase PHP memory limit
   - Use `--stop-on-failure` flag
   - Run smaller test suites

2. **Database Issues**
   - Check database connection
   - Verify test database exists
   - Ensure proper cleanup

3. **Coverage Issues**
   - Install Xdebug or PCOV
   - Check file paths in coverage configuration
   - Verify coverage filters

### Running Tests in Development

```bash
# Quick test run
composer test:unit

# Full test suite
php scripts/run_all_tests.php

# Debug specific test
vendor/bin/phpunit --debug tests/Unit/Core/ApplicationTest.php
```

## Extending the Testing Framework

### Adding New Test Types
1. Create test classes extending `TestCase`
2. Add new test suite to `phpunit.xml`
3. Update composer scripts

### Adding New Behat Contexts
1. Create context class implementing `Context`
2. Add to `behat.yml` configuration
3. Create corresponding feature files

### Adding New Assertions
1. Add methods to `AssertionTrait`
2. Follow naming convention: `assert*`
3. Include helpful error messages

## Performance Considerations

- Use in-memory SQLite for faster tests
- Parallel test execution when possible
- Optimize test data fixtures
- Use appropriate test isolation levels

## Maintenance

- Regularly update testing dependencies
- Review and update test coverage goals
- Refactor tests as code evolves
- Monitor CI/CD pipeline performance
