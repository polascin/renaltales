# RenalTales Template System - Complete Guide

## Table of Contents

1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Template Syntax](#template-syntax)
4. [Components and Partials](#components-and-partials)
5. [Data Binding](#data-binding)
6. [Security Features](#security-features)
7. [Performance Optimization](#performance-optimization)
8. [Migration from Old System](#migration-from-old-system)
9. [Common Use Cases](#common-use-cases)
10. [Best Practices](#best-practices)
11. [Troubleshooting](#troubleshooting)

---

## Overview

RenalTales has transitioned from a complex PHP string concatenation system to a modern, maintainable template system. This new approach provides:

- **Clean separation** of presentation and business logic
- **Component-based architecture** for reusable UI elements
- **Template caching** for improved performance
- **Security-first design** with automatic HTML escaping
- **Multi-language support** with seamless integration

### Benefits Over Previous System

| Old System | New System |
|------------|------------|
| 607+ lines of PHP concatenation | Clean, focused template files |
| Mixed logic and presentation | Clear separation of concerns |
| Hard to maintain and modify | Easy template editing |
| No component reusability | Modular, reusable components |
| Manual HTML escaping | Automatic security escaping |

---

## Architecture

### Core Components

```
src/
├── Contracts/
│   └── TemplateRendererInterface.php    # Template renderer contract
├── Services/
│   └── TemplateRenderer.php             # Main template engine
└── Template.php                         # Legacy compatibility layer

resources/
├── templates/                           # New template system
│   ├── home.html                        # Main templates
│   └── components/                      # Reusable components
│       ├── header.html
│       ├── footer.html
│       ├── navigation.html
│       └── language-switcher.html
└── views/                               # Legacy templates (deprecated)
```

### Template Renderer Interface

The `TemplateRendererInterface` defines the contract for all template rendering:

```php
interface TemplateRendererInterface
{
    public function render(string $template, array $data = []): string;
    public function registerPartial(string $name, string $template): void;
    public function setTemplateDirectory(string $directory): void;
    public function templateExists(string $template): bool;
}
```

---

## Template Syntax

### Variable Substitution

```html
<!-- Basic variable -->
<h1>{{pageTitle}}</h1>
<p>Welcome to {{appName}}!</p>

<!-- Nested data -->
<span>{{user.name}} - {{user.email}}</span>
```

### Conditionals

```html
<!-- Show content if condition is true -->
{{#isLoggedIn}}
    <p>Welcome back, {{userName}}!</p>
    <a href="/dashboard">Go to Dashboard</a>
{{/isLoggedIn}}

<!-- Multiple conditions -->
{{#hasPermissions}}
    {{#isAdmin}}
        <button class="btn-admin">Admin Panel</button>
    {{/isAdmin}}
{{/hasPermissions}}
```

### Loops

```html
<!-- Loop through arrays -->
{{#menuItems}}
    <li><a href="{{url}}">{{title}}</a></li>
{{/menuItems}}

<!-- Feature cards example -->
{{#featureCards}}
    <div class="card {{cardClass}}">
        <h3>{{title}}</h3>
        <p>{{description}}</p>
        <a href="{{link}}" class="{{buttonClass}}">{{buttonText}}</a>
    </div>
{{/featureCards}}
```

### Partials/Components

```html
<!-- Include header component -->
{{>header}}

<!-- Include navigation with current page context -->
{{>navigation}}

<!-- Include footer -->
{{>footer}}
```

---

## Components and Partials

### Available Components

#### Header Component (`components/header.html`)
```html
<header class="main-header">
    <div class="container">
        <h1 class="logo">{{appName}}</h1>
        {{>theme-toggle}}
    </div>
</header>
```

#### Navigation Component (`components/navigation.html`)
```html
<nav class="main-nav">
    <ul>
        {{#navigationItems}}
            <li><a href="{{url}}" {{#isActive}}class="active"{{/isActive}}>{{title}}</a></li>
        {{/navigationItems}}
    </ul>
</nav>
```

#### Language Switcher (`components/language-switcher.html`)
```html
<div class="language-switcher">
    <label for="language-select">{{languageLabel}}</label>
    <select id="language-select" name="language">
        {{#supportedLanguages}}
            <option value="{{code}}" {{#selected}}selected{{/selected}}>{{name}}</option>
        {{/supportedLanguages}}
    </select>
</div>
```

### Creating Custom Components

1. Create a new `.html` file in `resources/templates/components/`
2. Use template syntax for dynamic content
3. Include in templates using `{{>component-name}}`

Example custom component (`components/alert.html`):
```html
<div class="alert alert-{{type}}">
    {{#icon}}<i class="icon-{{icon}}"></i>{{/icon}}
    <span>{{message}}</span>
    {{#dismissible}}<button class="btn-close" data-dismiss="alert">&times;</button>{{/dismissible}}
</div>
```

---

## Data Binding

### Controller Data Preparation

Controllers should prepare data as simple associative arrays:

```php
class HomeController 
{
    public function index(): Response
    {
        $data = [
            // Page metadata
            'pageTitle' => 'RenalTales - Home',
            'appName' => 'RenalTales',
            'currentLanguage' => 'en',
            
            // Navigation
            'navigationItems' => [
                ['url' => '/', 'title' => 'Home', 'isActive' => true],
                ['url' => '/stories', 'title' => 'Stories', 'isActive' => false],
                ['url' => '/about', 'title' => 'About', 'isActive' => false],
            ],
            
            // Feature cards
            'featureCards' => [
                [
                    'title' => 'Create Stories',
                    'description' => 'Share your renal health journey',
                    'link' => '/stories/create',
                    'buttonText' => 'Get Started',
                    'buttonClass' => 'btn-primary',
                    'cardClass' => 'feature-create'
                ],
                [
                    'title' => 'Connect with Others',
                    'description' => 'Join our supportive community',
                    'link' => '/community',
                    'buttonText' => 'Join Now',
                    'buttonClass' => 'btn-secondary',
                    'cardClass' => 'feature-connect'
                ]
            ],
            
            // Language support
            'supportedLanguages' => [
                ['code' => 'en', 'name' => 'English', 'selected' => true],
                ['code' => 'sk', 'name' => 'Slovak', 'selected' => false],
                ['code' => 'es', 'name' => 'Spanish', 'selected' => false],
            ],
            
            // Conditional content
            'isLoggedIn' => false,
            'hasNotifications' => true,
            'notificationCount' => 3,
        ];
        
        $renderer = new TemplateRenderer();
        $html = $renderer->render('home', $data);
        
        return new Response($html);
    }
}
```

### Complex Data Structures

For nested data and complex objects:

```php
$data = [
    'user' => [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'profile' => [
            'avatar' => '/images/avatars/john.jpg',
            'bio' => 'Renal health advocate',
            'joinDate' => '2024-01-15'
        ]
    ],
    'dashboard' => [
        'stats' => [
            'storiesShared' => 15,
            'communitiesJoined' => 3,
            'helpfulVotes' => 42
        ],
        'recentActivity' => [
            ['type' => 'story', 'title' => 'My dialysis journey', 'date' => '2025-01-15'],
            ['type' => 'comment', 'title' => 'Encouraging words', 'date' => '2025-01-14'],
        ]
    ]
];
```

Template usage:
```html
<div class="user-profile">
    <img src="{{user.profile.avatar}}" alt="{{user.name}}">
    <h2>{{user.name}}</h2>
    <p>{{user.profile.bio}}</p>
    
    <div class="stats">
        <span>Stories: {{dashboard.stats.storiesShared}}</span>
        <span>Communities: {{dashboard.stats.communitiesJoined}}</span>
        <span>Helpful Votes: {{dashboard.stats.helpfulVotes}}</span>
    </div>
</div>
```

---

## Security Features

### Automatic HTML Escaping

All variables are automatically escaped for HTML output:

```php
$data = [
    'userInput' => '<script>alert("XSS")</script>',
    'safeContent' => 'This is safe content'
];
```

Template output:
```html
<!-- Automatically escaped - safe -->
<p>{{userInput}}</p>
<!-- Outputs: <p>&lt;script&gt;alert("XSS")&lt;/script&gt;</p> -->

<p>{{safeContent}}</p>
<!-- Outputs: <p>This is safe content</p> -->
```

### CSRF Protection Integration

Templates can include CSRF tokens:

```php
$data = [
    'csrfToken' => $securityManager->getCSRFToken()
];
```

```html
<form method="POST" action="/submit">
    <input type="hidden" name="csrf_token" value="{{csrfToken}}">
    <!-- form fields -->
</form>
```

### Security Best Practices

1. **Never bypass escaping** unless absolutely necessary
2. **Validate all input data** before passing to templates
3. **Use CSRF tokens** in all forms
4. **Sanitize file paths** when including dynamic templates
5. **Log security violations** for monitoring

---

## Performance Optimization

### Template Caching

The template system automatically caches loaded templates:

```php
class TemplateRenderer 
{
    private array $cache = [];
    
    private function loadTemplate(string $template): string 
    {
        // Check cache first
        if (isset($this->cache[$template])) {
            return $this->cache[$template];
        }
        
        // Load and cache
        $content = file_get_contents($templatePath);
        $this->cache[$template] = $content;
        return $content;
    }
}
```

### Optimization Techniques

#### 1. Minimize Template Complexity
```html
<!-- Good: Simple, direct -->
{{#items}}
    <li>{{name}}</li>
{{/items}}

<!-- Avoid: Nested complexity -->
{{#items}}
    {{#subcategories}}
        {{#subitems}}
            <!-- deeply nested content -->
        {{/subitems}}
    {{/subcategories}}
{{/items}}
```

#### 2. Precompute Data in Controllers
```php
// Good: Prepare data in controller
$data = [
    'formattedDate' => date('F j, Y', $timestamp),
    'userDisplayName' => $user->firstName . ' ' . $user->lastName,
    'isActiveUser' => $user->lastLogin > strtotime('-30 days')
];

// Avoid: Complex logic in templates
$data = [
    'timestamp' => $timestamp,
    'user' => $user  // Don't pass complex objects
];
```

#### 3. Use Component Caching for Static Parts
```php
// Cache static components separately
if (!$this->cache['header_cached']) {
    $this->cache['header_cached'] = $this->renderComponent('header', $staticData);
}
```

### Performance Monitoring

The system includes built-in performance monitoring:

```php
class TemplateRenderer 
{
    private function measureRenderTime(callable $renderFunction): string 
    {
        $startTime = microtime(true);
        $result = $renderFunction();
        $endTime = microtime(true);
        
        $renderTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
        
        if ($renderTime > 100) { // Log slow renders
            error_log("Slow template render: {$renderTime}ms");
        }
        
        return $result;
    }
}
```

---

## Migration from Old System

### Conversion Steps

#### 1. Identify Old Template Methods
```php
// Old system (deprecated)
class HomeView extends AbstractView 
{
    public function getHomePageTemplate(): string 
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head><title>{$this->pageTitle}</title></head>
<body>
    {$this->getHeaderContent()}
    {$this->getMainContent()}
    {$this->getFooterContent()}
</body>
</html>
HTML;
    }
}
```

#### 2. Create New Template Files
```html
<!-- resources/templates/home.html -->
<!DOCTYPE html>
<html lang="{{currentLanguage}}">
<head>
    <title>{{pageTitle}}</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    {{>header}}
    <main>{{>main-content}}</main>
    {{>footer}}
</body>
</html>
```

#### 3. Convert Controllers
```php
// New system
class HomeController 
{
    public function index(): Response 
    {
        $renderer = new TemplateRenderer();
        $data = $this->prepareHomeData();
        $html = $renderer->render('home', $data);
        
        return new Response($html);
    }
    
    private function prepareHomeData(): array 
    {
        return [
            'pageTitle' => $this->trans('home.title'),
            'currentLanguage' => $this->getCurrentLanguage(),
            'appName' => 'RenalTales',
            // ... other data
        ];
    }
}
```

### Backward Compatibility

The system maintains backward compatibility through adapter patterns:

```php
// Legacy support wrapper
class HomeView extends AbstractView 
{
    public function render(array $data = []): string 
    {
        $renderer = new TemplateRenderer();
        $templateData = $this->convertLegacyData($data);
        return $renderer->render('home', $templateData);
    }
    
    private function convertLegacyData(array $legacyData): array 
    {
        // Convert old data format to new template format
        return [
            'pageTitle' => $legacyData['title'] ?? 'RenalTales',
            'currentLanguage' => $legacyData['lang'] ?? 'en',
            // ... data conversion
        ];
    }
}
```

---

## Common Use Cases

### 1. Basic Page Rendering

```php
// Controller
public function aboutPage(): Response 
{
    $data = [
        'pageTitle' => 'About RenalTales',
        'heroTitle' => 'Our Mission',
        'heroDescription' => 'Empowering renal health through shared stories',
        'features' => [
            ['title' => 'Community Support', 'icon' => 'users'],
            ['title' => 'Expert Resources', 'icon' => 'book'],
            ['title' => 'Personal Stories', 'icon' => 'heart']
        ]
    ];
    
    return $this->renderTemplate('about', $data);
}
```

```html
<!-- templates/about.html -->
<!DOCTYPE html>
<html>
<head><title>{{pageTitle}}</title></head>
<body>
    {{>header}}
    
    <section class="hero">
        <h1>{{heroTitle}}</h1>
        <p>{{heroDescription}}</p>
    </section>
    
    <section class="features">
        {{#features}}
            <div class="feature-item">
                <i class="icon-{{icon}}"></i>
                <h3>{{title}}</h3>
            </div>
        {{/features}}
    </section>
    
    {{>footer}}
</body>
</html>
```

### 2. Form Rendering with Validation

```php
// Controller with form data and errors
public function showContactForm(?array $errors = null): Response 
{
    $data = [
        'pageTitle' => 'Contact Us',
        'formAction' => '/contact/submit',
        'csrfToken' => $this->getCSRFToken(),
        'formData' => $this->getFlashData('form_data', []),
        'errors' => $errors ?? [],
        'hasErrors' => !empty($errors)
    ];
    
    return $this->renderTemplate('contact', $data);
}
```

```html
<!-- templates/contact.html -->
<form method="POST" action="{{formAction}}">
    <input type="hidden" name="csrf_token" value="{{csrfToken}}">
    
    {{#hasErrors}}
        <div class="alert alert-error">
            <ul>
                {{#errors}}
                    <li>{{message}}</li>
                {{/errors}}
            </ul>
        </div>
    {{/hasErrors}}
    
    <div class="form-group">
        <label for="name">Name</label>
        <input type="text" name="name" value="{{formData.name}}" required>
    </div>
    
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" value="{{formData.email}}" required>
    </div>
    
    <button type="submit">Send Message</button>
</form>
```

### 3. Dynamic Navigation with Active States

```php
// Service to build navigation
class NavigationBuilder 
{
    public function buildMainNavigation(string $currentPath): array 
    {
        $items = [
            ['path' => '/', 'label' => 'Home'],
            ['path' => '/stories', 'label' => 'Stories'],
            ['path' => '/community', 'label' => 'Community'],
            ['path' => '/resources', 'label' => 'Resources'],
            ['path' => '/about', 'label' => 'About']
        ];
        
        return array_map(function ($item) use ($currentPath) {
            $item['isActive'] = $item['path'] === $currentPath;
            $item['cssClass'] = $item['isActive'] ? 'nav-item active' : 'nav-item';
            return $item;
        }, $items);
    }
}
```

```html
<!-- components/navigation.html -->
<nav class="main-navigation">
    <ul class="nav-list">
        {{#navigationItems}}
            <li class="{{cssClass}}">
                <a href="{{path}}">{{label}}</a>
            </li>
        {{/navigationItems}}
    </ul>
</nav>
```

### 4. Multi-Language Content

```php
// Multi-language controller
public function renderWithLanguage(string $template, array $data): Response 
{
    $language = $this->getCurrentLanguage();
    
    $templateData = array_merge($data, [
        'currentLanguage' => $language,
        'translations' => [
            'welcome' => $this->trans('welcome'),
            'navigation' => [
                'home' => $this->trans('nav.home'),
                'about' => $this->trans('nav.about'),
            ]
        ],
        'supportedLanguages' => $this->getSupportedLanguages()
    ]);
    
    return $this->renderTemplate($template, $templateData);
}
```

```html
<!-- Multi-language template -->
<html lang="{{currentLanguage}}">
<body>
    <nav>
        <a href="/">{{translations.navigation.home}}</a>
        <a href="/about">{{translations.navigation.about}}</a>
        
        {{>language-switcher}}
    </nav>
    
    <main>
        <h1>{{translations.welcome}}</h1>
        <!-- content -->
    </main>
</body>
</html>
```

---

## Best Practices

### 1. Template Organization

```
resources/templates/
├── layouts/           # Base layouts
│   ├── main.html
│   └── minimal.html
├── pages/             # Complete pages
│   ├── home.html
│   ├── about.html
│   └── contact.html
├── components/        # Reusable components
│   ├── header.html
│   ├── footer.html
│   ├── navigation.html
│   └── forms/
│       ├── contact-form.html
│       └── search-form.html
└── partials/          # Small, specific parts
    ├── breadcrumb.html
    ├── pagination.html
    └── social-share.html
```

### 2. Naming Conventions

#### Template Files
- Use kebab-case: `user-profile.html`, `contact-form.html`
- Be descriptive: `error-404.html`, not `error.html`
- Group related templates: `forms/`, `modals/`, `cards/`

#### Template Variables
- Use snake_case: `user_name`, `page_title`
- Be consistent: always `currentLanguage`, not mixing `current_language`
- Use prefixes for grouped data: `user.name`, `nav.items`

#### Component Names
- Use descriptive names: `{{>user-avatar}}`, not `{{>avatar}}`
- Indicate purpose: `{{>form-error-message}}`, `{{>modal-confirm}}`

### 3. Data Structure Best Practices

#### Keep Data Flat When Possible
```php
// Good: Flat structure
$data = [
    'userName' => 'John Doe',
    'userEmail' => 'john@example.com',
    'userAvatar' => '/avatars/john.jpg'
];

// Avoid: Deep nesting unless necessary
$data = [
    'user' => [
        'personal' => [
            'name' => 'John Doe',
            'contact' => [
                'email' => 'john@example.com'
            ]
        ]
    ]
];
```

#### Use Consistent Array Structures
```php
// Good: Consistent structure for repeated elements
$data = [
    'menuItems' => [
        ['url' => '/', 'title' => 'Home', 'active' => true],
        ['url' => '/about', 'title' => 'About', 'active' => false],
    ],
    'socialLinks' => [
        ['url' => 'https://facebook.com', 'title' => 'Facebook', 'icon' => 'facebook'],
        ['url' => 'https://twitter.com', 'title' => 'Twitter', 'icon' => 'twitter'],
    ]
];
```

### 4. Performance Optimization

#### Minimize Template Processing
```php
// Cache computed values
class TemplateDataBuilder 
{
    private array $cache = [];
    
    public function buildUserData(User $user): array 
    {
        $cacheKey = "user_data_{$user->getId()}";
        
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        $data = [
            'displayName' => $user->getFirstName() . ' ' . $user->getLastName(),
            'initials' => strtoupper($user->getFirstName()[0] . $user->getLastName()[0]),
            'memberSince' => $user->getCreatedAt()->format('F Y'),
            'isActive' => $user->getLastLogin() > new DateTime('-30 days')
        ];
        
        $this->cache[$cacheKey] = $data;
        return $data;
    }
}
```

#### Use Lazy Loading for Expensive Data
```php
// Load expensive data only when needed
public function preparePageData(): array 
{
    return [
        'basicInfo' => $this->getBasicInfo(),
        'lazyContent' => function() {
            return $this->getExpensiveData();
        }
    ];
}
```

### 5. Security Guidelines

#### Always Validate Template Paths
```php
public function renderTemplate(string $template, array $data): string 
{
    // Validate template name
    if (!preg_match('/^[a-zA-Z0-9\-_\/]+$/', $template)) {
        throw new InvalidArgumentException('Invalid template name');
    }
    
    // Prevent directory traversal
    if (strpos($template, '..') !== false) {
        throw new SecurityException('Directory traversal attempt');
    }
    
    return $this->templateRenderer->render($template, $data);
}
```

#### Sanitize Dynamic Content
```php
public function prepareUserContent(string $userInput): array 
{
    return [
        'content' => strip_tags($userInput), // Remove HTML tags
        'excerpt' => substr($userInput, 0, 200), // Limit length
        'wordCount' => str_word_count($userInput)
    ];
}
```

### 6. Testing Templates

#### Unit Testing Template Output
```php
class TemplateTest extends TestCase 
{
    public function testHomeTemplateRender(): void 
    {
        $renderer = new TemplateRenderer();
        $data = [
            'pageTitle' => 'Test Page',
            'appName' => 'TestApp'
        ];
        
        $html = $renderer->render('home', $data);
        
        $this->assertStringContains('<title>Test Page</title>', $html);
        $this->assertStringContains('TestApp', $html);
        $this->assertStringNotContains('{{', $html); // No unprocessed variables
    }
}
```

#### Integration Testing with Controllers
```php
class HomeControllerTest extends TestCase 
{
    public function testHomePageResponse(): void 
    {
        $controller = new HomeController();
        $response = $controller->index();
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContains('<!DOCTYPE html>', $response->getBody());
        $this->assertStringContains('RenalTales', $response->getBody());
    }
}
```

---

## Troubleshooting

### Common Issues and Solutions

#### 1. Template Not Found
**Error:** `Template not found: home (looked in: /path/to/templates/home.html)`

**Solutions:**
- Check file path and name spelling
- Ensure template directory is correctly set
- Verify file permissions

```php
// Debug template loading
$renderer = new TemplateRenderer();
$renderer->setTemplateDirectory('/correct/path/to/templates');

if (!$renderer->templateExists('home')) {
    throw new Exception('Template home.html not found in templates directory');
}
```

#### 2. Variables Not Displaying
**Issue:** `{{variable}}` appears as literal text in output

**Solutions:**
- Check variable name spelling in data array
- Ensure data is passed correctly to render method
- Verify template syntax

```php
// Debug data passing
$data = ['pageTitle' => 'Test'];
$html = $renderer->render('test', $data);

// Check for unprocessed variables
if (strpos($html, '{{') !== false) {
    error_log('Unprocessed template variables found');
}
```

#### 3. Component Not Loading
**Issue:** `{{>component}}` results in empty content

**Solutions:**
- Check component file exists in `components/` directory
- Verify component name spelling
- Check component template syntax

```php
// Debug component loading
$componentPath = $templateDir . '/components/header.html';
if (!file_exists($componentPath)) {
    error_log("Component not found: {$componentPath}");
}
```

#### 4. Loop Not Working
**Issue:** `{{#items}}` section doesn't repeat

**Solutions:**
- Ensure data is an array
- Check array key name matches template
- Verify array is not empty

```php
// Debug loop data
$data = [
    'items' => [
        ['name' => 'Item 1'],
        ['name' => 'Item 2']
    ]
];

// Log array structure
error_log('Loop data: ' . print_r($data['items'], true));
```

#### 5. Performance Issues
**Issue:** Templates rendering slowly

**Solutions:**
- Enable template caching
- Minimize data complexity
- Use performance monitoring

```php
// Monitor render time
$start = microtime(true);
$html = $renderer->render('template', $data);
$time = (microtime(true) - $start) * 1000;

if ($time > 100) {
    error_log("Slow render: {$time}ms for template");
}
```

### Debugging Tools

#### Template Debug Mode
```php
class DebugTemplateRenderer extends TemplateRenderer 
{
    private bool $debugMode = false;
    
    public function setDebugMode(bool $debug): void 
    {
        $this->debugMode = $debug;
    }
    
    public function render(string $template, array $data = []): string 
    {
        if ($this->debugMode) {
            $html = parent::render($template, $data);
            
            // Add debug information
            $debugInfo = "<!-- Template: {$template} -->\n";
            $debugInfo .= "<!-- Variables: " . json_encode(array_keys($data)) . " -->\n";
            
            return $debugInfo . $html;
        }
        
        return parent::render($template, $data);
    }
}
```

#### Variable Inspector
```php
function inspectTemplateData(array $data, string $template): void 
{
    echo "Template: {$template}\n";
    echo "Data structure:\n";
    
    foreach ($data as $key => $value) {
        $type = gettype($value);
        if (is_array($value)) {
            $type .= '[' . count($value) . ']';
        }
        echo "  {$key}: {$type}\n";
    }
}
```

### Development Workflow

#### 1. Template Development Cycle
1. **Create template file** with static content
2. **Add template variables** for dynamic content
3. **Prepare controller data** to match template variables
4. **Test template rendering** with sample data
5. **Refine and optimize** based on requirements

#### 2. Component Development
1. **Identify reusable elements** across pages
2. **Extract to component files** with parameterized content
3. **Update templates** to use components (`{{>component}}`)
4. **Test component integration** across different pages

#### 3. Performance Testing
1. **Measure baseline performance** before changes
2. **Implement caching** for static components
3. **Profile template rendering** to identify bottlenecks
4. **Optimize data structures** for template consumption

---

This comprehensive guide provides everything needed to work effectively with the RenalTales template system. The new architecture promotes maintainable, secure, and performant web applications while preserving all the functionality of the previous system.
