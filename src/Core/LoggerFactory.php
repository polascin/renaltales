<?php

declare(strict_types=1);

namespace RenalTales\Core;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Processor\UidProcessor;
use Monolog\Processor\WebProcessor;
use Monolog\Processor\PsrLogMessageProcessor;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\JsonFormatter;
use Monolog\Level;

/**
 * Logger Factory
 *
 * Creates and configures logger instances with various handlers based on configuration.
 * Supports multiple output destinations, log rotation, and different formatting options.
 *
 * @package RenalTales\Core
 * @version 2025.3.1.dev
 * @author Ľubomír Polaščín
 */
class LoggerFactory
{
    /**
     * Create a logger instance
     *
     * @param string $name Logger name
     * @param array $config Logger configuration
     * @return Logger
     */
    public static function create(string $name = 'RenalTales', array $config = []): Logger
    {
        $logger = new Logger($name);
        
        // Default configuration
        $defaultConfig = [
            'level' => Level::Debug,
            'file' => null,
            'max_files' => 5,
            'format' => 'line',
            'include_stacktraces' => true,
            'bubble' => true,
            'handlers' => ['file'],
            'processors' => ['uid', 'web', 'psr'],
            'date_format' => 'Y-m-d H:i:s',
            'output_format' => "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
            'ignore_dot_files' => true,
            'file_permission' => 0664,
            'use_locking' => false,
        ];
        
        $config = array_merge($defaultConfig, $config);
        
        // Add handlers based on configuration
        self::addHandlers($logger, $config);
        
        // Add processors based on configuration
        self::addProcessors($logger, $config);
        
        return $logger;
    }
    
    /**
     * Add handlers to logger
     *
     * @param Logger $logger
     * @param array $config
     * @return void
     */
    private static function addHandlers(Logger $logger, array $config): void
    {
        foreach ($config['handlers'] as $handlerType) {
            switch ($handlerType) {
                case 'file':
                    if ($config['file']) {
                        $handler = new StreamHandler(
                            $config['file'],
                            $config['level'],
                            $config['bubble'],
                            $config['file_permission'],
                            $config['use_locking']
                        );
                        self::setFormatter($handler, $config);
                        $logger->pushHandler($handler);
                    }
                    break;
                    
                case 'rotating':
                    if ($config['file']) {
                        $handler = new RotatingFileHandler(
                            $config['file'],
                            $config['max_files'],
                            $config['level'],
                            $config['bubble'],
                            $config['file_permission'],
                            $config['use_locking']
                        );
                        self::setFormatter($handler, $config);
                        $logger->pushHandler($handler);
                    }
                    break;
                    
                case 'error_log':
                    $handler = new ErrorLogHandler(
                        ErrorLogHandler::OPERATING_SYSTEM,
                        $config['level'],
                        $config['bubble']
                    );
                    self::setFormatter($handler, $config);
                    $logger->pushHandler($handler);
                    break;
                    
                case 'syslog':
                    $handler = new SyslogHandler(
                        'RenalTales',
                        LOG_USER,
                        $config['level'],
                        $config['bubble']
                    );
                    self::setFormatter($handler, $config);
                    $logger->pushHandler($handler);
                    break;
                    
                case 'stderr':
                    $handler = new StreamHandler(
                        'php://stderr',
                        $config['level'],
                        $config['bubble']
                    );
                    self::setFormatter($handler, $config);
                    $logger->pushHandler($handler);
                    break;
                    
                case 'stdout':
                    $handler = new StreamHandler(
                        'php://stdout',
                        $config['level'],
                        $config['bubble']
                    );
                    self::setFormatter($handler, $config);
                    $logger->pushHandler($handler);
                    break;
            }
        }
    }
    
    /**
     * Add processors to logger
     *
     * @param Logger $logger
     * @param array $config
     * @return void
     */
    private static function addProcessors(Logger $logger, array $config): void
    {
        foreach ($config['processors'] as $processorType) {
            switch ($processorType) {
                case 'uid':
                    $logger->pushProcessor(new UidProcessor());
                    break;
                    
                case 'web':
                    if (isset($_SERVER['REQUEST_METHOD'])) {
                        $logger->pushProcessor(new WebProcessor());
                    }
                    break;
                    
                case 'psr':
                    $logger->pushProcessor(new PsrLogMessageProcessor());
                    break;
                    
                case 'memory':
                    $logger->pushProcessor(function ($record) {
                        $record['extra']['memory_usage'] = memory_get_usage(true);
                        $record['extra']['memory_peak'] = memory_get_peak_usage(true);
                        return $record;
                    });
                    break;
                    
                case 'git':
                    $logger->pushProcessor(function ($record) {
                        if (file_exists('.git/HEAD')) {
                            $head = trim(file_get_contents('.git/HEAD'));
                            if (strpos($head, 'ref:') === 0) {
                                $ref = substr($head, 5);
                                if (file_exists('.git/' . $ref)) {
                                    $record['extra']['git_commit'] = trim(file_get_contents('.git/' . $ref));
                                }
                            } else {
                                $record['extra']['git_commit'] = $head;
                            }
                        }
                        return $record;
                    });
                    break;
            }
        }
    }
    
