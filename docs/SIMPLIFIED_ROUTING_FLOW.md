# Simplified Routing and Rendering Flow

This document describes the new simplified request flow implemented in RenalTales application.

## Overview

The new architecture implements a simple request flow:
**Route → Controller method → Prepare data → Render template**

## Key Components

### 1. Router (`src/Core/Router.php`)
- **Simple route mappings**: Direct array mapping of paths to controller methods
- **No complex routing logic**: Uses simple array lookup for route resolution
- **Backward compatibility**: Supports legacy `?page=` parameter routing
- **Error handling**: Built-in 404/500 error responses

```php
private array $routes = [
    '/' => [HomeController::class, 'index'],
    '/home' => [HomeController::class, 'index'],
    '/language/switch' => [LanguageController::class, 'switch'],
    '/language/current' => [LanguageController::class, 'current'],
    '/error' => [ErrorController::class, 'index'],
];
```

### 2. Controllers
Controllers now implement direct data preparation and template rendering:

#### HomeController (`src/Controllers/HomeController.php`)
- **Direct data preparation**: All page data prepared in `prepareHomeData()` method
- **Simple associative arrays**: Data passed to templates as simple arrays
- **No service dependencies**: Uses only Translation helper from global scope
- **Template rendering**: Direct call to `Template->render()`

#### LanguageController (`src/Controllers/SimpleLanguageController.php`)
- **JSON responses**: Returns PSR-7 Response objects with JSON data
- **Language switching**: Direct interaction with Translation helper
- **Simple validation**: Basic language code validation

#### ErrorController (`src/Controllers/ErrorController.php`)
- **Error page rendering**: Handles all error scenarios
- **Fallback HTML**: Provides simple HTML if template rendering fails
- **Status code handling**: Proper HTTP status codes for different errors

### 3. Template Engine (`src/Template.php`)
The existing template engine is used with simple data passing:

```php
$template = new Template();
$html = $template->render('home', $data, true);
```

- **Simple variable replacement**: Uses `{{ variable }}` and `<?= $variable ?>` syntax
- **Associative arrays**: All data passed as simple PHP arrays
- **No view models**: Eliminated complex view model classes

### 4. Application Bootstrap (`src/Core/Application.php`)
Simplified bootstrap process:

```php
public function run(): void
{
    try {
        $request = $this->createRequestFromGlobals();
        $router = new Router();
        $response = $router->handle($request);
        $this->sendResponse($response);
    } catch (\Throwable $e) {
        $this->sendErrorResponse($e->getMessage(), 500);
    }
}
```

## Eliminated Components

### Complex Service Layers
- **Removed**: `HomeDataService.php`, `ErrorDataService.php`
- **Replaced with**: Direct data preparation methods in controllers

### View Models and Data Transformers
- **Removed**: Complex data transformation classes
- **Replaced with**: Simple associative arrays

### Intermediate View Classes
- **Removed**: Complex view rendering classes
- **Replaced with**: Direct template rendering from controllers

## Data Flow

1. **Request arrives** at `public/index.php`
2. **Application** creates PSR-7 request from globals
3. **Router** matches path to controller method
4. **Controller** method is called with PSR-7 request
5. **Controller** prepares data as simple associative array
6. **Controller** renders template directly with data
7. **PSR-7 Response** is returned to application
8. **Application** sends response to client

## Example Request Flow

### Home Page Request (`/`)

1. Router matches `/` to `HomeController::index()`
2. `HomeController::index()` calls `prepareHomeData()`
3. `prepareHomeData()` returns simple array:
   ```php
   [
       'page_title' => 'RenalTales - Home',
       'hero_title' => 'Welcome to RenalTales!',
       'features' => [...],
       'nav_home' => 'Home',
       // ... more data
   ]
   ```
4. Template renders `home.php` with this data
5. HTML response is returned

### Language Switch Request (`/language/switch`)

1. Router matches to `LanguageController::switch()`
2. Controller extracts language from request body/params
3. Controller validates and switches language
4. JSON response returned:
   ```json
   {
       "success": true,
       "message": "Language switched successfully",
       "current_language": "sk"
   }
   ```

## Benefits

- **Simplified architecture**: Removed multiple layers of abstraction
- **Direct data flow**: Clear path from request to response
- **Easy debugging**: Simple to trace request through the system
- **Better performance**: Fewer object instantiations and method calls
- **Maintainable code**: Less complex interactions between components

## Templates

Templates remain in `resources/views/` and use simple PHP syntax:

```php
<!DOCTYPE html>
<html lang="<?= $language ?>">
<head>
    <title><?= $page_title ?></title>
</head>
<body>
    <h1><?= $hero_title ?></h1>
    <?php foreach ($features as $feature): ?>
        <div class="feature">
            <h2><?= $feature['title'] ?></h2>
            <p><?= $feature['description'] ?></p>
        </div>
    <?php endforeach; ?>
</body>
</html>
```

## Error Handling

- **Router level**: 404 errors for unknown routes
- **Controller level**: 500 errors for exceptions
- **Application level**: Global error handling with fallback HTML
- **Template level**: Fallback to simple HTML if template rendering fails
