# Security Implementation Guide

This document outlines the comprehensive security features implemented in the Renal Tales application.

## Overview

The application implements multiple layers of security to protect against common web vulnerabilities and attacks:

- **CSRF Protection**: Prevents Cross-Site Request Forgery attacks
- **XSS Prevention**: Protects against Cross-Site Scripting attacks
- **SQL Injection Protection**: Uses prepared statements and query validation
- **Security Headers**: Implements security headers like CSP, X-Frame-Options, etc.
- **Rate Limiting**: Prevents abuse and DoS attacks on API endpoints
- **Input Validation**: Comprehensive validation and sanitization of user input
- **Secure File Upload**: Safe file upload handling with multiple security checks

## Security Components

### 1. SecurityManager (`core/SecurityManager.php`)

The main security component that handles:

- CSRF token generation and validation
- XSS prevention through input sanitization
- Security headers configuration
- Content Security Policy (CSP) management

**Usage:**

```php
$securityManager = new SecurityManager($sessionManager);

// Generate CSRF token field for forms
echo $securityManager->getCSRFTokenField();

// Validate CSRF token
if ($securityManager->validateCSRFToken($token)) {
    // Process request
}

// Sanitize input
$cleanInput = $securityManager->sanitizeInput($userInput);
```

### 2. RateLimitManager (`core/RateLimitManager.php`)

Implements rate limiting to prevent abuse:

- Per-endpoint rate limits
- Burst protection
- IP-based and user-based limiting
- Automatic blocking of repeat offenders

**Usage:**

```php
$rateLimitManager = new RateLimitManager();
$clientId = $rateLimitManager->getClientIdentifier();
$result = $rateLimitManager->checkRateLimit($clientId, 'api/stories');

if (!$result['allowed']) {
    // Rate limit exceeded
    http_response_code(429);
    exit;
}
```

### 3. InputValidator (`core/InputValidator.php`)

Comprehensive input validation and sanitization:

- Multiple validation rules
- SQL injection prevention
- XSS attack prevention
- Custom validation rules

**Usage:**

```php
$validator = new InputValidator();
$validator->setRules([
    'title' => ['required', 'maxlength:255', 'no_xss'],
    'content' => ['required', 'safe_html'],
    'email' => ['required', 'email']
]);

if ($validator->validate($data)) {
    $cleanData = $validator->getSanitizedData();
} else {
    $errors = $validator->getErrors();
}
```

### 4. FileUploadManager (`core/FileUploadManager.php`)

Secure file upload handling:

- File type validation
- MIME type checking
- Virus scanning (optional)
- Executable file detection
- Image processing and thumbnail generation

**Usage:**

```php
$uploadManager = new FileUploadManager();
$result = $uploadManager->uploadFile($_FILES['file']);

if ($result['success']) {
    $fileInfo = $result['file'];
} else {
    $errors = $result['errors'];
}
```

### 5. Enhanced Database Security (`core/Database.php`)

SQL injection protection:

- Prepared statements enforcement
- Query validation
- Parameter sanitization
- Dangerous pattern detection

## Security Features

### CSRF Protection

All forms include CSRF tokens:

```html
<form method="post">
    <?= $securityManager->getCSRFTokenField() ?>
    <!-- form fields -->
</form>
```

### XSS Prevention

Input is automatically sanitized:

```php
// Sanitize for output
$safeOutput = $securityManager->sanitizeInput($userInput);

// Sanitize HTML content
$safeHtml = $validator->sanitizeHTML($htmlContent);
```

### Security Headers

Automatically set on all responses:

- Content Security Policy (CSP)
- X-Frame-Options: DENY
- X-Content-Type-Options: nosniff
- X-XSS-Protection: 1; mode=block
- Referrer-Policy: strict-origin-when-cross-origin
- Strict-Transport-Security (HTTPS only)

### Rate Limiting

Configured per endpoint:

```php
'endpoints' => [
    'api/stories' => ['limit' => 60, 'window' => 3600],
    'api/upload' => ['limit' => 20, 'window' => 3600],
    'login' => ['limit' => 5, 'window' => 900],
]
```

### Input Validation Rules

Available validation rules:

