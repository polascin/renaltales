<?php

declare(strict_types=1);

namespace RenalTales\Core;

use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use React\Stream\ReadableResourceStream;
use React\Stream\WritableResourceStream;
use React\MySQL\ConnectionInterface;
use RenalTales\Core\PatchedMysqlFactory;
use React\MySQL\QueryResult;
use Exception;
use Closure;

/**
 * Async Manager
 *
 * Handles asynchronous operations using ReactPHP for better performance
 * and non-blocking I/O operations. Manages database connections, promises,
 * and async task execution.
 *
 * @package RenalTales\Core
 * @version 2025.3.1.dev
 * @author Ľubomír Polaščín
 */
class AsyncManager
{
    /**
     * @var LoopInterface Event loop instance
     */
    private LoopInterface $loop;

    /**
     * @var PatchedMysqlFactory MySQL factory for async connections
     */
    private PatchedMysqlFactory $mysqlFactory;

    /**
     * @var ConnectionInterface|null Async database connection
     */
    private ?ConnectionInterface $asyncConnection = null;

    /**
     * @var array<string, mixed> Database configuration
     */
    private array $dbConfig;

    /**
     * @var Logger|null Application logger
     */
    private ?Logger $logger = null;

    /**
     * @var array<string, callable> Registered task handlers
     */
    private array $taskHandlers = [];

    /**
     * @var array<string, PromiseInterface> Active promises
     */
    private array $activePromises = [];

    /**
     * @var bool Whether the manager is initialized
     */
    private bool $initialized = false;

    /**
     * Constructor
     *
     * @param array<string, mixed> $dbConfig Database configuration
     * @param Logger|null $logger Application logger
     */
    public function __construct(array $dbConfig, ?Logger $logger = null)
    {
        $this->dbConfig = $dbConfig;
        $this->logger = $logger;
        $this->loop = Loop::get();
        $this->mysqlFactory = new PatchedMysqlFactory();
    }

    /**
     * Initialize async manager
     *
     * @return PromiseInterface<void>
     */
    public function initialize(): PromiseInterface
    {
        if ($this->initialized) {
            return \React\Promise\resolve();
        }

        return $this->setupAsyncConnection()
            ->then(function () {
                $this->initialized = true;
                $this->log('Async manager initialized successfully');
            })
            ->otherwise(function (Exception $e) {
                $this->log('Async manager initialization failed: ' . $e->getMessage(), 'error');
                throw $e;
            });
    }

    /**
     * Setup async database connection
     *
     * @return PromiseInterface<ConnectionInterface>
     */
    private function setupAsyncConnection(): PromiseInterface
    {
        $connectionName = $this->dbConfig['default'] ?? 'mysql';
        $config = $this->dbConfig['connections'][$connectionName] ?? [];

        if (empty($config)) {
            return \React\Promise\reject(new Exception("Database connection configuration for '{$connectionName}' not found"));
        }

        $dsn = sprintf(
            '%s:%s@%s:%d/%s',
            $config['user'] ?? 'root',
            $config['password'] ?? '',
            $config['host'] ?? 'localhost',
            $config['port'] ?? 3306,
            $config['dbname'] ?? 'renaltales'
        );

        return $this->mysqlFactory->createConnection($dsn)
            ->then(function (ConnectionInterface $connection) {
                $this->asyncConnection = $connection;
                $this->log('Async database connection established');
                return $connection;
            });
    }

    /**
     * Execute async query
     *
     * @param string $query SQL query
     * @param array<mixed> $params Query parameters
     * @return PromiseInterface<QueryResult>
     */
    public function query(string $query, array $params = []): PromiseInterface
    {
        if (!$this->asyncConnection) {
            return \React\Promise\reject(new Exception('Async connection not established'));
        }

        return $this->asyncConnection->query($query, $params)
            ->then(function (QueryResult $result) use ($query) {
                $this->log('Async query executed successfully: ' . substr($query, 0, 100));
                return $result;
            })
            ->otherwise(function (Exception $e) use ($query) {
                $this->log('Async query failed: ' . $query . ' - ' . $e->getMessage(), 'error');
                throw $e;
            });
    }

