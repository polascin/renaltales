<?php
declare(strict_types=1);

namespace RenalTales\Validation;

use RenalTales\Database\DatabaseConnection;
use PDO;

/**
 * Centralized Validation System
 * 
 * Provides comprehensive validation logic with security-focused rules,
 * sanitization, and customizable error messages.
 */
class Validator
{
    private PDO $db;
    private array $errors = [];
    private array $customMessages = [];
    private array $data = [];

    // Password complexity requirements
    public const PASSWORD_MIN_LENGTH = 12;
    public const PASSWORD_REQUIRE_UPPERCASE = true;
    public const PASSWORD_REQUIRE_LOWERCASE = true;
    public const PASSWORD_REQUIRE_NUMBERS = true;
    public const PASSWORD_REQUIRE_SYMBOLS = true;

    // Input length limits
    public const USERNAME_MIN_LENGTH = 3;
    public const USERNAME_MAX_LENGTH = 50;
    public const EMAIL_MAX_LENGTH = 254;
    public const NAME_MAX_LENGTH = 100;
    public const TEXTAREA_MAX_LENGTH = 10000;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? DatabaseConnection::getInstance();
    }

    /**
     * Validate data against rules
     */
    public function validate(array $data, array $rules, array $messages = []): bool
    {
        $this->data = $data;
        $this->errors = [];
        $this->customMessages = $messages;

        foreach ($rules as $field => $fieldRules) {
            $this->validateField($field, $fieldRules);
        }

        return empty($this->errors);
    }

    /**
     * Get validation errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get sanitized data
     */
    public function getSanitized(): array
    {
        $sanitized = [];
        foreach ($this->data as $key => $value) {
            $sanitized[$key] = $this->sanitizeInput($value);
        }
        return $sanitized;
    }

    /**
     * Validate individual field
     */
    private function validateField(string $field, string $rules): void
    {
        $value = $this->data[$field] ?? '';
        $rulesList = explode('|', $rules);

        foreach ($rulesList as $rule) {
            if (!$this->applyRule($field, $value, $rule)) {
                break; // Stop on first error for this field
            }
        }
    }

    /**
     * Apply validation rule
     */
    private function applyRule(string $field, $value, string $rule): bool
    {
        $ruleParts = explode(':', $rule, 2);
        $ruleName = $ruleParts[0];
        $parameter = $ruleParts[1] ?? null;

        switch ($ruleName) {
            case 'required':
                return $this->validateRequired($field, $value);
            
            case 'required_if':
                return $this->validateRequiredIf($field, $value, $parameter);
            
            case 'email':
                return $this->validateEmail($field, $value);
            
            case 'unique':
                return $this->validateUnique($field, $value, $parameter);
            
            case 'exists':
                return $this->validateExists($field, $value, $parameter);
            
            case 'min':
                return $this->validateMin($field, $value, (int)$parameter);
            
            case 'max':
                return $this->validateMax($field, $value, (int)$parameter);
            
            case 'between':
                return $this->validateBetween($field, $value, $parameter);
            
            case 'alpha':
                return $this->validateAlpha($field, $value);
            
            case 'alpha_num':
                return $this->validateAlphaNum($field, $value);
            
            case 'alpha_dash':
                return $this->validateAlphaDash($field, $value);
            
            case 'numeric':
                return $this->validateNumeric($field, $value);
            
            case 'integer':
                return $this->validateInteger($field, $value);
            
            case 'url':
                return $this->validateUrl($field, $value);
            
            case 'ip':
                return $this->validateIp($field, $value);
            
            case 'regex':
                return $this->validateRegex($field, $value, $parameter);
            
            case 'password':
                return $this->validatePassword($field, $value);
            
            case 'confirmed':
                return $this->validateConfirmed($field, $value);
            
            case 'username':
                return $this->validateUsername($field, $value);
            
            case 'safe_html':
                return $this->validateSafeHtml($field, $value);
            
            case 'file_extension':
                return $this->validateFileExtension($field, $value, $parameter);
            
            case 'json':
                return $this->validateJson($field, $value);
            
            case 'date':
                return $this->validateDate($field, $value);
            
            case 'date_format':
                return $this->validateDateFormat($field, $value, $parameter);
            
            case 'before':
                return $this->validateBefore($field, $value, $parameter);
            
            case 'after':
                return $this->validateAfter($field, $value, $parameter);
            
            case 'in':
                return $this->validateIn($field, $value, $parameter);
            
            case 'not_in':
                return $this->validateNotIn($field, $value, $parameter);
            
            case 'boolean':
                return $this->validateBoolean($field, $value);
            
            case 'array':
                return $this->validateArray($field, $value);
            
            default:
                return true; // Unknown rule, skip
        }
    }

    /**
     * Validation rule implementations
     */
    private function validateRequired(string $field, $value): bool
    {
        if ($this->isEmpty($value)) {
            $this->addError($field, 'required', 'The %s field is required.');
            return false;
        }
        return true;
    }

    private function validateRequiredIf(string $field, $value, string $parameter): bool
    {
        [$otherField, $otherValue] = explode(',', $parameter, 2);
        $otherFieldValue = $this->data[$otherField] ?? '';
        
        if ($otherFieldValue == $otherValue && $this->isEmpty($value)) {
            $this->addError($field, 'required_if', 'The %s field is required when %s is %s.');
            return false;
        }
        return true;
    }

    private function validateEmail(string $field, $value): bool
    {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, 'email', 'The %s field must be a valid email address.');
            return false;
        }
        return true;
    }

    private function validateUnique(string $field, $value, string $parameter): bool
    {
        if (empty($value)) return true;

        [$table, $column, $except] = array_pad(explode(',', $parameter), 3, null);
        
        $query = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ?";
        $params = [$value];
        
        if ($except) {
            $query .= " AND id != ?";
            $params[] = $except;
        }
        
        $result = $this->db->query($query, $params)->fetch();
        
        if ($result['count'] > 0) {
            $this->addError($field, 'unique', 'The %s has already been taken.');
            return false;
        }
        return true;
    }

    private function validateExists(string $field, $value, string $parameter): bool
    {
        if (empty($value)) return true;

        [$table, $column] = explode(',', $parameter);
        
        $query = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ?";
        $result = $this->db->query($query, [$value])->fetch();
        
        if ($result['count'] == 0) {
            $this->addError($field, 'exists', 'The selected %s is invalid.');
            return false;
        }
        return true;
    }

    private function validateMin(string $field, $value, int $min): bool
    {
        if (!empty($value) && mb_strlen($value) < $min) {
            $this->addError($field, 'min', 'The %s field must be at least %d characters.');
            return false;
        }
        return true;
    }

    private function validateMax(string $field, $value, int $max): bool
    {
        if (!empty($value) && mb_strlen($value) > $max) {
            $this->addError($field, 'max', 'The %s field must not exceed %d characters.');
            return false;
        }
        return true;
    }

    private function validateBetween(string $field, $value, string $parameter): bool
    {
        [$min, $max] = explode(',', $parameter);
        $length = mb_strlen($value);
        
        if (!empty($value) && ($length < (int)$min || $length > (int)$max)) {
            $this->addError($field, 'between', 'The %s field must be between %d and %d characters.');
            return false;
        }
        return true;
    }

    private function validateAlpha(string $field, $value): bool
    {
        if (!empty($value) && !ctype_alpha($value)) {
            $this->addError($field, 'alpha', 'The %s field may only contain letters.');
            return false;
        }
        return true;
    }

    private function validateAlphaNum(string $field, $value): bool
    {
        if (!empty($value) && !ctype_alnum($value)) {
            $this->addError($field, 'alpha_num', 'The %s field may only contain letters and numbers.');
            return false;
        }
        return true;
    }

    private function validateAlphaDash(string $field, $value): bool
    {
        if (!empty($value) && !preg_match('/^[a-zA-Z0-9_-]+$/', $value)) {
            $this->addError($field, 'alpha_dash', 'The %s field may only contain letters, numbers, dashes, and underscores.');
            return false;
        }
        return true;
    }

    private function validateNumeric(string $field, $value): bool
    {
        if (!empty($value) && !is_numeric($value)) {
            $this->addError($field, 'numeric', 'The %s field must be a number.');
            return false;
        }
        return true;
    }

    private function validateInteger(string $field, $value): bool
    {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
            $this->addError($field, 'integer', 'The %s field must be an integer.');
            return false;
        }
        return true;
    }

    private function validateUrl(string $field, $value): bool
    {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addError($field, 'url', 'The %s field must be a valid URL.');
            return false;
        }
        return true;
    }

    private function validateIp(string $field, $value): bool
    {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_IP)) {
            $this->addError($field, 'ip', 'The %s field must be a valid IP address.');
            return false;
        }
        return true;
    }

    private function validateRegex(string $field, $value, string $pattern): bool
    {
        if (!empty($value) && !preg_match($pattern, $value)) {
            $this->addError($field, 'regex', 'The %s field format is invalid.');
            return false;
        }
        return true;
    }

    private function validatePassword(string $field, $value): bool
    {
        if (empty($value)) return true;

        $errors = [];
        
        if (strlen($value) < self::PASSWORD_MIN_LENGTH) {
            $errors[] = 'at least ' . self::PASSWORD_MIN_LENGTH . ' characters';
        }
        
        if (self::PASSWORD_REQUIRE_UPPERCASE && !preg_match('/[A-Z]/', $value)) {
            $errors[] = 'at least one uppercase letter';
        }
        
        if (self::PASSWORD_REQUIRE_LOWERCASE && !preg_match('/[a-z]/', $value)) {
            $errors[] = 'at least one lowercase letter';
        }
        
        if (self::PASSWORD_REQUIRE_NUMBERS && !preg_match('/\d/', $value)) {
            $errors[] = 'at least one number';
        }
        
        if (self::PASSWORD_REQUIRE_SYMBOLS && !preg_match('/[^a-zA-Z\d]/', $value)) {
            $errors[] = 'at least one special character';
        }
        
        // Check for common weak patterns
        if (preg_match('/(.)\1{2,}/', $value)) {
            $errors[] = 'no repeated characters (more than 2)';
        }
        
        if (preg_match('/^(123|abc|qwe|password|admin)/i', $value)) {
            $errors[] = 'no common patterns';
        }
        
        if (!empty($errors)) {
            $message = 'The %s must contain ' . implode(', ', $errors) . '.';
            $this->addError($field, 'password', $message);
            return false;
        }
        
        return true;
    }

    private function validateConfirmed(string $field, $value): bool
    {
        $confirmationField = $field . '_confirmation';
        $confirmationValue = $this->data[$confirmationField] ?? '';
        
        if ($value !== $confirmationValue) {
            $this->addError($field, 'confirmed', 'The %s confirmation does not match.');
            return false;
        }
        return true;
    }

    private function validateUsername(string $field, $value): bool
    {
        if (empty($value)) return true;

        // Length check
        if (strlen($value) < self::USERNAME_MIN_LENGTH) {
            $this->addError($field, 'username', 'The %s must be at least ' . self::USERNAME_MIN_LENGTH . ' characters.');
            return false;
        }
        
        if (strlen($value) > self::USERNAME_MAX_LENGTH) {
            $this->addError($field, 'username', 'The %s must not exceed ' . self::USERNAME_MAX_LENGTH . ' characters.');
            return false;
        }
        
        // Character validation
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $value)) {
            $this->addError($field, 'username', 'The %s may only contain letters, numbers, and underscores.');
            return false;
        }
        
        // Reserved usernames
        $reserved = ['admin', 'administrator', 'root', 'system', 'test', 'guest', 'user', 'moderator', 'api', 'www', 'ftp', 'mail'];
        if (in_array(strtolower($value), $reserved)) {
            $this->addError($field, 'username', 'The %s is not available.');
            return false;
        }
        
        return true;
    }

    private function validateSafeHtml(string $field, $value): bool
    {
        if (empty($value)) return true;

        // Check for dangerous tags and scripts
        $dangerous = ['<script', '<iframe', '<object', '<embed', '<form', 'javascript:', 'vbscript:', 'onload=', 'onerror='];
        
        foreach ($dangerous as $pattern) {
            if (stripos($value, $pattern) !== false) {
                $this->addError($field, 'safe_html', 'The %s contains unsafe content.');
                return false;
            }
        }
        
        return true;
    }

    private function validateFileExtension(string $field, $value, string $parameter): bool
    {
        if (empty($value)) return true;

        $allowedExtensions = explode(',', $parameter);
        $extension = strtolower(pathinfo($value, PATHINFO_EXTENSION));
        
        if (!in_array($extension, $allowedExtensions)) {
            $this->addError($field, 'file_extension', 'The %s must be a file of type: ' . implode(', ', $allowedExtensions) . '.');
            return false;
        }
        
        return true;
    }

    private function validateJson(string $field, $value): bool
    {
        if (!empty($value)) {
            json_decode($value);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->addError($field, 'json', 'The %s field must be valid JSON.');
                return false;
            }
        }
        return true;
    }

    private function validateDate(string $field, $value): bool
    {
        if (!empty($value) && !strtotime($value)) {
            $this->addError($field, 'date', 'The %s field must be a valid date.');
            return false;
        }
        return true;
    }

    private function validateDateFormat(string $field, $value, string $format): bool
    {
        if (!empty($value)) {
            $date = \DateTime::createFromFormat($format, $value);
            if (!$date || $date->format($format) !== $value) {
                $this->addError($field, 'date_format', 'The %s field must match the format ' . $format . '.');
                return false;
            }
        }
        return true;
    }

    private function validateBefore(string $field, $value, string $parameter): bool
    {
        if (!empty($value)) {
            $valueTime = strtotime($value);
            $beforeTime = strtotime($parameter);
            
            if ($valueTime >= $beforeTime) {
                $this->addError($field, 'before', 'The %s field must be before ' . $parameter . '.');
                return false;
            }
        }
        return true;
    }

    private function validateAfter(string $field, $value, string $parameter): bool
    {
        if (!empty($value)) {
            $valueTime = strtotime($value);
            $afterTime = strtotime($parameter);
            
            if ($valueTime <= $afterTime) {
                $this->addError($field, 'after', 'The %s field must be after ' . $parameter . '.');
                return false;
            }
        }
        return true;
    }

    private function validateIn(string $field, $value, string $parameter): bool
    {
        if (!empty($value)) {
            $allowedValues = explode(',', $parameter);
            if (!in_array($value, $allowedValues)) {
                $this->addError($field, 'in', 'The selected %s is invalid.');
                return false;
            }
        }
        return true;
    }

    private function validateNotIn(string $field, $value, string $parameter): bool
    {
        if (!empty($value)) {
            $forbiddenValues = explode(',', $parameter);
            if (in_array($value, $forbiddenValues)) {
                $this->addError($field, 'not_in', 'The selected %s is invalid.');
                return false;
            }
        }
        return true;
    }

    private function validateBoolean(string $field, $value): bool
    {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)) {
            $this->addError($field, 'boolean', 'The %s field must be true or false.');
            return false;
        }
        return true;
    }

    private function validateArray(string $field, $value): bool
    {
        if (!empty($value) && !is_array($value)) {
            $this->addError($field, 'array', 'The %s field must be an array.');
            return false;
        }
        return true;
    }

    /**
     * Check if value is empty
     */
    private function isEmpty($value): bool
    {
        if (is_null($value)) return true;
        if (is_string($value) && trim($value) === '') return true;
        if (is_array($value) && count($value) === 0) return true;
        return false;
    }

    /**
     * Add validation error
     */
    private function addError(string $field, string $rule, string $message): void
    {
        $fieldName = str_replace('_', ' ', $field);
        
        // Use custom message if provided
        $customKey = "{$field}.{$rule}";
        if (isset($this->customMessages[$customKey])) {
            $message = $this->customMessages[$customKey];
        } elseif (isset($this->customMessages[$field])) {
            $message = $this->customMessages[$field];
        }
        
        $this->errors[$field] = sprintf($message, $fieldName);
    }

    /**
     * Sanitize input for safe output
     */
    public function sanitizeInput($input): string|array
    {
        if (is_array($input)) {
            return array_map([$this, 'sanitizeInput'], $input);
        }
        
        if (!is_string($input)) {
            return $input;
        }
        
        // Remove null bytes
        $input = str_replace(chr(0), '', $input);
        
        // Trim whitespace
        $input = trim($input);
        
        // Convert special characters to HTML entities
        return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Sanitize for database storage (no HTML encoding)
     */
    public function sanitizeForStorage($input): string|array
    {
        if (is_array($input)) {
            return array_map([$this, 'sanitizeForStorage'], $input);
        }
        
        if (!is_string($input)) {
            return $input;
        }
        
        // Remove null bytes
        $input = str_replace(chr(0), '', $input);
        
        // Trim whitespace
        return trim($input);
    }

    /**
     * Validate file upload
     */
    public function validateFileUpload(array $file, array $options = []): bool
    {
        $maxSize = $options['max_size'] ?? 10485760; // 10MB
        $allowedTypes = $options['allowed_types'] ?? ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
        $allowedMimes = $options['allowed_mimes'] ?? [
            'image/jpeg', 'image/png', 'image/gif', 'application/pdf'
        ];

        // Check if file was uploaded
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            $this->errors['file'] = 'No file was uploaded.';
            return false;
        }

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors['file'] = 'File upload error: ' . $this->getUploadErrorMessage($file['error']);
            return false;
        }

        // Check file size
        if ($file['size'] > $maxSize) {
            $this->errors['file'] = 'File size exceeds maximum allowed size of ' . $this->formatBytes($maxSize) . '.';
            return false;
        }

        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedTypes)) {
            $this->errors['file'] = 'File type not allowed. Allowed types: ' . implode(', ', $allowedTypes);
            return false;
        }

        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedMimes)) {
            $this->errors['file'] = 'Invalid file type detected.';
            return false;
        }

        // Additional security checks for images
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            $imageInfo = getimagesize($file['tmp_name']);
            if (!$imageInfo) {
                $this->errors['file'] = 'Invalid image file.';
                return false;
            }
        }

        return true;
    }

    /**
     * Get upload error message
     */
    private function getUploadErrorMessage(int $error): string
    {
        switch ($error) {
            case UPLOAD_ERR_INI_SIZE:
                return 'File exceeds upload_max_filesize directive';
            case UPLOAD_ERR_FORM_SIZE:
                return 'File exceeds MAX_FILE_SIZE directive';
            case UPLOAD_ERR_PARTIAL:
                return 'File was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing a temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'A PHP extension stopped the file upload';
            default:
                return 'Unknown upload error';
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
