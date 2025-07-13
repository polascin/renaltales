# Remaining PSR Compliance Issues

## Files that need manual fixes:

### 1. src/Core/LegacyMultilingualSupport.php
**Issues:**
- Multiple classes in single file (violates PSR-4)
- Classes: `LanguageDetector`, `LanguageModel`, `LegacyMultilingualTrait`, `MultilingualMigrationNotice`

**Action Required:**
```bash
# Split into separate files:
# - src/Core/LanguageDetector.php
# - src/Core/LanguageModel.php  
# - src/Core/LegacyMultilingualTrait.php
# - src/Core/MultilingualMigrationNotice.php
```

### 2. src/Core/Contracts/LanguageInterface.php
**Issues:**
- Missing proper namespace declaration
- Current: `Core\Contracts\LanguageInterface`
- Should be: `RenalTales\Core\Contracts\LanguageInterface`

**Action Required:**
```php
<?php

namespace RenalTales\Core\Contracts;

interface LanguageInterface
{
    // ... existing methods
}
```

### 3. src/Core/Logger.php
**Issues:**
- Missing type hints for method parameters
- Long lines (>120 characters)

**Action Required:**
```php
public function logRegistration(
    ?int $userId,
    string $username,
    string $email,
    string $status,
    ?string $failureReason = null,
    ?array $additionalData = null
): bool {
    // ... method body
}
```

### 4. Files using require_once instead of autoloading:
- `api/stories.php`
- `scripts/` directory files
- `public/admin/` files
- `database/` files

**Action Required:**
Replace manual includes with proper autoloading:
```php
// Instead of:
require_once 'path/to/file.php';

// Use:
// (classes will be autoloaded when needed)
```

## Commands to run after fixes:

```bash
# Regenerate autoloader
composer dump-autoload

# Check PSR-12 compliance
php vendor/bin/phpcs --standard=PSR12 src

# Auto-fix what can be fixed
php vendor/bin/phpcbf --standard=PSR12 src

# Run tests to ensure nothing is broken
php vendor/bin/phpunit
```

## Additional recommendations:

1. **Add strict types** to all PHP files:
```php
<?php declare(strict_types=1);
```

2. **Use proper return types**:
```php
public function getName(): string
{
    return $this->name;
}
```

3. **Add proper PHPDoc blocks**:
```php
/**
 * Log user registration events
 * 
 * @param int|null $userId The user ID
 * @param string $username The username
 * @param string $email The email address
 * @param string $status The registration status
 * @param string|null $failureReason The failure reason if any
 * @param array|null $additionalData Additional data to log
 * @return bool True if logging was successful
 */
public function logRegistration(
    ?int $userId,
    string $username,
    string $email,
    string $status,
    ?string $failureReason = null,
    ?array $additionalData = null
): bool {
    // ... method implementation
}
```

4. **Consider adding a PSR-12 check to CI/CD**:
```yaml
# .github/workflows/php.yml
- name: Check PSR-12 compliance
  run: php vendor/bin/phpcs --standard=PSR12 src
```