    /**
     * Execute multiple async queries in parallel
     *
     * @param array<string> $queries Array of SQL queries
     * @return PromiseInterface<array<QueryResult>>
     */
    public function queryMultiple(array $queries): PromiseInterface
    {
        $promises = [];
        
        foreach ($queries as $query) {
            $promises[] = $this->query($query);
        }

        return \React\Promise\all($promises)
            ->then(function (array $results) {
                $this->log('Multiple async queries executed successfully');
                return $results;
            });
    }

    /**
     * Execute async task
     *
     * @param string $taskName Task name
     * @param array<mixed> $params Task parameters
     * @return PromiseInterface<mixed>
     */
    public function executeTask(string $taskName, array $params = []): PromiseInterface
    {
        if (!isset($this->taskHandlers[$taskName])) {
            return \React\Promise\reject(new Exception("Task handler for '{$taskName}' not found"));
        }

        $handler = $this->taskHandlers[$taskName];
        $promiseId = uniqid('task_' . $taskName . '_');

        try {
            $promise = $handler($params);
            
            if (!$promise instanceof PromiseInterface) {
                $promise = \React\Promise\resolve($promise);
            }

            $this->activePromises[$promiseId] = $promise;

            return $promise
                ->then(function ($result) use ($promiseId, $taskName) {
                    unset($this->activePromises[$promiseId]);
                    $this->log("Task '{$taskName}' completed successfully");
                    return $result;
                })
                ->otherwise(function (Exception $e) use ($promiseId, $taskName) {
                    unset($this->activePromises[$promiseId]);
                    $this->log("Task '{$taskName}' failed: " . $e->getMessage(), 'error');
                    throw $e;
                });
        } catch (Exception $e) {
            return \React\Promise\reject($e);
        }
    }

    /**
     * Register task handler
     *
     * @param string $taskName Task name
     * @param callable $handler Task handler
     * @return void
     */
    public function registerTask(string $taskName, callable $handler): void
    {
        $this->taskHandlers[$taskName] = $handler;
        $this->log("Task handler registered: {$taskName}");
    }

    /**
     * Schedule recurring task
     *
     * @param string $taskName Task name
     * @param int $interval Interval in seconds
     * @param array<mixed> $params Task parameters
     * @return void
     */
    public function scheduleRecurring(string $taskName, int $interval, array $params = []): void
    {
        $this->loop->addPeriodicTimer($interval, function () use ($taskName, $params) {
            $this->executeTask($taskName, $params)
                ->otherwise(function (Exception $e) use ($taskName) {
                    $this->log("Recurring task '{$taskName}' failed: " . $e->getMessage(), 'error');
                });
        });

        $this->log("Recurring task scheduled: {$taskName} (interval: {$interval}s)");
    }

    /**
     * Schedule one-time task
     *
     * @param string $taskName Task name
     * @param int $delay Delay in seconds
     * @param array<mixed> $params Task parameters
     * @return void
     */
    public function scheduleOnce(string $taskName, int $delay, array $params = []): void
    {
        $this->loop->addTimer($delay, function () use ($taskName, $params) {
            $this->executeTask($taskName, $params)
                ->otherwise(function (Exception $e) use ($taskName) {
                    $this->log("Scheduled task '{$taskName}' failed: " . $e->getMessage(), 'error');
                });
        });

        $this->log("One-time task scheduled: {$taskName} (delay: {$delay}s)");
    }

    /**
     * Execute async file operation
     *
     * @param string $operation Operation type (read, write, etc.)
     * @param string $filePath File path
     * @param mixed $data Data for write operations
     * @return PromiseInterface<mixed>
     */
    public function fileOperation(string $operation, string $filePath, $data = null): PromiseInterface
    {
        switch ($operation) {
            case 'read':
                return $this->readFileAsync($filePath);
            case 'write':
                return $this->writeFileAsync($filePath, $data);
            default:
                return \React\Promise\reject(new Exception("Unknown file operation: {$operation}"));
        }
    }