    /**
     * Set formatter for handler
     *
     * @param mixed $handler
     * @param array $config
     * @return void
     */
    private static function setFormatter($handler, array $config): void
    {
        switch ($config['format']) {
            case 'json':
                $formatter = new JsonFormatter();
                break;
                
            case 'line':
            default:
                $formatter = new LineFormatter(
                    $config['output_format'],
                    $config['date_format'],
                    false,
                    true,
                    true
                );
                $formatter->includeStacktraces($config['include_stacktraces']);
                break;
        }
        
        $handler->setFormatter($formatter);
    }
    
    /**
     * Create application logger with environment-specific configuration
     *
     * @param string $environment
     * @param string $logPath
     * @return Logger
     */
    public static function createAppLogger(string $environment, string $logPath): Logger
    {
        $config = [
            'file' => $logPath,
            'level' => $environment === 'production' ? Level::Info : Level::Debug,
            'handlers' => ['rotating'],
            'max_files' => 10,
            'processors' => ['uid', 'web', 'psr', 'memory'],
            'format' => $environment === 'production' ? 'json' : 'line',
            'include_stacktraces' => $environment !== 'production',
        ];
        
        return self::create('RenalTales', $config);
    }
    
    /**
     * Create error logger for error handling
     *
     * @param string $environment
     * @param string $logPath
     * @return Logger
     */
    public static function createErrorLogger(string $environment, string $logPath): Logger
    {
        $errorLogPath = str_replace('.log', '-error.log', $logPath);
        
        $config = [
            'file' => $errorLogPath,
            'level' => Level::Warning,
            'handlers' => ['rotating', 'error_log'],
            'max_files' => 30,
            'processors' => ['uid', 'web', 'psr', 'memory', 'git'],
            'format' => 'json',
            'include_stacktraces' => true,
        ];
        
        return self::create('RenalTales-Error', $config);
    }
    
    /**
     * Create database logger
     *
     * @param string $environment
     * @param string $logPath
     * @return Logger
     */
    public static function createDatabaseLogger(string $environment, string $logPath): Logger
    {
        $dbLogPath = str_replace('.log', '-database.log', $logPath);
        
        $config = [
            'file' => $dbLogPath,
            'level' => $environment === 'production' ? Level::Error : Level::Debug,
            'handlers' => ['rotating'],
            'max_files' => 7,
            'processors' => ['uid', 'psr', 'memory'],
            'format' => 'line',
            'include_stacktraces' => false,
        ];
        
        return self::create('RenalTales-DB', $config);
    }
    
    /**
     * Create security logger
     *
     * @param string $environment
     * @param string $logPath
     * @return Logger
     */
    public static function createSecurityLogger(string $environment, string $logPath): Logger
    {
        $securityLogPath = str_replace('.log', '-security.log', $logPath);
        
        $config = [
            'file' => $securityLogPath,
            'level' => Level::Info,
            'handlers' => ['rotating', 'syslog'],
            'max_files' => 365,
            'processors' => ['uid', 'web', 'psr'],
            'format' => 'json',
            'include_stacktraces' => false,
        ];
        
        return self::create('RenalTales-Security', $config);
    }
    
    /**
     * Create performance logger
     *
     * @param string $environment
     * @param string $logPath
     * @return Logger
     */
    public static function createPerformanceLogger(string $environment, string $logPath): Logger
    {
        $perfLogPath = str_replace('.log', '-performance.log', $logPath);
        
        $config = [
            'file' => $perfLogPath,
            'level' => $environment === 'production' ? Level::Warning : Level::Info,
            'handlers' => ['rotating'],
            'max_files' => 7,
            'processors' => ['uid', 'memory'],
            'format' => 'json',
            'include_stacktraces' => false,
        ];
        
        return self::create('RenalTales-Performance', $config);
    }
}
