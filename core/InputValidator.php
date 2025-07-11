<?php

/**
 * InputValidator - Comprehensive input validation and sanitization
 * 
 * Provides validation rules, sanitization methods, and security checks for user input
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

class InputValidator {
    
    private $rules = [];
    private $errors = [];
    private $sanitizedData = [];
    
    /**
     * Validation rules
     */
    private $validationRules = [
        'required' => 'validateRequired',
        'email' => 'validateEmail',
        'url' => 'validateUrl',
        'numeric' => 'validateNumeric',
        'integer' => 'validateInteger',
        'float' => 'validateFloat',
        'alpha' => 'validateAlpha',
        'alphanum' => 'validateAlphaNumeric',
        'min' => 'validateMin',
        'max' => 'validateMax',
        'length' => 'validateLength',
        'minlength' => 'validateMinLength',
        'maxlength' => 'validateMaxLength',
        'pattern' => 'validatePattern',
        'in' => 'validateIn',
        'not_in' => 'validateNotIn',
        'confirmed' => 'validateConfirmed',
        'unique' => 'validateUnique',
        'exists' => 'validateExists',
        'date' => 'validateDate',
        'before' => 'validateBefore',
        'after' => 'validateAfter',
        'ip' => 'validateIP',
        'json' => 'validateJson',
        'file' => 'validateFile',
        'image' => 'validateImage',
        'mimes' => 'validateMimes',
        'max_file_size' => 'validateMaxFileSize',
        'no_sql_injection' => 'validateNoSQLInjection',
        'no_xss' => 'validateNoXSS',
        'safe_html' => 'validateSafeHTML',
        'slug' => 'validateSlug',
        'username' => 'validateUsername',
        'password' => 'validatePassword',
        'phone' => 'validatePhone',
        'postal_code' => 'validatePostalCode'
    ];
    
    /**
     * Set validation rules
     * 
     * @param array $rules
     */
    public function setRules(array $rules) {
        $this->rules = $rules;
    }
    
    /**
     * Add validation rule
     * 
     * @param string $field
     * @param string|array $rule
     */
    public function addRule($field, $rule) {
        if (!isset($this->rules[$field])) {
            $this->rules[$field] = [];
        }
        
        if (is_string($rule)) {
            $this->rules[$field][] = $rule;
        } elseif (is_array($rule)) {
            $this->rules[$field] = array_merge($this->rules[$field], $rule);
        }
    }
    
    /**
     * Validate input data
     * 
     * @param array $data
     * @return bool
     */
    public function validate(array $data) {
        $this->errors = [];
        $this->sanitizedData = [];
        
        foreach ($this->rules as $field => $rules) {
            $value = $data[$field] ?? null;
            $this->validateField($field, $value, $rules, $data);
        }
        
        return empty($this->errors);
    }
    
    /**
     * Validate single field
     * 
     * @param string $field
     * @param mixed $value
     * @param array $rules
     * @param array $allData
     */
    private function validateField($field, $value, array $rules, array $allData) {
        // First sanitize the value
        $sanitizedValue = $this->sanitizeValue($value);
        $this->sanitizedData[$field] = $sanitizedValue;
        
        foreach ($rules as $rule) {
            $ruleName = $rule;
            $ruleParams = [];
            
            // Parse rule parameters
            if (strpos($rule, ':') !== false) {
                list($ruleName, $paramString) = explode(':', $rule, 2);
                $ruleParams = explode(',', $paramString);
            }
            
            // Skip validation if field is empty and not required
            if (empty($sanitizedValue) && $ruleName !== 'required') {
                continue;
            }
            
            // Execute validation rule
            if (isset($this->validationRules[$ruleName])) {
                $method = $this->validationRules[$ruleName];
                $result = $this->$method($field, $sanitizedValue, $ruleParams, $allData);
                
                if (!$result) {
                    $this->addError($field, $ruleName, $ruleParams);
                }
            }
        }
    }
    
    /**
     * Sanitize value
     * 
     * @param mixed $value
     * @return mixed
     */
    private function sanitizeValue($value) {
        if (is_string($value)) {
            // Basic sanitization
            $value = trim($value);
            $value = stripslashes($value);
            return $value;
        }
        
        if (is_array($value)) {
            return array_map([$this, 'sanitizeValue'], $value);
        }
        
        return $value;
    }
    
    /**
     * Add validation error
     * 
     * @param string $field
     * @param string $rule
     * @param array $params
     */
    private function addError($field, $rule, $params = []) {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        
        $this->errors[$field][] = $this->getErrorMessage($field, $rule, $params);
    }
    
    /**
     * Get error message
     * 
     * @param string $field
     * @param string $rule
     * @param array $params
     * @return string
     */
    private function getErrorMessage($field, $rule, $params = []) {
        $messages = [
            'required' => "The {$field} field is required.",
            'email' => "The {$field} field must be a valid email address.",
            'url' => "The {$field} field must be a valid URL.",
            'numeric' => "The {$field} field must be numeric.",
            'integer' => "The {$field} field must be an integer.",
            'float' => "The {$field} field must be a float.",
            'alpha' => "The {$field} field must contain only alphabetic characters.",
            'alphanum' => "The {$field} field must contain only alphanumeric characters.",
            'min' => "The {$field} field must be at least {$params[0]}.",
            'max' => "The {$field} field must not exceed {$params[0]}.",
            'length' => "The {$field} field must be exactly {$params[0]} characters long.",
            'minlength' => "The {$field} field must be at least {$params[0]} characters long.",
            'maxlength' => "The {$field} field must not exceed {$params[0]} characters.",
            'pattern' => "The {$field} field format is invalid.",
            'in' => "The {$field} field must be one of: " . implode(', ', $params),
            'not_in' => "The {$field} field must not be one of: " . implode(', ', $params),
            'confirmed' => "The {$field} field confirmation does not match.",
            'unique' => "The {$field} field must be unique.",
            'exists' => "The {$field} field does not exist.",
            'date' => "The {$field} field must be a valid date.",
            'before' => "The {$field} field must be before {$params[0]}.",
            'after' => "The {$field} field must be after {$params[0]}.",
            'ip' => "The {$field} field must be a valid IP address.",
            'json' => "The {$field} field must be valid JSON.",
            'file' => "The {$field} field must be a valid file.",
            'image' => "The {$field} field must be a valid image.",
            'mimes' => "The {$field} field must be a file of type: " . implode(', ', $params),
            'max_file_size' => "The {$field} field must not exceed {$params[0]} bytes.",
            'no_sql_injection' => "The {$field} field contains potentially dangerous content.",
            'no_xss' => "The {$field} field contains potentially dangerous content.",
            'safe_html' => "The {$field} field contains unsafe HTML.",
            'slug' => "The {$field} field must be a valid slug.",
            'username' => "The {$field} field must be a valid username.",
            'password' => "The {$field} field must meet password requirements.",
            'phone' => "The {$field} field must be a valid phone number.",
            'postal_code' => "The {$field} field must be a valid postal code."
        ];
        
        return $messages[$rule] ?? "The {$field} field is invalid.";
    }
    
    /**
     * Get validation errors
     * 
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Get sanitized data
     * 
     * @return array
     */
    public function getSanitizedData() {
        return $this->sanitizedData;
    }
    
    /**
     * Get first error for field
     * 
     * @param string $field
     * @return string|null
     */
    public function getFirstError($field) {
        return $this->errors[$field][0] ?? null;
    }
    
    /**
     * Check if field has errors
     * 
     * @param string $field
     * @return bool
     */
    public function hasError($field) {
        return isset($this->errors[$field]) && !empty($this->errors[$field]);
    }
    
    // Validation methods
    
    private function validateRequired($field, $value, $params, $allData) {
        return !empty($value) || $value === '0' || $value === 0;
    }
    
    private function validateEmail($field, $value, $params, $allData) {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    private function validateUrl($field, $value, $params, $allData) {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }
    
    private function validateNumeric($field, $value, $params, $allData) {
        return is_numeric($value);
    }
    
    private function validateInteger($field, $value, $params, $allData) {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }
    
    private function validateFloat($field, $value, $params, $allData) {
        return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
    }
    
    private function validateAlpha($field, $value, $params, $allData) {
        return preg_match('/^[a-zA-Z]+$/', $value);
    }
    
    private function validateAlphaNumeric($field, $value, $params, $allData) {
        return preg_match('/^[a-zA-Z0-9]+$/', $value);
    }
    
    private function validateMin($field, $value, $params, $allData) {
        if (is_numeric($value)) {
            return $value >= $params[0];
        }
        return strlen($value) >= $params[0];
    }
    
    private function validateMax($field, $value, $params, $allData) {
        if (is_numeric($value)) {
            return $value <= $params[0];
        }
        return strlen($value) <= $params[0];
    }
    
    private function validateLength($field, $value, $params, $allData) {
        return strlen($value) === (int)$params[0];
    }
    
    private function validateMinLength($field, $value, $params, $allData) {
        return strlen($value) >= (int)$params[0];
    }
    
    private function validateMaxLength($field, $value, $params, $allData) {
        return strlen($value) <= (int)$params[0];
    }
    
    private function validatePattern($field, $value, $params, $allData) {
        return preg_match($params[0], $value);
    }
    
    private function validateIn($field, $value, $params, $allData) {
        return in_array($value, $params);
    }
    
    private function validateNotIn($field, $value, $params, $allData) {
        return !in_array($value, $params);
    }
    
    private function validateConfirmed($field, $value, $params, $allData) {
        $confirmField = $field . '_confirmation';
        return isset($allData[$confirmField]) && $value === $allData[$confirmField];
    }
    
    private function validateUnique($field, $value, $params, $allData) {
        // This would typically check database for uniqueness
        // For now, just return true
        return true;
    }
    
    private function validateExists($field, $value, $params, $allData) {
        // This would typically check database for existence
        // For now, just return true
        return true;
    }
    
    private function validateDate($field, $value, $params, $allData) {
        return strtotime($value) !== false;
    }
    
    private function validateBefore($field, $value, $params, $allData) {
        return strtotime($value) < strtotime($params[0]);
    }
    
    private function validateAfter($field, $value, $params, $allData) {
        return strtotime($value) > strtotime($params[0]);
    }
    
    private function validateIP($field, $value, $params, $allData) {
        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    }
    
    private function validateJson($field, $value, $params, $allData) {
        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }
    
    private function validateFile($field, $value, $params, $allData) {
        return isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK;
    }
    
    private function validateImage($field, $value, $params, $allData) {
        if (!$this->validateFile($field, $value, $params, $allData)) {
            return false;
        }
        
        $imageInfo = getimagesize($_FILES[$field]['tmp_name']);
        return $imageInfo !== false;
    }
    
    private function validateMimes($field, $value, $params, $allData) {
        if (!isset($_FILES[$field])) {
            return false;
        }
        
        $fileMime = $_FILES[$field]['type'];
        return in_array($fileMime, $params);
    }
    
    private function validateMaxFileSize($field, $value, $params, $allData) {
        if (!isset($_FILES[$field])) {
            return false;
        }
        
        return $_FILES[$field]['size'] <= $params[0];
    }
    
    private function validateNoSQLInjection($field, $value, $params, $allData) {
        $sqlPatterns = [
            '/(\bSELECT\b|\bINSERT\b|\bUPDATE\b|\bDELETE\b|\bDROP\b|\bCREATE\b|\bALTER\b)/i',
            '/(\bUNION\b|\bJOIN\b)/i',
            '/(\bWHERE\b|\bHAVING\b|\bGROUP BY\b|\bORDER BY\b)/i',
            '/(\bEXEC\b|\bEXECUTE\b)/i',
            '/(\bSP_\b|\bXP_\b)/i',
            '/(\b--\b|\/\*|\*\/)/i',
            '/(\bOR\b|\bAND\b)\s*\d+\s*=\s*\d+/i',
            '/(\bOR\b|\bAND\b)\s*[\'"].*[\'"].*=/i'
        ];
        
        foreach ($sqlPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return false;
            }
        }
        
        return true;
    }
    
    private function validateNoXSS($field, $value, $params, $allData) {
        $xssPatterns = [
            '/<script[^>]*>.*?<\/script>/is',
            '/<iframe[^>]*>.*?<\/iframe>/is',
            '/<object[^>]*>.*?<\/object>/is',
            '/<embed[^>]*>.*?<\/embed>/is',
            '/<form[^>]*>.*?<\/form>/is',
            '/javascript:/i',
            '/vbscript:/i',
            '/on\w+\s*=/i',
            '/expression\s*\(/i',
            '/eval\s*\(/i',
            '/document\.cookie/i',
            '/document\.write/i'
        ];
        
        foreach ($xssPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return false;
            }
        }
        
        return true;
    }
    
    private function validateSafeHTML($field, $value, $params, $allData) {
        $allowedTags = $params[0] ?? '<p><br><strong><em><u><ol><ul><li><a><h1><h2><h3><h4><h5><h6>';
        $stripped = strip_tags($value, $allowedTags);
        
        // Check if stripping changed the content significantly
        return strlen($stripped) >= strlen($value) * 0.9;
    }
    
    private function validateSlug($field, $value, $params, $allData) {
        return preg_match('/^[a-z0-9-]+$/', $value);
    }
    
    private function validateUsername($field, $value, $params, $allData) {
        // Username: 3-20 characters, alphanumeric, underscores, hyphens
        return preg_match('/^[a-zA-Z0-9_-]{3,20}$/', $value);
    }
    
    private function validatePassword($field, $value, $params, $allData) {
        // Password: at least 8 characters, one uppercase, one lowercase, one digit
        return strlen($value) >= 8 &&
               preg_match('/[A-Z]/', $value) &&
               preg_match('/[a-z]/', $value) &&
               preg_match('/[0-9]/', $value);
    }
    
    private function validatePhone($field, $value, $params, $allData) {
        // Basic phone number validation
        return preg_match('/^[\+]?[0-9\s\-\(\)]{10,20}$/', $value);
    }
    
    private function validatePostalCode($field, $value, $params, $allData) {
        // Basic postal code validation (adjust for specific countries)
        return preg_match('/^[A-Z0-9\s\-]{3,10}$/i', $value);
    }
    
    /**
     * Sanitize HTML content
     * 
     * @param string $html
     * @param array $allowedTags
     * @return string
     */
    public function sanitizeHTML($html, $allowedTags = []) {
        if (empty($allowedTags)) {
            $allowedTags = ['p', 'br', 'strong', 'em', 'u', 'ol', 'ul', 'li', 'a', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
        }
        
        $html = strip_tags($html, '<' . implode('><', $allowedTags) . '>');
        
        // Remove dangerous attributes
        $html = preg_replace('/(<[^>]+)\s+(on\w+|javascript:|vbscript:|data:)/i', '$1', $html);
        
        // Remove script and style tags completely
        $html = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $html);
        $html = preg_replace('/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/mi', '', $html);
        
        return $html;
    }
    
    /**
     * Sanitize for database storage
     * 
     * @param string $value
     * @return string
     */
    public function sanitizeForDatabase($value) {
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    /**
     * Sanitize for output
     * 
     * @param string $value
     * @return string
     */
    public function sanitizeForOutput($value) {
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    /**
     * Clean filename
     * 
     * @param string $filename
     * @return string
     */
    public function cleanFilename($filename) {
        // Remove directory traversal attempts
        $filename = basename($filename);
        
        // Remove dangerous characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        
        // Limit length
        if (strlen($filename) > 100) {
            $filename = substr($filename, 0, 100);
        }
        
        return $filename;
    }
}
