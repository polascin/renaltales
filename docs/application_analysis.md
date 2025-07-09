# Renal Tales Application Analysis

## Database Schema

### Users Table
- **Fields**: `id`, `username`, `email`, `password_hash`, `email_verified`, etc.
- **Constraints**: Unique `username`, `email`.
- **Indexes**: Indexed by `email`, `username`.

### Password Resets Table
- **Fields**: `id`, `user_id`, `email`, `token`, etc.
- **Constraints**: Unique `token`.
- **Indexes**: Indexed by `token`, `email`.

### Email Verifications Table
- **Fields**: `id`, `user_id`, `email`, `token`, etc.
- **Constraints**: Unique `token`.
- **Indexes**: Indexed by `token`, `email`.

### Logging Tables
- **User Registration Logs**: Captures registration events and IP.
- **Login Logs**: Captures login events and IP.
...

## Multilanguage Support
- **Languages**: Supports 136 languages, default Slovak.
- **Detection**: Based on browser headers + user preference.

## User Management & Security
- **Security Features**: CSRF, Input Sanitization, Error Handling.
- **Tokens**: Password, email verification.
- **Logs**: User actions, failed attempts, security audits.

## Content Management
- **MVC Pattern**: Separated into Models, Views, and Controllers.
- **Assets**: Managed through `public` and `resources` directories.

---

*This report serves to provide a comprehensive overview of the current state of the Renal Tales application, aiming to identify core components, existing security measures, and multilingual support.*
