<?php

declare(strict_types=1);

namespace RenalTales\Core;

/**
 * Simple File-Based Logger
 *
 * This class provides basic logging functionality without database dependency
 *
 * @package RenalTales
 * @version 2025.v3.0dev
 * @author Ľubomír Polaščín
 */
class Logger
{
    private string $logFile;
    
    public function __construct(string $logFile = null)
    {
        $this->logFile = $logFile ?? (APP_ROOT . '/storage/logs/app.log');
        
        // Ensure log directory exists
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Log a message with timestamp
     */
    public function log(string $level, string $message, array $context = []): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $contextString = !empty($context) ? json_encode($context) : '';
        $logEntry = "[{$timestamp}] [{$level}] {$message} {$contextString}" . PHP_EOL;
        
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Log info message
     */
    public function info(string $message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }
    
    /**
     * Log warning message
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log('WARNING', $message, $context);
    }
    
    /**
     * Log error message
     */
    public function error(string $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }
    
    /**
     * Log debug message
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log('DEBUG', $message, $context);
    }
}