- `required`: Field must not be empty
- `email`: Valid email address
- `url`: Valid URL
- `numeric`: Numeric value
- `integer`: Integer value
- `alpha`: Alphabetic characters only
- `alphanum`: Alphanumeric characters only
- `min:X`: Minimum value/length
- `max:X`: Maximum value/length
- `minlength:X`: Minimum string length
- `maxlength:X`: Maximum string length
- `pattern:REGEX`: Match regular expression
- `in:a,b,c`: Value in list
- `not_in:a,b,c`: Value not in list
- `confirmed`: Confirmation field matches
- `date`: Valid date
- `before:DATE`: Date before specified date
- `after:DATE`: Date after specified date
- `ip`: Valid IP address
- `json`: Valid JSON
- `file`: Valid file upload
- `image`: Valid image file
- `mimes:jpg,png`: File MIME types
- `max_file_size:X`: Maximum file size
- `no_sql_injection`: No SQL injection patterns
- `no_xss`: No XSS patterns
- `safe_html`: Safe HTML content
- `username`: Valid username format
- `password`: Strong password requirements
- `phone`: Valid phone number
- `postal_code`: Valid postal code

### File Upload Security

Security checks performed:

1. File size validation
2. Extension validation
3. MIME type validation
4. Double extension detection
5. Executable file detection
6. Embedded script detection
7. PHP code detection
8. Virus scanning (optional)

## Configuration

Security settings are configured in `config/security.php`:

```php
return [
    'security_manager' => [
        'csrf_expire_time' => 3600,
        'xss_protection' => true,
        // ...
    ],
    'rate_limiting' => [
        'default_limit' => 100,
        'default_window' => 3600,
        // ...
    ],
    // ... other settings
];
```

## Security Logs

Security events are logged to:

- `storage/logs/security_events.log`
- `storage/logs/rate_limit_violations.log`
- `storage/logs/file_upload_security.log`

## Best Practices

### For Developers

1. **Always use prepared statements** for database queries
2. **Validate and sanitize all input** before processing
3. **Include CSRF tokens** in all forms
4. **Use rate limiting** for API endpoints
5. **Implement proper error handling** without exposing sensitive information
6. **Keep security components updated**
7. **Regularly review security logs**

### For Administrators

1. **Enable HTTPS** in production
2. **Configure virus scanning** for file uploads
3. **Set up log monitoring** and alerts
4. **Regularly update allowed file types** and MIME types
5. **Review rate limit settings** based on usage patterns
6. **Implement backup and recovery procedures**
7. **Conduct regular security audits**

### For Users

1. **Use strong passwords** with mixed case, numbers, and symbols
2. **Enable two-factor authentication** when available
3. **Be cautious with file uploads** and only upload trusted files
4. **Report suspicious activity** to administrators
5. **Keep browsers updated** for latest security features

## Security Testing

### Manual Testing

1. **CSRF Testing**: Try submitting forms without CSRF tokens
2. **XSS Testing**: Try injecting scripts in input fields
3. **SQL Injection Testing**: Try SQL injection patterns in inputs
4. **File Upload Testing**: Try uploading malicious files
5. **Rate Limit Testing**: Make rapid requests to test rate limiting

### Automated Testing

Consider using security testing tools:

- **OWASP ZAP** for vulnerability scanning
- **Nikto** for web server testing
- **SQLMap** for SQL injection testing
- **Burp Suite** for comprehensive security testing

## Incident Response

In case of security incidents:

1. **Immediate Actions**:
   - Block suspicious IP addresses
   - Review security logs
   - Disable affected accounts if necessary

2. **Investigation**:
   - Analyze attack vectors
   - Identify compromised data
   - Document incident details

3. **Recovery**:
   - Patch vulnerabilities
   - Restore from backups if necessary
   - Update security configurations

4. **Post-Incident**:
   - Update security policies
   - Implement additional protections
   - Train users on new threats

## Compliance

This security implementation helps with:

- **GDPR**: Data protection and privacy
- **OWASP Top 10**: Protection against common vulnerabilities
- **PCI DSS**: Payment card data security (if applicable)
- **HIPAA**: Healthcare data protection (if applicable)

## Updates and Maintenance

### Regular Tasks

1. **Update dependencies** regularly
2. **Review and rotate secrets** periodically
3. **Monitor security logs** daily
4. **Test security measures** monthly
5. **Update security configurations** as needed

### Security Patches

1. **Monitor security advisories** for used libraries
2. **Apply security patches** promptly
3. **Test patches** in staging environment first
4. **Update documentation** after changes

## Contact

For security-related questions or to report vulnerabilities:

- Email: <security@renaltales.com>
- Create an issue in the project repository
- Contact the development team directly

---

**Note**: This security implementation is comprehensive but should be regularly reviewed and updated based on emerging threats and best practices. Always keep security components up to date and conduct regular security assessments.
