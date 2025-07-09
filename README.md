# **Renal Tales** 

> *Web Application by* ***Lumpe Paskuden von Lumpenen*** *aka* ***Walter Kyo*** *or* ***Walter Csoelle Kyo***
> *Author:* Lubomir Polascin (Ľubomír Polaščín)

> *Technology used:* **PHP**, **HTML**, **CSS**, **JavaScript**, **MySQL**

---

## Project Overview

Renal Tales is a multilingual web application for sharing kidney disorder stories, built with PHP using the MVC (Model-View-Controller) architectural pattern.

## Enhanced Directory Structure

The project follows modern PHP application best practices with a well-organized directory structure:

### 📁 Project Structure

```
renaltales/
├── 📁 config/              # Configuration files
│   ├── app.php             # Main application settings
│   ├── database.php        # Database configuration  
│   └── .prettierrc         # Code formatting rules
├── 📁 controllers/         # MVC Controllers
│   ├── BaseController.php
│   └── ApplicationController.php
├── 📁 core/               # Core framework components
│   ├── Application.php
│   ├── Database.php
│   ├── EmailVerificationManager.php
│   ├── LanguageDetector.php
│   ├── Logger.php
│   ├── PasswordResetManager.php
│   └── SessionManager.php
├── 📁 database/           # Database scripts and migrations
│   ├── schema/            # Database schema files
│   ├── setup_database.sql
│   └── logging_system_setup.sql
├── 📁 docs/               # Project documentation
│   ├── README.md
│   ├── MVC_STRUCTURE.md
│   ├── refaktoring.md
│   └── database_README.md
├── 📁 models/             # MVC Models
│   ├── BaseModel.php
│   └── LanguageModel.php
├── 📁 public/             # Public web-accessible files
│   ├── index.php          # Application entry point
│   └── assets/            # Static assets
│       ├── css/           # Stylesheets
│       ├── js/            # JavaScript files
│       ├── images/        # Images and illustrations
│       ├── flags/         # Country flag assets
│       └── templates/     # HTML templates
├── 📁 resources/          # Application resources
│   ├── lang/             # Language files (136 languages)
│   └── views/            # View templates (future)
├── 📁 storage/           # Application storage (not in git)
│   ├── cache/            # Cache files
│   ├── logs/             # Log files
│   ├── sessions/         # Session storage
│   ├── temp/             # Temporary files
│   └── uploads/          # User uploaded files
├── 📁 scripts/           # Utility scripts
│   ├── cleanup.php       # Cleanup script
│   └── README.md         # Scripts documentation
├── 📁 tests/             # Unit and feature tests
│   ├── Feature/
│   ├── Unit/
│   └── database/         # Database tests
│       └── test_setup.php # Database setup test
├── 📁 views/             # MVC Views
│   ├── BaseView.php
│   ├── ApplicationView.php
│   └── ErrorView.php
├── .env.example          # Environment configuration template
├── .gitignore            # Git ignore rules
└── .htaccess             # Apache configuration
```

## 🚀 Quick Start

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Laragon (recommended for local development)

### Installation

1. **Clone/Download** the project to your web server directory
   ```bash
   # For Laragon users
   Place in: C:\laragon\www\renaltales
   # Document root: G:"Môj disk"\www\renaltales
   ```

2. **Set up Environment**
   ```bash
   cp .env.example .env
   # Edit .env file with your database credentials
   ```

3. **Configure Database**
   - Create database: `renaltales`
   - Import: `database/setup_database.sql`
   - Run schema files from `database/schema/`

4. **Set Permissions** (if on Linux/macOS)
   ```bash
   chmod -R 755 storage/
   chmod -R 755 resources/lang/
   ```

5. **Access Application**
   - URL: `http://localhost/renaltales`
   - The application will auto-detect language and display the interface

## 🔧 Configuration

### Main Configuration (`config/app.php`)
- Application settings
- Language configuration  
- Security settings
- Logging configuration
- Cache settings

