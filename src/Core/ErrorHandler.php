<?php
declare(strict_types=1);

namespace RenalTales\Core;

use Exception;

class ErrorHandler
{
    private static bool $isDebugMode = false;
    private static array $config = [];

    public static function initialize(array $config = []): void
    {
        self::$config = $config;
        self::$isDebugMode = $config['app']['debug'] ?? false;
        
        // Set global exception handler
        set_exception_handler([self::class, 'handleException']);
        
        // Set error handler for non-fatal errors
        set_error_handler([self::class, 'handleError']);
    }

    public static function handleException(\Throwable $e): void
    {
        // Determine status code
        $statusCode = self::getHttpStatusCode($e);
        
        // Log the error
        self::logError($e, $statusCode);

        // Set HTTP status code
        if (!headers_sent()) {
            http_response_code($statusCode);
        }

        // Show appropriate error page
        if (self::$isDebugMode) {
            self::renderDebugPage($e);
        } else {
            self::renderErrorPage($statusCode);
        }

        exit;
    }

    public static function handleError(int $severity, string $message, string $file, int $line): bool
    {
        // Convert error to exception and handle it
        $exception = new \ErrorException($message, 0, $severity, $file, $line);
        self::logError($exception, 500);
        
        // Return false to allow normal error handling for non-fatal errors
        return false;
    }

    private static function getHttpStatusCode(\Throwable $e): int
    {
        // Check if it's a custom HTTP exception
        if (method_exists($e, 'getStatusCode')) {
            return $e->getStatusCode();
        }
        
        // Check for common exception types
        $className = get_class($e);
        switch ($className) {
            case 'InvalidArgumentException':
                return 400;
            case 'UnauthorizedException':
                return 401;
            case 'ForbiddenException':
                return 403;
            case 'NotFoundException':
                return 404;
            case 'MethodNotAllowedException':
                return 405;
            case 'TooManyRequestsException':
                return 429;
            default:
                return 500;
        }
    }

    private static function logError(\Throwable $e, int $statusCode): void
    {
        $logDir = __DIR__ . '/../../storage/logs';
        
        // Ensure log directory exists
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Create daily log files
        $logFile = $logDir . '/error-' . date('Y-m-d') . '.log';
        
        // Format log message with detailed information
        $message = sprintf(
            "[%s] [%s] %s: %s\nFile: %s:%d\nTrace:\n%s\nRequest: %s %s\nUser Agent: %s\nIP: %s\n%s\n",
            date('Y-m-d H:i:s'),
            $statusCode,
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString(),
            $_SERVER['REQUEST_METHOD'] ?? 'CLI',
            $_SERVER['REQUEST_URI'] ?? 'N/A',
            $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
            $_SERVER['REMOTE_ADDR'] ?? 'N/A',
            str_repeat('-', 80)
        );
        
        // Write to log file
        file_put_contents($logFile, $message, FILE_APPEND | LOCK_EX);
        
        // Also log to PHP error log for critical errors
        if ($statusCode >= 500) {
            error_log(sprintf(
                "[RenalTales] %s: %s in %s:%d",
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));
        }
    }

    private static function renderDebugPage(\Throwable $e): void
    {
        $title = get_class($e) . ': ' . $e->getMessage();
        $file = $e->getFile();
        $line = $e->getLine();
        $trace = $e->getTraceAsString();
        
        echo "<!DOCTYPE html>
<html>
<head>
    <title>Error Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .error-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #e74c3c; margin-bottom: 20px; }
        .error-details { background: #f8f9fa; padding: 15px; border-left: 4px solid #e74c3c; margin: 20px 0; }
        .trace { background: #2c3e50; color: #ecf0f1; padding: 15px; border-radius: 4px; white-space: pre-wrap; font-family: monospace; }
    </style>
</head>
<body>
    <div class='error-container'>
        <h1>Debug Mode Error</h1>
        <div class='error-details'>
            <h3>{$title}</h3>
            <p><strong>File:</strong> {$file}</p>
            <p><strong>Line:</strong> {$line}</p>
        </div>
        <h3>Stack Trace:</h3>
        <div class='trace'>{$trace}</div>
    </div>
</body>
</html>";
    }

    private static function renderErrorPage(int $statusCode): void
    {
        // Try to load error page from views directory first
        $errorView = __DIR__ . '/../../app/Views/errors/' . $statusCode . '.php';
        if (file_exists($errorView)) {
            include $errorView;
            return;
        }
        
        // Fallback to simple error pages
        switch ($statusCode) {
            case 404:
                self::render404Page();
                break;
            case 500:
            default:
                self::render500Page();
                break;
        }
    }

    private static function render404Page(): void
    {
        echo "<!DOCTYPE html>
<html>
<head>
    <title>Page Not Found - RenalTales</title>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .container { max-width: 600px; margin: 100px auto; text-align: center; padding: 40px 20px; }
        h1 { font-size: 72px; margin: 0; font-weight: 300; }
        h2 { font-size: 24px; margin: 20px 0; font-weight: 400; }
        p { font-size: 16px; line-height: 1.6; margin: 20px 0; }
        .btn { display: inline-block; padding: 12px 30px; background: rgba(255,255,255,0.2); color: white; text-decoration: none; border-radius: 25px; margin: 20px 10px; transition: all 0.3s; }
        .btn:hover { background: rgba(255,255,255,0.3); transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class='container'>
        <h1>404</h1>
        <h2>Page Not Found</h2>
        <p>Sorry, the page you are looking for doesn't exist or has been moved.</p>
        <a href='/' class='btn'>Go Home</a>
        <a href='/stories' class='btn'>Browse Stories</a>
    </div>
</body>
</html>";
    }

    private static function render500Page(): void
    {
        echo "<!DOCTYPE html>
<html>
<head>
    <title>Server Error - RenalTales</title>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background: linear-gradient(135deg, #ff7e5f 0%, #feb47b 100%); color: white; }
        .container { max-width: 600px; margin: 100px auto; text-align: center; padding: 40px 20px; }
        h1 { font-size: 72px; margin: 0; font-weight: 300; }
        h2 { font-size: 24px; margin: 20px 0; font-weight: 400; }
        p { font-size: 16px; line-height: 1.6; margin: 20px 0; }
        .btn { display: inline-block; padding: 12px 30px; background: rgba(255,255,255,0.2); color: white; text-decoration: none; border-radius: 25px; margin: 20px 10px; transition: all 0.3s; }
        .btn:hover { background: rgba(255,255,255,0.3); transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class='container'>
        <h1>500</h1>
        <h2>Server Error</h2>
        <p>Something went wrong on our end. Our team has been notified and is working to fix this issue.</p>
        <a href='/' class='btn'>Go Home</a>
        <a href='javascript:history.back()' class='btn'>Go Back</a>
    </div>
</body>
</html>";
    }

    public static function handleNotFound(): void
    {
        $exception = new Exception('Page not found');
        self::logError($exception, 404);
        
        if (!headers_sent()) {
            http_response_code(404);
        }
        
        if (self::$isDebugMode) {
            self::renderDebugPage($exception);
        } else {
            self::render404Page();
        }
        
        exit;
    }
}

