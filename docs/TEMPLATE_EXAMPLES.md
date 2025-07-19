# RenalTales Template System Examples

## Table of Contents

1. [Basic Page Templates](#basic-page-templates)
2. [Component Examples](#component-examples)  
3. [Form Templates](#form-templates)
4. [Multi-Language Templates](#multi-language-templates)
5. [Dashboard Templates](#dashboard-templates)
6. [Error Pages](#error-pages)
7. [Email Templates](#email-templates)

---

## Basic Page Templates

### Home Page Template
```html
<!-- resources/templates/home.html -->
<!DOCTYPE html>
<html lang="{{currentLanguage}}">
<head>
    <title>{{pageTitle}}</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>
    {{>header}}
    
    <main class="main-content">
        <section class="hero">
            <div class="container">
                <h1 class="hero-title">{{heroTitle}}</h1>
                <p class="hero-description">{{heroDescription}}</p>
                {{#isLoggedIn}}
                    <a href="/dashboard" class="btn btn-primary">{{dashboardText}}</a>
                {{/isLoggedIn}}
                {{^isLoggedIn}}
                    <a href="/register" class="btn btn-primary">{{getStartedText}}</a>
                {{/isLoggedIn}}
            </div>
        </section>
        
        <section class="features">
            <div class="container">
                <h2>{{featuresTitle}}</h2>
                <div class="feature-grid">
                    {{#featureCards}}
                        {{>feature-card}}
                    {{/featureCards}}
                </div>
            </div>
        </section>
    </main>
    
    {{>footer}}
    
    <script src="/assets/js/main.js"></script>
</body>
</html>
```

### About Page Template  
```html
<!-- resources/templates/about.html -->
<!DOCTYPE html>
<html lang="{{currentLanguage}}">
<head>
    <title>{{pageTitle}}</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>
    {{>header}}
    
    <main class="main-content">
        <section class="page-header">
            <div class="container">
                <h1>{{pageTitle}}</h1>
                <p class="lead">{{pageDescription}}</p>
            </div>
        </section>
        
        <section class="about-content">
            <div class="container">
                <div class="content-grid">
                    <div class="main-content">
                        <h2>{{missionTitle}}</h2>
                        <p>{{missionDescription}}</p>
                        
                        <h3>{{valuesTitle}}</h3>
                        <ul class="values-list">
                            {{#values}}
                                <li>
                                    <strong>{{title}}:</strong> {{description}}
                                </li>
                            {{/values}}
                        </ul>
                    </div>
                    
                    <div class="sidebar">
                        {{>stats-widget}}
                        {{>testimonial-widget}}
                    </div>
                </div>
            </div>
        </section>
    </main>
    
    {{>footer}}
</body>
</html>
```

---

## Component Examples

### Header Component
```html
<!-- resources/templates/components/header.html -->
<header class="main-header">
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <a href="/">
                    <img src="/assets/images/logo.png" alt="{{appName}}" />
                    <span class="logo-text">{{appName}}</span>
                </a>
            </div>
            
            <nav class="main-nav">
                {{>navigation}}
            </nav>
            
            <div class="header-actions">
                {{>language-switcher}}
                {{>theme-toggle}}
                
                {{#isLoggedIn}}
                    <div class="user-menu">
                        <button class="user-avatar" data-dropdown="user-menu">
                            <img src="{{userAvatar}}" alt="{{userName}}" />
                        </button>
                        <div class="dropdown-menu" id="user-menu">
                            <a href="/profile">{{profileText}}</a>
                            <a href="/settings">{{settingsText}}</a>
                            <hr>
                            <a href="/logout">{{logoutText}}</a>
                        </div>
                    </div>
                {{/isLoggedIn}}
                {{^isLoggedIn}}
                    <div class="auth-actions">
                        <a href="/login" class="btn btn-outline">{{loginText}}</a>
                        <a href="/register" class="btn btn-primary">{{registerText}}</a>
                    </div>
                {{/isLoggedIn}}
            </div>
        </div>
    </div>
</header>
```

### Navigation Component
```html
<!-- resources/templates/components/navigation.html -->
<ul class="nav-list">
    {{#navigationItems}}
        <li class="nav-item {{#isActive}}active{{/isActive}}">
            <a href="{{url}}" class="nav-link">
                {{#icon}}<i class="icon-{{icon}}"></i>{{/icon}}
                {{title}}
            </a>
            {{#hasSubmenu}}
                <ul class="submenu">
                    {{#submenuItems}}
                        <li><a href="{{url}}">{{title}}</a></li>
                    {{/submenuItems}}
                </ul>
            {{/hasSubmenu}}
        </li>
    {{/navigationItems}}
</ul>
```

### Feature Card Component
```html
<!-- resources/templates/components/feature-card.html -->
<div class="feature-card {{cardClass}}">
    {{#icon}}
        <div class="feature-icon">
            <i class="icon-{{icon}}"></i>
        </div>
    {{/icon}}
    
    <div class="feature-content">
        <h3 class="feature-title">{{title}}</h3>
        <p class="feature-description">{{description}}</p>
        
        {{#link}}
            <a href="{{link}}" class="{{buttonClass}}">
                {{buttonText}}
                <i class="icon-arrow-right"></i>
            </a>
        {{/link}}
    </div>
</div>
```

### Footer Component
```html
<!-- resources/templates/components/footer.html -->
<footer class="main-footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h4>{{appName}}</h4>
                <p>{{footerDescription}}</p>
                <div class="social-links">
                    {{#socialLinks}}
                        <a href="{{url}}" title="{{title}}" target="_blank">
                            <i class="icon-{{icon}}"></i>
                        </a>
                    {{/socialLinks}}
                </div>
            </div>
            
            <div class="footer-section">
                <h5>{{linksTitle}}</h5>
                <ul>
                    {{#footerLinks}}
                        <li><a href="{{url}}">{{title}}</a></li>
                    {{/footerLinks}}
                </ul>
            </div>
            
            <div class="footer-section">
                <h5>{{supportTitle}}</h5>
                <ul>
                    <li><a href="/help">{{helpText}}</a></li>
                    <li><a href="/contact">{{contactText}}</a></li>
                    <li><a href="/privacy">{{privacyText}}</a></li>
                    <li><a href="/terms">{{termsText}}</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h5>{{newsletterTitle}}</h5>
                <p>{{newsletterDescription}}</p>
                <form class="newsletter-form" method="POST" action="/newsletter/subscribe">
                    <input type="email" name="email" placeholder="{{emailPlaceholder}}" required>
                    <button type="submit" class="btn btn-primary">{{subscribeText}}</button>
                </form>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; {{currentYear}} {{appName}}. {{rightsReservedText}}</p>
        </div>
    </div>
</footer>
```

---

## Form Templates

### Contact Form Template
```html
<!-- resources/templates/contact.html -->
<!DOCTYPE html>
<html lang="{{currentLanguage}}">
<head>
    <title>{{pageTitle}}</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>
    {{>header}}
    
    <main class="main-content">
        <div class="container">
            <div class="form-page">
                <h1>{{pageTitle}}</h1>
                <p>{{pageDescription}}</p>
                
                {{#hasSuccess}}
                    <div class="alert alert-success">
                        <i class="icon-check"></i>
                        {{successMessage}}
                    </div>
                {{/hasSuccess}}
                
                {{#hasErrors}}
                    <div class="alert alert-error">
                        <h4>{{errorTitle}}</h4>
                        <ul>
                            {{#errors}}
                                <li>{{message}}</li>
                            {{/errors}}
                        </ul>
                    </div>
                {{/hasErrors}}
                
                <form class="contact-form" method="POST" action="{{formAction}}">
                    <input type="hidden" name="csrf_token" value="{{csrfToken}}">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">{{firstNameLabel}} *</label>
                            <input type="text" id="first_name" name="first_name" 
                                   value="{{formData.first_name}}" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name">{{lastNameLabel}} *</label>
                            <input type="text" id="last_name" name="last_name" 
                                   value="{{formData.last_name}}" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">{{emailLabel}} *</label>
                        <input type="email" id="email" name="email" 
                               value="{{formData.email}}" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">{{subjectLabel}} *</label>
                        <select id="subject" name="subject" required>
                            <option value="">{{selectSubjectText}}</option>
                            {{#subjects}}
                                <option value="{{value}}" {{#selected}}selected{{/selected}}>
                                    {{label}}
                                </option>
                            {{/subjects}}
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">{{messageLabel}} *</label>
                        <textarea id="message" name="message" rows="5" required>{{formData.message}}</textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            {{submitText}}
                        </button>
                        <a href="/" class="btn btn-outline">{{cancelText}}</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
    
    {{>footer}}
</body>
</html>
```

### User Registration Form
```html
<!-- resources/templates/register.html -->
<!DOCTYPE html>
<html lang="{{currentLanguage}}">
<head>
    <title>{{pageTitle}}</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>
    {{>header}}
    
    <main class="main-content">
        <div class="container">
            <div class="auth-page">
                <div class="auth-form-container">
                    <h1>{{pageTitle}}</h1>
                    <p>{{pageDescription}}</p>
                    
                    {{>form-errors}}
                    
                    <form class="registration-form" method="POST" action="{{formAction}}">
                        <input type="hidden" name="csrf_token" value="{{csrfToken}}">
                        
                        <div class="form-group">
                            <label for="username">{{usernameLabel}} *</label>
                            <input type="text" id="username" name="username" 
                                   value="{{formData.username}}" required 
                                   minlength="3" maxlength="30">
                            <small class="form-hint">{{usernameHint}}</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">{{emailLabel}} *</label>
                            <input type="email" id="email" name="email" 
                                   value="{{formData.email}}" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">{{firstNameLabel}} *</label>
                                <input type="text" id="first_name" name="first_name" 
                                       value="{{formData.first_name}}" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name">{{lastNameLabel}} *</label>
                                <input type="text" id="last_name" name="last_name" 
                                       value="{{formData.last_name}}" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">{{passwordLabel}} *</label>
                            <input type="password" id="password" name="password" required 
                                   minlength="8" class="password-input">
                            <small class="form-hint">{{passwordHint}}</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="password_confirmation">{{confirmPasswordLabel}} *</label>
                            <input type="password" id="password_confirmation" 
                                   name="password_confirmation" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="terms_accepted" 
                                       {{#formData.terms_accepted}}checked{{/formData.terms_accepted}} 
                                       required>
                                {{termsText}} 
                                <a href="/terms" target="_blank">{{termsLinkText}}</a>
                            </label>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-block">
                                {{registerText}}
                            </button>
                        </div>
                    </form>
                    
                    <div class="auth-footer">
                        <p>{{alreadyMemberText}} 
                           <a href="/login">{{loginLinkText}}</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    {{>footer}}
</body>
</html>
```

---

## Multi-Language Templates

### Language Switcher Component
```html
<!-- resources/templates/components/language-switcher.html -->
<div class="language-switcher" data-component="language-switcher">
    <button class="language-toggle" data-toggle="language-menu">
        <i class="icon-globe"></i>
        <span class="current-language">{{currentLanguageName}}</span>
        <i class="icon-chevron-down"></i>
    </button>
    
    <div class="language-menu" data-menu="language-menu">
        <div class="language-menu-header">
            <h4>{{selectLanguageText}}</h4>
        </div>
        
        <div class="language-list">
            {{#supportedLanguages}}
                <a href="/language/switch?lang={{code}}" 
                   class="language-option {{#selected}}active{{/selected}}"
                   data-language="{{code}}">
                    <img src="/assets/images/flags/{{code}}.png" alt="{{name}}" class="flag">
                    <span class="language-name">{{name}}</span>
                    {{#selected}}<i class="icon-check"></i>{{/selected}}
                </a>
            {{/supportedLanguages}}
        </div>
    </div>
</div>
```

### Multilingual Page Header
```html
<!-- resources/templates/components/multilingual-header.html -->
<div class="page-header multilingual">
    <div class="container">
        <div class="header-content">
            <h1>{{pageTitle}}</h1>
            {{#pageDescription}}
                <p class="lead">{{pageDescription}}</p>
            {{/pageDescription}}
            
            {{#availableTranslations}}
                <div class="translation-notice">
                    <i class="icon-info"></i>
                    <span>{{translationAvailableText}}</span>
                    <div class="available-languages">
                        {{#translations}}
                            <a href="{{url}}" class="language-link" hreflang="{{code}}">
                                {{name}}
                            </a>
                        {{/translations}}
                    </div>
                </div>
            {{/availableTranslations}}
        </div>
    </div>
</div>
```

---

## Dashboard Templates

### User Dashboard
```html
<!-- resources/templates/dashboard.html -->
<!DOCTYPE html>
<html lang="{{currentLanguage}}">
<head>
    <title>{{pageTitle}}</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>
<body class="dashboard-page">
    {{>header}}
    
    <div class="dashboard-layout">
        <aside class="dashboard-sidebar">
            {{>dashboard-navigation}}
        </aside>
        
        <main class="dashboard-content">
            <div class="dashboard-header">
                <h1>{{welcomeText}}, {{userName}}!</h1>
                <div class="dashboard-actions">
                    <button class="btn btn-primary" data-action="create-story">
                        <i class="icon-plus"></i>
                        {{createStoryText}}
                    </button>
                </div>
            </div>
            
            <div class="dashboard-widgets">
                <div class="widget-grid">
                    {{>stats-widget}}
                    {{>recent-activity-widget}}
                    {{>quick-actions-widget}}
                </div>
            </div>
            
            <div class="dashboard-sections">
                {{#showRecentStories}}
                    <section class="dashboard-section">
                        <h2>{{recentStoriesTitle}}</h2>
                        <div class="stories-grid">
                            {{#recentStories}}
                                {{>story-card}}
                            {{/recentStories}}
                        </div>
                        <a href="/stories" class="view-all-link">{{viewAllStoriesText}}</a>
                    </section>
                {{/showRecentStories}}
                
                {{#showCommunityUpdates}}
                    <section class="dashboard-section">
                        <h2>{{communityUpdatesTitle}}</h2>
                        <div class="updates-list">
                            {{#communityUpdates}}
                                {{>update-item}}
                            {{/communityUpdates}}
                        </div>
                    </section>
                {{/showCommunityUpdates}}
            </div>
        </main>
    </div>
    
    {{>footer}}
    
    <script src="/assets/js/dashboard.js"></script>
</body>
</html>
```

### Dashboard Statistics Widget
```html
<!-- resources/templates/components/stats-widget.html -->
<div class="widget stats-widget">
    <div class="widget-header">
        <h3>{{statsTitle}}</h3>
        <select class="period-selector" data-action="change-period">
            {{#periods}}
                <option value="{{value}}" {{#selected}}selected{{/selected}}>
                    {{label}}
                </option>
            {{/periods}}
        </select>
    </div>
    
    <div class="widget-content">
        <div class="stats-grid">
            {{#statistics}}
                <div class="stat-item {{cssClass}}">
                    <div class="stat-icon">
                        <i class="icon-{{icon}}"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">{{value}}</div>
                        <div class="stat-label">{{label}}</div>
                        {{#change}}
                            <div class="stat-change {{changeClass}}">
                                <i class="icon-{{changeIcon}}"></i>
                                {{changeText}}
                            </div>
                        {{/change}}
                    </div>
                </div>
            {{/statistics}}
        </div>
    </div>
</div>
```

---

## Error Pages

### 404 Error Page
```html
<!-- resources/templates/errors/404.html -->
<!DOCTYPE html>
<html lang="{{currentLanguage}}">
<head>
    <title>{{errorTitle}} - {{appName}}</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/error.css">
</head>
<body class="error-page">
    {{>header}}
    
    <main class="error-content">
        <div class="container">
            <div class="error-container">
                <div class="error-illustration">
                    <img src="/assets/images/404-illustration.svg" alt="Page not found">
                </div>
                
                <div class="error-details">
                    <h1 class="error-code">404</h1>
                    <h2 class="error-title">{{errorTitle}}</h2>
                    <p class="error-description">{{errorDescription}}</p>
                    
                    <div class="error-actions">
                        <a href="/" class="btn btn-primary">{{homeButtonText}}</a>
                        <button class="btn btn-outline" onclick="history.back()">
                            {{goBackText}}
                        </button>
                    </div>
                    
                    <div class="helpful-links">
                        <h3>{{helpfulLinksTitle}}</h3>
                        <ul>
                            {{#helpfulLinks}}
                                <li><a href="{{url}}">{{title}}</a></li>
                            {{/helpfulLinks}}
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    {{>footer}}
</body>
</html>
```

### 500 Error Page
```html
<!-- resources/templates/errors/500.html -->
<!DOCTYPE html>
<html lang="{{currentLanguage}}">
<head>
    <title>{{errorTitle}} - {{appName}}</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/error.css">
</head>
<body class="error-page">
    {{>header}}
    
    <main class="error-content">
        <div class="container">
            <div class="error-container">
                <div class="error-illustration">
                    <img src="/assets/images/500-illustration.svg" alt="Server error">
                </div>
                
                <div class="error-details">
                    <h1 class="error-code">500</h1>
                    <h2 class="error-title">{{errorTitle}}</h2>
                    <p class="error-description">{{errorDescription}}</p>
                    
                    {{#showErrorDetails}}
                        <div class="error-technical">
                            <details>
                                <summary>{{technicalDetailsTitle}}</summary>
                                <div class="error-stack">
                                    <pre>{{errorStack}}</pre>
                                </div>
                            </details>
                        </div>
                    {{/showErrorDetails}}
                    
                    <div class="error-actions">
                        <button class="btn btn-primary" onclick="location.reload()">
                            {{retryButtonText}}
                        </button>
                        <a href="/" class="btn btn-outline">{{homeButtonText}}</a>
                    </div>
                    
                    <div class="error-support">
                        <p>{{supportText}}</p>
                        <a href="/contact?error_id={{errorId}}" class="support-link">
                            {{contactSupportText}}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    {{>footer}}
</body>
</html>
```

---

## Email Templates

### Welcome Email Template
```html
<!-- resources/templates/emails/welcome.html -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{emailSubject}}</title>
    <style>
        /* Email-safe CSS */
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .button { display: inline-block; padding: 12px 24px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; }
        .footer { text-align: center; margin-top: 40px; color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{logoUrl}}" alt="{{appName}}" width="150">
            <h1>{{welcomeTitle}}</h1>
        </div>
        
        <div class="content">
            <p>{{greetingText}}, {{userName}}!</p>
            
            <p>{{welcomeMessage}}</p>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{activationUrl}}" class="button">
                    {{activateAccountText}}
                </a>
            </div>
            
            <p>{{activationInstructions}}</p>
            
            <div style="background: #f8f9fa; padding: 20px; border-radius: 4px; margin: 20px 0;">
                <h3>{{gettingStartedTitle}}</h3>
                <ul>
                    {{#gettingStartedSteps}}
                        <li>{{step}}</li>
                    {{/gettingStartedSteps}}
                </ul>
            </div>
            
            <p>{{supportText}} 
               <a href="mailto:{{supportEmail}}">{{supportEmail}}</a>
            </p>
        </div>
        
        <div class="footer">
            <p>&copy; {{currentYear}} {{appName}}. {{rightsReservedText}}</p>
            <p>
                <a href="{{unsubscribeUrl}}">{{unsubscribeText}}</a> |
                <a href="{{privacyPolicyUrl}}">{{privacyPolicyText}}</a>
            </p>
        </div>
    </div>
</body>
</html>
```

### Password Reset Email
```html
<!-- resources/templates/emails/password-reset.html -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{emailSubject}}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .alert { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 4px; margin: 20px 0; }
        .button { display: inline-block; padding: 12px 24px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; }
        .security-info { background: #f8f9fa; padding: 20px; border-left: 4px solid #007bff; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{logoUrl}}" alt="{{appName}}" width="150">
            <h1>{{resetPasswordTitle}}</h1>
        </div>
        
        <div class="content">
            <p>{{greetingText}}, {{userName}}!</p>
            
            <p>{{resetRequestText}}</p>
            
            <div class="alert">
                <strong>{{importantText}}:</strong> {{resetInstructions}}
            </div>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{resetUrl}}" class="button">
                    {{resetPasswordButtonText}}
                </a>
            </div>
            
            <p>{{alternativeText}}: <br>
               <code>{{resetUrl}}</code>
            </p>
            
            <div class="security-info">
                <h3>{{securityTitle}}</h3>
                <ul>
                    <li>{{securityTip1}}</li>
                    <li>{{securityTip2}}</li>
                    <li>{{securityTip3}}</li>
                </ul>
            </div>
            
            <p>{{notRequestedText}} 
               <a href="mailto:{{supportEmail}}">{{contactUsText}}</a>
            </p>
        </div>
        
        <div class="footer">
            <p>{{expirationText}}: {{expirationTime}}</p>
            <p>&copy; {{currentYear}} {{appName}}</p>
        </div>
    </div>
</body>
</html>
```

---

## Controller Examples for Templates

### Home Controller
```php
<?php

class HomeController
{
    public function index(): Response
    {
        $data = [
            // Page metadata
            'pageTitle' => $this->trans('home.title', 'RenalTales - Home'),
            'currentLanguage' => $this->getCurrentLanguage(),
            'appName' => 'RenalTales',
            
            // Hero section
            'heroTitle' => $this->trans('home.hero.title', 'Welcome to RenalTales'),
            'heroDescription' => $this->trans('home.hero.description', 'Share your renal health journey and connect with others'),
            'getStartedText' => $this->trans('home.get_started', 'Get Started'),
            'dashboardText' => $this->trans('home.dashboard', 'Go to Dashboard'),
            
            // Features
            'featuresTitle' => $this->trans('home.features.title', 'How We Help'),
            'featureCards' => [
                [
                    'icon' => 'story',
                    'title' => $this->trans('home.features.stories.title', 'Share Stories'),
                    'description' => $this->trans('home.features.stories.description', 'Tell your story and inspire others'),
                    'link' => '/stories/create',
                    'buttonText' => $this->trans('home.features.stories.button', 'Create Story'),
                    'buttonClass' => 'btn btn-primary',
                    'cardClass' => 'feature-stories'
                ],
                [
                    'icon' => 'community',
                    'title' => $this->trans('home.features.community.title', 'Join Community'),
                    'description' => $this->trans('home.features.community.description', 'Connect with others on similar journeys'),
                    'link' => '/community',
                    'buttonText' => $this->trans('home.features.community.button', 'Explore Community'),
                    'buttonClass' => 'btn btn-secondary',
                    'cardClass' => 'feature-community'
                ],
                [
                    'icon' => 'resources',
                    'title' => $this->trans('home.features.resources.title', 'Learn More'),
                    'description' => $this->trans('home.features.resources.description', 'Access expert resources and information'),
                    'link' => '/resources',
                    'buttonText' => $this->trans('home.features.resources.button', 'View Resources'),
                    'buttonClass' => 'btn btn-outline',
                    'cardClass' => 'feature-resources'
                ]
            ],
            
            // User state
            'isLoggedIn' => $this->isAuthenticated(),
            'userName' => $this->getAuthenticatedUser()?->getName(),
            'userAvatar' => $this->getAuthenticatedUser()?->getAvatar(),
            
            // Language support
            'supportedLanguages' => $this->getSupportedLanguagesForSwitcher(),
            'currentLanguageName' => $this->getCurrentLanguageName(),
        ];
        
        $renderer = new TemplateRenderer();
        $html = $renderer->render('home', $data);
        
        return new Response($html);
    }
}
```

These examples demonstrate comprehensive template usage across different types of pages and components in the RenalTales application. Each template follows the established patterns and provides real-world examples of the template system's capabilities.
