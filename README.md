# Renal Tales 
### PHP Web Application
---

#### 1. *Directory structure:*

renaltales/
├── config/           # Configuration files
├── public/          # Public web root
├── src/             # Application source code
│   ├── Controller/  # Controllers
│   ├── Model/       # Data models
│   ├── Service/     # Business logic services
│   ├── Security/    # Security-related classes
│   └── Language/    # Language-related classes
├── templates/       # View templates
├── translations/    # Language translation files
├── tests/           # Test files
└── var/            # Variable data (cache, logs)

#### 2. *Core Components:*
•  composer.json: Dependencies and autoloading configuration
•  Application.php: Main application class with routing and initialization
•  Config.php: Configuration management with .env support
•  LanguageManager.php: Comprehensive language management system

### Next steps:
1. Setting up the database schema for stories and users
2. Creating the security components (authentication, authorization)
3. Implementing the story management system
4. Setting up the translation system
5. Creating the frontend templates