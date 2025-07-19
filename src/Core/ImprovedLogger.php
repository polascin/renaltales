<?php

declare(strict_types=1);

namespace RenalTales\Core;

use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonologLogger;

/**
 * Improved Logger
 *
 * Provides advanced logging capabilities using Monolog, enabling logging to various outputs.
 *
 * @package RenalTales\Core
 * @version 2025.v3.1.dev
 */
class ImprovedLogger extends MonologLogger
{
    /**
     * Constructor
     *
     * @param string $logFile Path to the log file
     */
    public function __construct(string $logFile)
    {
        parent::__construct('RenalTalesLogger');

        // Create a handler
        $streamHandler = new StreamHandler($logFile, MonologLogger::DEBUG);

        // Add the handler to the logger
        $this->pushHandler($streamHandler);
    }
}
