# Login System Documentation

## Overview

The RenalTales application now includes a complete login system with user authentication, session management, and security features.

## Features

### ✅ Implemented Features
- **User Authentication**: Login with email and password
- **Session Management**: Secure session handling with CSRF protection
- **Password Security**: Bcrypt password hashing
- **Remember Me**: Optional persistent login sessions
- **Security Logging**: Failed login attempts and security events
- **Rate Limiting**: Protection against brute force attacks
- **User Interface**: Responsive login form with error handling
- **Database Integration**: User data storage and retrieval
- **Role-Based Access**: User roles and permissions
- **Language Support**: Multi-language login interface

### 🔒 Security Features
- CSRF token validation
- SQL injection protection
- XSS prevention
- Password strength requirements
- Account lockout after failed attempts
- IP-based rate limiting
- Secure session configuration
- Remember me token encryption

## Usage

### For End Users

1. **Login**: Navigate to `/?action=login` to access the login form
2. **Credentials**: Enter your email and password
3. **Remember Me**: Check the box to stay logged in
4. **Logout**: Click the logout link when authenticated

### Test Credentials
- **Email**: test@example.com
- **Password**: password123

### For Developers

#### Login Controller
```php
$loginController = new LoginController($languageModel, $sessionManager);

// Show login form
$loginController->showLoginForm();

// Process login
$loginController->processLogin();

// Handle logout
$loginController->logout();
```

#### Authentication Manager
```php
$authManager = new AuthenticationManager($sessionManager);

// Authenticate user
$result = $authManager->authenticate($email, $password, $ipAddress);

// Check if authenticated
if ($authManager->isAuthenticated()) {
    // User is logged in
}

// Get current user
$currentUser = $authManager->getCurrentUser();
```

## Database Schema

### Users Table
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    role ENUM('user','verified_user','translator','moderator','admin'),
    two_factor_secret VARCHAR(255),
    two_factor_enabled BOOLEAN DEFAULT FALSE,
    language_preference CHAR(2),
    email_verified_at DATETIME,
    remember_token VARCHAR(100),
    last_login_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Remember Tokens Table
```sql
CREATE TABLE remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

## Files Structure

```
controllers/
├── LoginController.php          # Login form and authentication logic
├── ApplicationController.php    # Main controller with login routing

core/
├── AuthenticationManager.php    # Authentication and security
├── SessionManager.php          # Session management
├── Database.php                # Database connection

views/
├── LoginView.php               # Login form template
├── ApplicationView.php         # Main application view

models/
├── User.php                    # User data model

public/assets/css/
├── style.css                   # Login page styling
```

## Configuration

### Environment Variables
```php
define('APP_DIR', __DIR__);
define('DEFAULT_LANGUAGE', 'sk');
define('LANGUAGE_PATH', APP_DIR . '/resources/lang/');
define('APP_TITLE', 'Renal Tales');
define('DEBUG_MODE', true);
```

### Database Configuration
```php
// In core/Database.php
$this->host = 'localhost';
$this->database = 'renaltales';
$this->username = 'root';
$this->password = '';
```

## API Endpoints

- `GET /?action=login` - Show login form
- `POST /?action=login` - Process login form
- `GET /?action=logout` - Logout user

## Security Best Practices

1. **Passwords**: Use strong passwords with minimum 8 characters
2. **HTTPS**: Always use HTTPS in production
3. **Sessions**: Configure secure session settings
4. **Rate Limiting**: Monitor failed login attempts
5. **Input Validation**: Validate all user inputs
6. **CSRF Protection**: Include CSRF tokens in all forms
7. **SQL Injection**: Use prepared statements
8. **XSS Prevention**: Escape output data

## Testing

### Manual Testing
1. Open `http://localhost:8000/?action=login`
2. Enter test credentials: test@example.com / password123
3. Verify successful login and redirect
4. Test logout functionality
5. Test invalid credentials

### Automated Testing
```bash
# Run the test scripts
php test_simple_login.php
php create_test_user.php
php run_migration.php
```

## Troubleshooting

### Common Issues

1. **Database Connection**: Check MySQL server is running
2. **Session Errors**: Ensure session directory is writable
3. **CSRF Token**: Check if sessions are working properly
4. **Password Hash**: Verify bcrypt hashing is working
5. **Missing Tables**: Run database migrations

### Debug Mode
Set `DEBUG_MODE = true` to see detailed error messages.

## Future Enhancements

### Planned Features
- [ ] Email verification
- [ ] Password reset functionality
- [ ] Two-factor authentication (2FA)
- [ ] OAuth integration (Google, Facebook)
- [ ] User registration
- [ ] Profile management
- [ ] Admin user management
- [ ] Password history
- [ ] Login history
- [ ] Device management

## Support

For technical support or questions about the login system, please refer to the main application documentation or contact the development team.

---

**Version**: 2025.v1.0  
**Author**: Ľubomír Polaščín  
**Last Updated**: January 2025
