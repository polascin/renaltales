<?php

declare(strict_types=1);

namespace RenalTales\Tests\Unit\Core;

use RenalTales\Tests\TestCase;
use RenalTales\Core\Application;
use RenalTales\Core\Container;
use RenalTales\Core\ServiceProvider;
use RenalTales\Controllers\ApplicationController;

/**
 * Unit tests for the Application class
 */
class ApplicationTest extends TestCase
{
    private Application $application;

    protected function setUp(): void
    {
        parent::setUp();
        $this->application = new Application();
    }

    /**
     * Test that the application can be instantiated
     */
    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(Application::class, $this->application);
    }

    /**
     * Test that the application has a container
     */
    public function testHasContainer(): void
    {
        $container = $this->application->getContainer();
        $this->assertInstanceOf(Container::class, $container);
    }

    /**
     * Test that the application has a service provider
     */
    public function testHasServiceProvider(): void
    {
        $serviceProvider = $this->application->getServiceProvider();
        $this->assertInstanceOf(ServiceProvider::class, $serviceProvider);
    }

    /**
     * Test that the application can be bootstrapped
     */
    public function testCanBeBootstrapped(): void
    {
        $this->assertFalse($this->application->isBootstrapped());

        $result = $this->application->bootstrap();

        $this->assertInstanceOf(Application::class, $result);
        $this->assertTrue($this->application->isBootstrapped());
    }

    /**
     * Test that bootstrapping is idempotent
     */
    public function testBootstrapIsIdempotent(): void
    {
        $this->application->bootstrap();
        $this->assertTrue($this->application->isBootstrapped());

        $result = $this->application->bootstrap();

        $this->assertInstanceOf(Application::class, $result);
        $this->assertTrue($this->application->isBootstrapped());
    }

    /**
     * Test that the application controller is available after bootstrapping
     */
    public function testApplicationControllerAvailableAfterBootstrap(): void
    {
        $this->application->bootstrap();

        $controller = $this->application->getApplicationController();
        $this->assertInstanceOf(ApplicationController::class, $controller);
    }

    /**
     * Test that getting application controller before bootstrap throws exception
     */
    public function testGetApplicationControllerBeforeBootstrapThrowsException(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Application must be bootstrapped before accessing the application controller');

        $this->application->getApplicationController();
    }

    /**
     * Test that running before bootstrap throws exception
     */
    public function testRunBeforeBootstrapThrowsException(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Application must be bootstrapped before running');

        $this->application->run();
    }

    /**
     * Test that the application version is returned
     */
    public function testReturnsVersion(): void
    {
        $version = $this->application->getVersion();
        $this->assertIsString($version);
        $this->assertNotEmpty($version);
    }

    /**
     * Test that services can be retrieved from the application
     */
    public function testCanRetrieveServices(): void
    {
        $this->application->bootstrap();

        $this->assertTrue($this->application->has(Container::class));
        $container = $this->application->get(Container::class);
        $this->assertInstanceOf(Container::class, $container);
    }

    /**
     * Test that the application can be shut down
     */
    public function testCanBeShutDown(): void
    {
        $this->application->bootstrap();

        // Should not throw exception
        $this->application->shutdown();

        $this->assertTrue($this->application->isBootstrapped());
    }

    /**
     * Test that shutdown can be called on non-bootstrapped application
     */
    public function testShutdownCanBeCalledOnNonBootstrappedApplication(): void
    {
        // Should not throw exception
        $this->application->shutdown();

        $this->assertFalse($this->application->isBootstrapped());
    }
}
