<?php

declare(strict_types=1);

namespace RenalTales\Core;

use RenalTales\Core\Container;
use RenalTales\Core\SessionManager;
use RenalTales\Core\SecurityManager;
use RenalTales\Core\DatabaseManager;
use RenalTales\Models\LanguageModel;
use RenalTales\Repositories\LanguageRepository;
use RenalTales\Repositories\DoctrineLanguageRepository;
use RenalTales\Services\LanguageService;
use RenalTales\Services\RateLimiterService;
use RenalTales\Services\PasswordHashingService;
use RenalTales\Services\PerformanceService;
use RenalTales\Core\CacheManager;
use RenalTales\Core\AsyncManager;
use RenalTales\Repositories\CachedLanguageRepository;
use RenalTales\Core\ErrorHandler;
use RenalTales\Core\ErrorHandlingMiddleware;
use RenalTales\Core\MiddlewareManager;
use RenalTales\Core\LoggerFactory;
use Monolog\Logger as MonologLogger;
use Psr\Log\LoggerInterface;

/**
 * Service Provider
 *
 * Registers all services and their dependencies in the container.
 * Handles the configuration and binding of services for dependency injection.
 *
 * @package RenalTales\Core
 * @version 2025.v3.1.dev
 * @author Ľubomír Polaščín
 */
class ServiceProvider
{
    /**
     * @var Container The dependency injection container
     */
    private Container $container;

    /**
     * Constructor
     *
     * @param Container $container The dependency injection container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Register all services in the container
     *
     * @return void
     */
    public function register(): void
    {
        $this->registerModels();
        $this->registerRepositories();
        $this->registerServices();
        $this->registerCoreServices();
        $this->registerControllers();
    }

    /**
     * Register core services
     *
     * @return void
     */
    private function registerCoreServices(): void
    {
        // Register SessionManager as singleton
        $this->container->singleton(SessionManager::class, function (Container $container) {
            return new SessionManager([], defined('APP_DEBUG') ? APP_DEBUG : false);
        });

        // Register SecurityManager as singleton
        $this->container->singleton(SecurityManager::class, function (Container $container) {
            $sessionManager = $container->resolve(SessionManager::class);
            $config = require (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 2)) . '/config/security.php';
            $securityManager = new SecurityManager($sessionManager, $config);

            // Inject security services after they're registered
            if ($container->bound(RateLimiterService::class)) {
                $rateLimiterService = $container->resolve(RateLimiterService::class);
                $securityManager->setRateLimiterService($rateLimiterService);
            }

            if ($container->bound(PasswordHashingService::class)) {
                $passwordHashingService = $container->resolve(PasswordHashingService::class);
                $securityManager->setPasswordHashingService($passwordHashingService);
            }

            return $securityManager;
        });

        // Register Logger as singleton
        $this->container->singleton(Logger::class, function (Container $container) {
            return new Logger();
        });