### Database Configuration (`config/database.php`)
- Database connections
- Connection options
- Environment-based settings

### Environment Variables (`.env`)
```env
# Database
DB_HOST=localhost
DB_DATABASE=renaltales
DB_USERNAME=root
DB_PASSWORD=

# Application  
APP_ENV=development
APP_DEBUG=true
```

## 🌍 Multilingual Support

The application supports **136 languages** with automatic detection:
- Language files: `resources/lang/`
- Default language: Slovak (sk)
- Fallback language: English (en)
- Detection: Browser headers + user preference

## 🏗️ Architecture

### MVC Pattern
- **Models**: Data access and business logic
- **Views**: Presentation layer and HTML generation  
- **Controllers**: Request handling and application flow

### Key Features
- ✅ CSRF Protection
- ✅ Session Management
- ✅ Input Sanitization
- ✅ Error Handling
- ✅ Logging System
- ✅ Multi-language Support
- ✅ Responsive Design

## 📝 Development

### Adding New Features
1. **Controller**: Extend `BaseController`
2. **Model**: Extend `BaseModel`
3. **View**: Extend `BaseView`

### Directory Best Practices
- **config/**: All configuration files
- **storage/**: Never commit storage contents
- **docs/**: Keep documentation updated
- **public/**: Only publicly accessible files
- **resources/**: Application resources and assets

## 🔒 Security

- Environment-based configuration
- CSRF token protection
- Input validation and sanitization
- Secure session handling
- SQL injection prevention
- XSS protection

## 📊 Logging

Logs are stored in `storage/logs/`:
- `application.log`: General application logs
- `error.log`: Error and exception logs

## 🤝 Contributing

1. Follow the established directory structure
2. Use the MVC pattern for new features
3. Add proper documentation
4. Test thoroughly before submitting

## 📖 Documentation

Detailed documentation available in `docs/`:
- `MVC_STRUCTURE.md`: Architecture details
- `database_README.md`: Database documentation
- `refaktoring.md`: Refactoring process (Slovak)

## 📞 Support

For issues or questions regarding the directory structure or application:
- Check documentation in `docs/`
- Review configuration in `config/`
- Check logs in `storage/logs/`

## 🚀 Deployment Preparation

### Documentation
- **Deployment Guide**: `docs/DEPLOYMENT.md` - Complete deployment instructions
- **User Guide**: `docs/USER_GUIDE.md` - End-user documentation
- **Admin Guide**: `docs/ADMIN_GUIDE.md` - Administrator documentation

### Environment Configurations
- **Development**: `config/environments/development.php`
- **Production**: `config/environments/production.php`
- **Environment Templates**: `.env.example` with all required variables

### Backup & Recovery
- **Backup System**: `scripts/backup/backup-system.php`
- **Automated Backups**: Database, files, and configuration backups
- **Recovery Procedures**: Complete restoration workflows

### Monitoring & Health Checks
- **Health Endpoint**: `public/health.php` - Application health monitoring
- **System Monitoring**: Database, cache, storage, and performance checks
- **Alerting**: Built-in monitoring and alerting system

### Production Optimization
- **Asset Optimization**: `scripts/optimize/production-optimizer.php`
- **Caching**: Redis integration for production environments
- **Minification**: CSS/JS minification and compression
- **Database Optimization**: Query optimization and cleanup
- **Image Optimization**: Automatic image compression

### Security Features
- **Environment-specific configurations**
- **HTTPS enforcement in production**
- **Security headers and CSP**
- **Rate limiting and brute force protection**
- **Database security hardening**

### Performance Features
- **OPcache optimization**
- **Redis caching**
- **Asset bundling and minification**
- **Database query optimization**
- **Image optimization**
- **Gzip compression**

---

*Last updated: January 2025*
*Directory structure optimized for maintainability and scalability*
*Deployment-ready with comprehensive documentation and tooling*
