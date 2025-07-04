
# **Renal Tales** 

#### PHP Web Application by Lumpe Paskuden von Lumpenen

##### With the help of Warp App and its AI Agents | [warp.dev](https://www.warp.dev/)

---

***[Link to Warp App Session Page](https://app.warp.dev/session/055c81cd-d792-48ce-bf23-7db560161d89?pwd=ebe96265-f2a5-476d-8bdd-6f808cb38333)***

---


###### Prompt for Warp AI Agents:

Create from scratch in the directory 'G:\"Môj disk"\www\renaltales' the PHP web app with the name of "renaltales". Do not use any framework. Prepare optimal directory structure. The app should have comprehensive multilingual support for all known world language support with language detection and easy switch using national flags; and safe users management support. Apply the best software security standards. The app should display renal tales and stories from and for the community of people with various kidney disorders, on dialyis, and before or after kidnney transplant or those with no chance to get it. Stories should be of various categories. Some should be available without registration, some only for certain users. Useres should have the possibility to add new stories, that would be then revised by users with the right to do it. Translators should be able to translate stories into supported languages for stories: 'en', 'sk', 'cs', 'de', 'pl', 'hu', 'uk', 'ru', 'it', 'nl', 'fr', 'es', 'pt', 'ro', 'bg', 'sl', 'hr', 'sr', 'mk', 'sq', 'el', 'da', 'no', 'sv', 'fi', 'is', 'et', 'lv', 'lt', 'tr', 'eo', 'ja', 'zh', 'ko', 'ar', 'hi', 'th', 'vi', 'id', 'ms', 'tl', 'sw', 'am', 'yo', 'zu'. All other world languages are detected but not supported for the stories.

##### Directory Structure

1. Root Directory: renaltales/
•  public/: Contains files accessible to the public. It's the document root.
◦  index.php: Main entry point.
◦  assets/: CSS, JavaScript, images (including national flags).
•  app/: Application logic.
◦  Controllers/: Handle requests, call models and render views.
◦  Models/: Database interaction and business logic.
◦  Views/: Templates for displaying content.
•  config/: Configuration files.
◦  config.php: Configuration settings, database credentials.
◦  languages.php: Language support configuration.
•  storage/: User uploads and cache.
•  logs/: Log files.
•  vendor/: Composer dependencies (optional if any).
•  tests/: Unit and integration tests.
•  scripts/: Utility scripts (e.g., for language detection).

##### Key Features

1. Multilingual Support
•  Store language settings in config/languages.php.
•  Detect user language using browser settings.
•  Provide an option to switch languages using a dropdown or national flags.
2. User Management
•  Register, login, and manage user roles and permissions.
•  Secure password handling with hashing (e.g., bcrypt).
•  Sessions and tokens for authentication.
3. Content Management
•  Categories for stories.
•  Story submission and approval workflow.
•  Access control for stories based on user roles.
4. Security Standards
•  Input validation and sanitization.
•  Rate limiting to prevent abuse.
•  Secure session handling.

##### Implementation Steps

1. Setup the Project:
•  Create the directories as outlined above in G:\Môj disk\www\renaltales.
2. Create Core Files:
•  Implement basic routing in public/index.php.
•  Set up basic config in config/config.php.
•  Create initial controllers, models, and views.
3. Multilingual Setup:
•  Write a script for language detection in scripts/lang_detect.php.
•  Implement interfaces for language selection in views.
4. User Management System:
•  Use PHP sessions for state management.
•  Develop user registration and login functionality in the Controllers/UserController.php.
5. Security and Standards:
•  Sanitize all inputs to avoid XSS and SQL Injection.
•  Use HTTPS if applicable.
•  Log access and errors.

---

### The initial setup for the PHP web application "RenalTales"

*What has been done:*

•  Directory Structure: Created an organized directory with essential folders and core files.
•  Core Files: Implemented key components like the Database, Router, Controller, Security, and Language classes.
•  Controllers and Views: Created a basic HomeController and its associated view for the homepage.
•  Database Schema: Provided a SQL migration script to create the necessary database tables.

Further development, such as adding more controllers, finishing the views, and implementing the user management and story functionalities.

---

