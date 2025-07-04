<?php
declare(strict_types=1);

namespace RenalTales\Core;

/**
 * Flash Messages System
 * 
 * Handles success, error, warning, and info messages across redirects
 * with support for multiple messages per type and auto-cleanup.
 */
class FlashMessages
{
    private const SESSION_KEY = '_flash_messages';
    
    // Message types
    public const SUCCESS = 'success';
    public const ERROR = 'error';
    public const WARNING = 'warning';
    public const INFO = 'info';
    
    /**
     * Add a success message
     */
    public static function success(string $message): void
    {
        self::add(self::SUCCESS, $message);
    }
    
    /**
     * Add an error message
     */
    public static function error(string $message): void
    {
        self::add(self::ERROR, $message);
    }
    
    /**
     * Add a warning message
     */
    public static function warning(string $message): void
    {
        self::add(self::WARNING, $message);
    }
    
    /**
     * Add an info message
     */
    public static function info(string $message): void
    {
        self::add(self::INFO, $message);
    }
    
    /**
     * Add a message of specified type
     */
    public static function add(string $type, string $message): void
    {
        if (!isset($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }
        
        if (!isset($_SESSION[self::SESSION_KEY][$type])) {
            $_SESSION[self::SESSION_KEY][$type] = [];
        }
        
        $_SESSION[self::SESSION_KEY][$type][] = [
            'message' => htmlspecialchars($message, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'timestamp' => time()
        ];
    }
    
    /**
     * Add multiple messages of the same type
     */
    public static function addMultiple(string $type, array $messages): void
    {
        foreach ($messages as $message) {
            self::add($type, $message);
        }
    }
    
    /**
     * Add validation errors
     */
    public static function validationErrors(array $errors): void
    {
        foreach ($errors as $field => $message) {
            self::error($message);
        }
    }
    
    /**
     * Get all messages of a specific type
     */
    public static function get(string $type): array
    {
        $messages = $_SESSION[self::SESSION_KEY][$type] ?? [];
        
        // Clear messages after retrieval
        unset($_SESSION[self::SESSION_KEY][$type]);
        
        // Clean up empty session array
        if (empty($_SESSION[self::SESSION_KEY])) {
            unset($_SESSION[self::SESSION_KEY]);
        }
        
        return array_column($messages, 'message');
    }
    
    /**
     * Get all messages (all types)
     */
    public static function getAll(): array
    {
        $allMessages = $_SESSION[self::SESSION_KEY] ?? [];
        $result = [];
        
        foreach ($allMessages as $type => $messages) {
            $result[$type] = array_column($messages, 'message');
        }
        
        // Clear all messages
        unset($_SESSION[self::SESSION_KEY]);
        
        return $result;
    }
    
    /**
     * Check if there are any messages of a specific type
     */
    public static function has(string $type): bool
    {
        return !empty($_SESSION[self::SESSION_KEY][$type]);
    }
    
    /**
     * Check if there are any messages at all
     */
    public static function hasAny(): bool
    {
        return !empty($_SESSION[self::SESSION_KEY]);
    }
    
    /**
     * Peek at messages without removing them
     */
    public static function peek(string $type): array
    {
        $messages = $_SESSION[self::SESSION_KEY][$type] ?? [];
        return array_column($messages, 'message');
    }
    
    /**
     * Peek at all messages without removing them
     */
    public static function peekAll(): array
    {
        $allMessages = $_SESSION[self::SESSION_KEY] ?? [];
        $result = [];
        
        foreach ($allMessages as $type => $messages) {
            $result[$type] = array_column($messages, 'message');
        }
        
        return $result;
    }
    
    /**
     * Clear all messages of a specific type
     */
    public static function clear(string $type): void
    {
        unset($_SESSION[self::SESSION_KEY][$type]);
        
        if (empty($_SESSION[self::SESSION_KEY])) {
            unset($_SESSION[self::SESSION_KEY]);
        }
    }
    
    /**
     * Clear all messages
     */
    public static function clearAll(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
    }
    
    /**
     * Get message count for a specific type
     */
    public static function count(string $type): int
    {
        return count($_SESSION[self::SESSION_KEY][$type] ?? []);
    }
    
    /**
     * Get total message count
     */
    public static function countAll(): int
    {
        $total = 0;
        foreach (($_SESSION[self::SESSION_KEY] ?? []) as $messages) {
            $total += count($messages);
        }
        return $total;
    }
    
    /**
     * Clean up old messages (older than 1 hour)
     */
    public static function cleanup(): void
    {
        if (!isset($_SESSION[self::SESSION_KEY])) {
            return;
        }
        
        $cutoff = time() - 3600; // 1 hour ago
        $cleaned = false;
        
        foreach ($_SESSION[self::SESSION_KEY] as $type => $messages) {
            $_SESSION[self::SESSION_KEY][$type] = array_filter(
                $messages,
                fn($msg) => $msg['timestamp'] > $cutoff
            );
            
            if (empty($_SESSION[self::SESSION_KEY][$type])) {
                unset($_SESSION[self::SESSION_KEY][$type]);
                $cleaned = true;
            }
        }
        
        if ($cleaned && empty($_SESSION[self::SESSION_KEY])) {
            unset($_SESSION[self::SESSION_KEY]);
        }
    }
    
    /**
     * Render messages as HTML
     */
    public static function render(): string
    {
        $messages = self::getAll();
        
        if (empty($messages)) {
            return '';
        }
        
        $html = '';
        
        foreach ($messages as $type => $typeMessages) {
            if (!empty($typeMessages)) {
                $html .= self::renderType($type, $typeMessages);
            }
        }
        
        return $html;
    }
    
    /**
     * Render messages of a specific type as HTML
     */
    public static function renderType(string $type, array $messages): string
    {
        if (empty($messages)) {
            return '';
        }
        
        $alertClass = self::getBootstrapClass($type);
        $icon = self::getIcon($type);
        
        $html = '<div class="alert alert-' . $alertClass . ' alert-dismissible fade show" role="alert">';
        $html .= '<i class="' . $icon . ' me-2"></i>';
        
        if (count($messages) === 1) {
            $html .= $messages[0];
        } else {
            $html .= '<ul class="mb-0">';
            foreach ($messages as $message) {
                $html .= '<li>' . $message . '</li>';
            }
            $html .= '</ul>';
        }
        
        $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Get Bootstrap alert class for message type
     */
    private static function getBootstrapClass(string $type): string
    {
        return match ($type) {
            self::SUCCESS => 'success',
            self::ERROR => 'danger',
            self::WARNING => 'warning',
            self::INFO => 'info',
            default => 'secondary'
        };
    }
    
    /**
     * Get icon class for message type
     */
    private static function getIcon(string $type): string
    {
        return match ($type) {
            self::SUCCESS => 'fas fa-check-circle',
            self::ERROR => 'fas fa-exclamation-circle',
            self::WARNING => 'fas fa-exclamation-triangle',
            self::INFO => 'fas fa-info-circle',
            default => 'fas fa-bell'
        };
    }
    
    /**
     * Convert messages to JSON format for AJAX responses
     */
    public static function toJson(): string
    {
        $messages = self::peekAll();
        return json_encode($messages);
    }
    
    /**
     * Set flash messages from JSON (for AJAX)
     */
    public static function fromJson(string $json): void
    {
        $messages = json_decode($json, true);
        
        if (is_array($messages)) {
            foreach ($messages as $type => $typeMessages) {
                if (is_array($typeMessages)) {
                    foreach ($typeMessages as $message) {
                        self::add($type, $message);
                    }
                }
            }
        }
    }
}
