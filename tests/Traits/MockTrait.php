<?php

declare(strict_types=1);

namespace RenalTales\Tests\Traits;

use PHPUnit\Framework\MockObject\MockObject;

/**
 * Mock trait for creating test doubles
 */
trait MockTrait
{
    /**
     * Create a mock object
     */
    protected function createMock(string $className, array $methods = []): MockObject
    {
        $builder = $this->getMockBuilder($className);
        
        if (!empty($methods)) {
            $builder->onlyMethods($methods);
        }
        
        return $builder->getMock();
    }

    /**
     * Create a partial mock object
     */
    protected function createPartialMock(string $className, array $methods): MockObject
    {
        return $this->getMockBuilder($className)
            ->onlyMethods($methods)
            ->getMock();
    }

    /**
     * Create a stub object
     */
    protected function createStub(string $className): MockObject
    {
        return $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Create a spy object (calls original methods by default)
     */
    protected function createSpy(string $className): MockObject
    {
        return $this->getMockBuilder($className)
            ->enableProxyingToOriginalMethods()
            ->getMock();
    }

    /**
     * Mock a method to return a specific value
     */
    protected function mockMethod(MockObject $mock, string $method, $returnValue = null): void
    {
        $mock->expects($this->any())
            ->method($method)
            ->willReturn($returnValue);
    }

    /**
     * Mock a method to throw an exception
     */
    protected function mockMethodToThrow(MockObject $mock, string $method, \Throwable $exception): void
    {
        $mock->expects($this->any())
            ->method($method)
            ->willThrowException($exception);
    }

    /**
     * Mock a method to be called once
     */
    protected function mockMethodOnce(MockObject $mock, string $method, $returnValue = null): void
    {
        $mock->expects($this->once())
            ->method($method)
            ->willReturn($returnValue);
    }

    /**
     * Mock a method to be called never
     */
    protected function mockMethodNever(MockObject $mock, string $method): void
    {
        $mock->expects($this->never())
            ->method($method);
    }

    /**
     * Mock a method to be called exactly n times
     */
    protected function mockMethodExactly(MockObject $mock, string $method, int $times, $returnValue = null): void
    {
        $mock->expects($this->exactly($times))
            ->method($method)
            ->willReturn($returnValue);
    }

    /**
     * Mock a method with specific arguments
     */
    protected function mockMethodWithArgs(MockObject $mock, string $method, array $args, $returnValue = null): void
    {
        $mock->expects($this->any())
            ->method($method)
            ->with(...$args)
            ->willReturn($returnValue);
    }

    /**
     * Mock a method to return different values on consecutive calls
     */
    protected function mockMethodConsecutive(MockObject $mock, string $method, array $returnValues): void
    {
        $mock->expects($this->any())
            ->method($method)
            ->willReturnOnConsecutiveCalls(...$returnValues);
    }

    /**
     * Mock a method to execute a callback
     */
    protected function mockMethodCallback(MockObject $mock, string $method, callable $callback): void
    {
        $mock->expects($this->any())
            ->method($method)
            ->willReturnCallback($callback);
    }

    /**
     * Verify that a method was called
     */
    protected function verifyMethodCalled(MockObject $mock, string $method): void
    {
        $mock->expects($this->atLeastOnce())
            ->method($method);
    }

    /**
     * Verify that a method was called with specific arguments
     */
    protected function verifyMethodCalledWith(MockObject $mock, string $method, array $args): void
    {
        $mock->expects($this->atLeastOnce())
            ->method($method)
            ->with(...$args);
    }

    /**
     * Create a mock for a service class
     */
    protected function createServiceMock(string $serviceClass, array $methods = []): MockObject
    {
        return $this->createMock($serviceClass, $methods);
    }

    /**
     * Create a mock for a repository class
     */
    protected function createRepositoryMock(string $repositoryClass): MockObject
    {
        return $this->createMock($repositoryClass, [
            'find',
            'findBy',
            'findOneBy',
            'findAll',
            'count',
            'save',
            'remove'
        ]);
    }

    /**
     * Create a mock for an entity class
     */
    protected function createEntityMock(string $entityClass): MockObject
    {
        return $this->createMock($entityClass);
    }

    /**
     * Create a mock for a controller class
     */
    protected function createControllerMock(string $controllerClass): MockObject
    {
        return $this->createMock($controllerClass);
    }

    /**
     * Create a mock for a view class
     */
    protected function createViewMock(string $viewClass): MockObject
    {
        return $this->createMock($viewClass, ['render']);
    }

    /**
     * Create a mock for a cache interface
     */
    protected function createCacheMock(): MockObject
    {
        return $this->createMock('Psr\Cache\CacheItemPoolInterface', [
            'getItem',
            'getItems',
            'hasItem',
            'clear',
            'deleteItem',
            'deleteItems',
            'save',
            'saveDeferred',
            'commit'
        ]);
    }

    /**
     * Create a mock for a logger interface
     */
    protected function createLoggerMock(): MockObject
    {
        return $this->createMock('Psr\Log\LoggerInterface', [
            'emergency',
            'alert',
            'critical',
            'error',
            'warning',
            'notice',
            'info',
            'debug',
            'log'
        ]);
    }
}
