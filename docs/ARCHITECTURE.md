# RenalTales Architecture Documentation

## Overview

This document describes the refactored architecture of the RenalTales application, which has been transformed from a simple MVC structure to a more sophisticated architecture using the Service Layer pattern, Repository pattern, and Dependency Injection.

## Architecture Components

#### 1. Dependency Injection Container (`src/Core/Container.php`)

The DI container is the heart of the new architecture. It provides:
- Service binding and resolution
- Singleton management
- Automatic dependency resolution
- Factory pattern support

**Key Features:**
- Automatic constructor injection
- Service lifetime management
- Circular dependency detection
- Type-safe resolution

### 2. Service Layer (`src/Services/`)

The service layer contains business logic and coordinates between repositories and controllers.

**LanguageService** (`src/Services/LanguageService.php`):
- Handles language switching logic
- Manages translation retrieval
- Coordinates with session management
- Provides language statistics

### 3. Repository Layer (`src/Repositories/`)

Repositories abstract data access and provide a consistent interface for data operations.

**LanguageRepository** (`src/Repositories/LanguageRepository.php`):
- Implements `RepositoryInterface`
- Provides CRUD operations for language data
- Abstracts data source details

### 4. Service Provider (`src/Core/ServiceProvider.php`)

The service provider registers all services and their dependencies in the container.

**Registration Process:**
1. Core services (SessionManager, SecurityManager, Logger)
2. Models (LanguageModel)
3. Repositories (LanguageRepository)
4. Services (LanguageService)
5. Controllers (on-demand)

### 5. Application Factory (`src/Core/Application.php`)

The application factory bootstraps the entire application and manages its lifecycle.

**Responsibilities:**
- Container initialization
- Service registration
- Application bootstrapping
- Error handling
- Application metadata

## Benefits of the New Architecture

### 1. Separation of Concerns
- **Controllers**: Handle HTTP requests and responses
- **Services**: Contain business logic
- **Repositories**: Handle data access
- **Models**: Represent data entities

### 2. Testability
- Dependencies are injected, making unit testing easier
- Services can be mocked for testing
- Clear interfaces enable contract testing

### 3. Maintainability
- Loosely coupled components
- Single responsibility principle
- Easy to modify individual components

### 4. Scalability
- Easy to add new services
- Repository pattern allows changing data sources
- Service layer can be expanded with new business logic

### 5. Dependency Management
- Automatic dependency resolution
- Singleton pattern for shared resources
- Clear dependency graph

## Usage Examples

### Service Resolution
```php
// Get language service
$languageService = $container->resolve(LanguageService::class);

// Switch language
$languageService->switchLanguage('en');

// Get translated text
$text = $languageService->getText('welcome_message', [], 'Welcome!');
```

### Repository Usage
```php
// Get language repository
$languageRepo = $container->resolve(LanguageRepository::class);

// Get all supported languages
$languages = $languageRepo->findAll();

// Check if language exists
$exists = $languageRepo->exists('en');
```

### Application Bootstrap
```php
// Initialize application
$app = new Application();
$app->bootstrap();
$app->run();
```

## Configuration

### Service Registration

Services are registered in `ServiceProvider::register()`:

```php
// Register as singleton
$this->container->singleton(LanguageService::class, function (Container $container) {
    return new LanguageService(
        $container->resolve(LanguageRepository::class),
        $container->resolve(SessionManager::class),
        $container->resolve(LanguageModel::class)
    );
});
```

### Service Binding

Services can be bound in different ways:

```php
// Bind class to interface
$container->bind(RepositoryInterface::class, LanguageRepository::class);

// Bind with factory
$container->factory(ServiceInterface::class, function($container) {
    return new ConcreteService($container->resolve(Dependency::class));
});
```

## Migration Guide

### From Old Architecture
1. Controllers directly instantiated dependencies
2. Business logic mixed with controllers
3. Direct model access from controllers
4. No dependency injection

### To New Architecture
1. Dependencies injected through constructor
2. Business logic in service layer
3. Repository layer for data access
4. Full dependency injection container

## Error Handling

The new architecture provides centralized error handling:

```php
try {
    $app = new Application();
    $app->bootstrap();
    $app->run();
} catch (Throwable $e) {
    // Centralized error handling
    $errorView = new ErrorView($e, $debugMode, $languageService);
    echo $errorView->render();
}
```

## Performance Considerations

1. **Singletons**: Core services are singletons to avoid recreating expensive objects
2. **Lazy Loading**: Services are only instantiated when needed
3. **Caching**: Container caches resolved services
4. **Minimal Overhead**: DI container is optimized for performance

## Future Enhancements

1. **Event System**: Add event dispatching for loose coupling
2. **Middleware**: Add middleware support for request/response processing
3. **Cache Service**: Add caching layer for performance
4. **Database Layer**: Add database abstraction and ORM
5. **API Layer**: Add REST API support
6. **Validation**: Add input validation service
7. **Logging**: Enhanced logging with multiple channels

## Testing

### Unit Testing
```php
// Mock dependencies
$mockRepo = $this->createMock(LanguageRepository::class);
$mockSession = $this->createMock(SessionManager::class);
$mockModel = $this->createMock(LanguageModel::class);

// Test service
$service = new LanguageService($mockRepo, $mockSession, $mockModel);
$result = $service->switchLanguage('en');
```

### Integration Testing
```php
// Test with real container
$container = new Container();
$serviceProvider = new ServiceProvider($container);
$serviceProvider->register();

$languageService = $container->resolve(LanguageService::class);
$this->assertTrue($languageService->switchLanguage('en'));
```

## Conclusion

The refactored architecture provides a solid foundation for the RenalTales application with improved maintainability, testability, and scalability. The use of dependency injection, service layers, and repository patterns creates a clean, professional codebase that follows modern PHP best practices.