    /**
     * Read file asynchronously
     *
     * @param string $filePath File path
     * @return PromiseInterface<string>
     */
    private function readFileAsync(string $filePath): PromiseInterface
    {
        return new Promise(function (callable $resolve, callable $reject) use ($filePath) {
            if (!file_exists($filePath)) {
                $reject(new Exception("File not found: {$filePath}"));
                return;
            }

            $stream = new ReadableResourceStream(fopen($filePath, 'r'), $this->loop);
            $content = '';

            $stream->on('data', function ($chunk) use (&$content) {
                $content .= $chunk;
            });

            $stream->on('end', function () use (&$content, $resolve) {
                $resolve($content);
            });

            $stream->on('error', function (Exception $e) use ($reject) {
                $reject($e);
            });
        });
    }

    /**
     * Write file asynchronously
     *
     * @param string $filePath File path
     * @param string $data Data to write
     * @return PromiseInterface<bool>
     */
    private function writeFileAsync(string $filePath, string $data): PromiseInterface
    {
        return new Promise(function (callable $resolve, callable $reject) use ($filePath, $data) {
            $resource = fopen($filePath, 'w');
            if (!$resource) {
                $reject(new Exception("Cannot open file for writing: {$filePath}"));
                return;
            }

            $stream = new WritableResourceStream($resource, $this->loop);

            $stream->write($data);
            $stream->end();

            $stream->on('finish', function () use ($resolve) {
                $resolve(true);
            });

            $stream->on('error', function (Exception $e) use ($reject) {
                $reject($e);
            });
        });
    }

    /**
     * Create timeout promise
     *
     * @param int $seconds Timeout in seconds
     * @return PromiseInterface<void>
     */
    public function timeout(int $seconds): PromiseInterface
    {
        return new Promise(function (callable $resolve) use ($seconds) {
            $this->loop->addTimer($seconds, function () use ($resolve) {
                $resolve();
            });
        });
    }

    /**
     * Race multiple promises
     *
     * @param array<PromiseInterface> $promises Array of promises
     * @return PromiseInterface<mixed>
     */
    public function race(array $promises): PromiseInterface
    {
        return \React\Promise\race($promises);
    }

    /**
     * Get active promises count
     *
     * @return int Number of active promises
     */
    public function getActivePromisesCount(): int
    {
        return count($this->activePromises);
    }

    /**
     * Get registered tasks
     *
     * @return array<string> Array of registered task names
     */
    public function getRegisteredTasks(): array
    {
        return array_keys($this->taskHandlers);
    }

    /**
     * Cancel all active promises
     *
     * @return void
     */
    public function cancelAllPromises(): void
    {
        foreach ($this->activePromises as $promise) {
            if (method_exists($promise, 'cancel')) {
                $promise->cancel();
            }
        }
        
        $this->activePromises = [];
        $this->log('All active promises cancelled');
    }

    /**
     * Get event loop
     *
     * @return LoopInterface Event loop instance
     */
    public function getLoop(): LoopInterface
    {
        return $this->loop;
    }

    /**
     * Check if manager is initialized
     *
     * @return bool True if initialized, false otherwise
     */
    public function isInitialized(): bool
    {
        return $this->initialized;
    }

    /**
     * Log a message
     *
     * @param string $message Log message
     * @param string $level Log level
     * @return void
     */
    private function log(string $message, string $level = 'info'): void
    {
        if ($this->logger) {
            $this->logger->log($level, $message);
        }
    }

    /**
     * Close async connection and cleanup
     *
     * @return void
     */
    public function close(): void
    {
        if ($this->asyncConnection) {
            $this->asyncConnection->close();
            $this->asyncConnection = null;
        }

        $this->cancelAllPromises();
        $this->initialized = false;
        $this->log('Async manager closed');
    }
}
