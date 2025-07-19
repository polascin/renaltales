<?php

declare(strict_types=1);

namespace RenalTales\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use RenalTales\Core\Application;
use RenalTales\Core\Container;
use RenalTales\Core\DatabaseManager;
use RenalTales\Tests\Traits\DatabaseTrait;
use RenalTales\Tests\Traits\MockTrait;
use RenalTales\Tests\Traits\AssertionTrait;
use Faker\Factory as FakerFactory;
use Faker\Generator as Faker;

/**
 * Base TestCase class for all PHPUnit tests
 *
 * Provides common functionality for all test classes including:
 * - Application setup and teardown
 * - Database management
 * - Mocking utilities
 * - Custom assertions
 * - Test data factories
 */
abstract class TestCase extends BaseTestCase
{
    use DatabaseTrait;
    use MockTrait;
    use AssertionTrait;

    /**
     * @var Application|null The test application instance
     */
    protected static ?Application $app = null;

    /**
     * @var Container|null The dependency injection container
     */
    protected ?Container $container = null;

    /**
     * @var Faker The Faker instance for generating test data
     */
    protected Faker $faker;

    /**
     * @var array Test data cache
     */
    protected array $testData = [];

    /**
     * Initialize the test application
     */
    public static function initializeTestApplication(): void
    {
        if (static::$app === null) {
            static::$app = new Application();
            static::$app->bootstrap();
        }
    }

    /**
     * Clean up the test application
     */
    public static function cleanupTestApplication(): void
    {
        if (static::$app !== null) {
            static::$app->shutdown();
            static::$app = null;
        }
    }

    /**
     * Set up the test case
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Initialize faker
        $this->faker = FakerFactory::create();

        // Get container instance
        $this->container = static::$app->getContainer();

        // Set up database for testing
        $this->setUpDatabase();

        // Clear test data
        $this->testData = [];
    }

    /**
     * Tear down the test case
     */
    protected function tearDown(): void
    {
        // Clean up database
        $this->tearDownDatabase();

        // Clear test data
        $this->testData = [];

        parent::tearDown();
    }

    /**
     * Get the application instance
     */
    protected function getApplication(): Application
    {
        return static::$app;
    }

    /**
     * Get the container instance
     */
    protected function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Get a service from the container
     *
     * @param string $abstract The service identifier
     * @return mixed The service instance
     */
    protected function getService(string $abstract)
    {
        return $this->container->resolve($abstract);
    }

    /**
     * Create a test double for a service
     *
     * @param string $className The class name to mock
     * @param array $methods Methods to mock
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function createServiceMock(string $className, array $methods = []): \PHPUnit\Framework\MockObject\MockObject
    {
        return $this->createMock($className, $methods);
    }

    /**
     * Replace a service in the container with a mock
     *
     * @param string $abstract The service identifier
     * @param \PHPUnit\Framework\MockObject\MockObject $mock The mock object
     */
    protected function replaceService(string $abstract, \PHPUnit\Framework\MockObject\MockObject $mock): void
    {
        $this->container->bind($abstract, $mock);
    }

    /**
     * Assert that a service is registered in the container
     *
     * @param string $abstract The service identifier
     * @param string $message Custom assertion message
     */
    protected function assertServiceRegistered(string $abstract, string $message = ''): void
    {
        $this->assertTrue(
            $this->container->bound($abstract),
            $message ?: "Service '{$abstract}' is not registered in the container"
        );
    }

    /**
     * Assert that a service is not registered in the container
     *
     * @param string $abstract The service identifier
     * @param string $message Custom assertion message
     */
    protected function assertServiceNotRegistered(string $abstract, string $message = ''): void
    {
        $this->assertFalse(
            $this->container->bound($abstract),
            $message ?: "Service '{$abstract}' should not be registered in the container"
        );
    }

    /**
     * Get test data by key
     *
     * @param string $key The data key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed The test data
     */
    protected function getTestData(string $key, $default = null)
    {
        return $this->testData[$key] ?? $default;
    }

    /**
     * Set test data by key
     *
     * @param string $key The data key
     * @param mixed $value The data value
     */
    protected function setTestData(string $key, $value): void
    {
        $this->testData[$key] = $value;
    }

    /**
     * Clear all test data
     */
    protected function clearTestData(): void
    {
        $this->testData = [];
    }

    /**
     * Create a temporary file for testing
     *
     * @param string $content File content
     * @param string $extension File extension
     * @return string The temporary file path
     */
    protected function createTempFile(string $content = '', string $extension = '.tmp'): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'renaltales_test_') . $extension;
        file_put_contents($tempFile, $content);

        // Register for cleanup
        register_shutdown_function(function () use ($tempFile) {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        });

        return $tempFile;
    }

    /**
     * Get the path to test fixtures
     *
     * @param string $fixture The fixture name
     * @return string The fixture path
     */
    protected function getFixture(string $fixture): string
    {
        return __DIR__ . '/Fixtures/' . $fixture;
    }

    /**
     * Load fixture data
     *
     * @param string $fixture The fixture name
     * @return mixed The fixture data
     */
    protected function loadFixture(string $fixture)
    {
        $path = $this->getFixture($fixture);

        if (!file_exists($path)) {
            throw new \RuntimeException("Fixture file not found: {$path}");
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);

        switch ($extension) {
            case 'json':
                return json_decode(file_get_contents($path), true);
            case 'php':
                return require $path;
            default:
                return file_get_contents($path);
        }
    }

    /**
     * Skip test if condition is not met
     *
     * @param bool $condition The condition to check
     * @param string $message Skip message
     */
    protected function skipIf(bool $condition, string $message = ''): void
    {
        if ($condition) {
            $this->markTestSkipped($message ?: 'Test skipped due to condition');
        }
    }

    /**
     * Skip test if extension is not loaded
     *
     * @param string $extension The extension name
     * @param string $message Skip message
     */
    protected function skipIfExtensionNotLoaded(string $extension, string $message = ''): void
    {
        $this->skipIf(
            !extension_loaded($extension),
            $message ?: "Test skipped because '{$extension}' extension is not loaded"
        );
    }
}