        // Register DatabaseManager as singleton
        $this->container->singleton(DatabaseManager::class, function (Container $container) {
            $databaseConfig = require (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 2)) . '/config/database.php';
            $logger = $container->resolve(Logger::class);
            return new DatabaseManager($databaseConfig, $logger);
        });

        // Register CacheManager as singleton
        $this->container->singleton(CacheManager::class, function (Container $container) {
            $cacheConfig = require (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 2)) . '/config/cache.php';
            $logger = $container->resolve(Logger::class);
            return new CacheManager($cacheConfig, $logger);
        });

        // Register AsyncManager as singleton
        $this->container->singleton(AsyncManager::class, function (Container $container) {
            $databaseConfig = require (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 2)) . '/config/database.php';
            $logger = $container->resolve(Logger::class);
            return new AsyncManager($databaseConfig, $logger);
        });

        // Register Monolog Logger as singleton
        $this->container->singleton(MonologLogger::class, function (Container $container) {
            $environment = $_ENV['APP_ENV'] ?? 'development';
            $logPath = APP_ROOT . '/storage/logs/app.log';
            return LoggerFactory::createAppLogger($environment, $logPath);
        });

        // Register LoggerInterface to point to Monolog Logger
        $this->container->bind(LoggerInterface::class, function (Container $container) {
            return $container->resolve(MonologLogger::class);
        });

        // Register ErrorHandler as singleton
        $this->container->singleton(ErrorHandler::class, function (Container $container) {
            $monologLogger = $container->resolve(MonologLogger::class);
            $debug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $environment = $_ENV['APP_ENV'] ?? 'development';
            return new ErrorHandler($monologLogger, $debug, $environment);
        });

        // Register ErrorHandlingMiddleware
        $this->container->bind(ErrorHandlingMiddleware::class, function (Container $container) {
            $errorHandler = $container->resolve(ErrorHandler::class);
            $logger = $container->resolve(LoggerInterface::class);
            return new ErrorHandlingMiddleware($errorHandler, $logger);
        });

        // Register MiddlewareManager as singleton
        $this->container->singleton(MiddlewareManager::class, function (Container $container) {
            $manager = new MiddlewareManager();
            // Add error handling middleware
            $errorMiddleware = $container->resolve(ErrorHandlingMiddleware::class);
            $manager->add($errorMiddleware);
            return $manager;
        });
    }

    /**
     * Register models
     *
     * @return void
     */
    private function registerModels(): void
    {
        // Register LanguageModel as singleton
        $this->container->singleton(LanguageModel::class, function (Container $container) {
            return new LanguageModel();
        });
    }

    /**
     * Register repositories
     *
     * @return void
     */
    private function registerRepositories(): void
    {
        // Register LanguageRepository
        $this->container->bind(LanguageRepository::class, function (Container $container) {
            $languageModel = $container->resolve(LanguageModel::class);
            return new LanguageRepository($languageModel);
        });

        // Register CachedLanguageRepository
        $this->container->bind(CachedLanguageRepository::class, function (Container $container) {
            $baseRepository = $container->resolve(LanguageRepository::class);
            $cacheManager = $container->resolve(CacheManager::class);
            $asyncManager = $container->resolve(AsyncManager::class);
            return new CachedLanguageRepository($baseRepository, $cacheManager, $asyncManager);
        });
    }

    /**
     * Register services
     *
     * @return void
     */
    private function registerServices(): void
    {
        // Register LanguageService
        $this->container->bind(LanguageService::class, function (Container $container) {
            $languageRepository = $container->resolve(CachedLanguageRepository::class);
            $sessionManager = $container->resolve(SessionManager::class);
            $languageModel = $container->resolve(LanguageModel::class);

            return new LanguageService($languageRepository, $sessionManager, $languageModel);
        });

        // Register RateLimiterService as singleton
        $this->container->singleton(RateLimiterService::class, function (Container $container) {
            $config = require (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 2)) . '/config/security.php';
            return new RateLimiterService($config['rate_limiting'] ?? []);
        });

        // Register PasswordHashingService as singleton
        $this->container->singleton(PasswordHashingService::class, function (Container $container) {
            $config = require (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 2)) . '/config/security.php';
            return new PasswordHashingService($config['password_hashing'] ?? []);
        });

        // Register PerformanceService as singleton
        $this->container->singleton(PerformanceService::class, function (Container $container) {
            $cacheManager = $container->resolve(CacheManager::class);
            $asyncManager = $container->resolve(AsyncManager::class);
            $logger = $container->resolve(Logger::class);
            return new PerformanceService($cacheManager, $asyncManager, $logger);
        });
    }

    /**
     * Register controllers
     *
     * @return void
     */
    private function registerControllers(): void
    {
        // Controllers will be registered as needed
        // They typically don't need to be singletons
    }

    /**
     * Boot services that need initialization
     *
     * @return void
     */
    public function boot(): void
    {
        // Boot any services that need initialization
        $this->bootSessionManager();
        $this->bootSecurityManager();
        $this->bootDatabaseManager();
        $this->bootLanguageService();
        $this->bootErrorHandler();
    }

    /**
     * Boot session manager
     *
     * @return void
     */
    private function bootSessionManager(): void
    {
        $sessionManager = $this->container->resolve(SessionManager::class);
        // Session manager boots itself in constructor
    }

    /**
     * Boot security manager
     *
     * @return void
     */
    private function bootSecurityManager(): void
    {
        $securityManager = $this->container->resolve(SecurityManager::class);
        // Security manager boots itself in constructor
    }

    /**
     * Boot database manager
     *
     * @return void
     */
    private function bootDatabaseManager(): void
    {
        $databaseManager = $this->container->resolve(DatabaseManager::class);
        $databaseManager->initialize();
    }

    /**
     * Boot language service
     *
     * @return void
     */
    private function bootLanguageService(): void
    {
        $languageService = $this->container->resolve(LanguageService::class);

        // Handle language switching from request parameters
        if (isset($_GET['lang']) && is_string($_GET['lang'])) {
            $requestedLang = trim($_GET['lang']);
            $languageService->switchLanguage($requestedLang);
        } elseif (isset($_POST['lang']) && is_string($_POST['lang'])) {
            $requestedLang = trim($_POST['lang']);
            $languageService->switchLanguage($requestedLang);
        }
    }

    /**
     * Boot error handler
     *
     * @return void
     */
    private function bootErrorHandler(): void
    {
        $errorHandler = $this->container->resolve(ErrorHandler::class);
        $errorHandler->register();
    }

    /**
     * Get the container instance
     *
     * @return Container The container instance
     */
    public function getContainer(): Container
    {
        return $this->container;
    }
}
