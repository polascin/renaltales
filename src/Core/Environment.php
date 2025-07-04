<?php
declare(strict_types=1);

namespace RenalTales\Core;

class Environment
{
    private static bool $loaded = false;
    private static array $cache = [];

    public static function load(string $path = null): void
    {
        if (self::$loaded) {
            return;
        }

        if ($path === null) {
            $path = dirname(__DIR__, 2) . '/.env';
        }

        if (!file_exists($path)) {
            throw new \RuntimeException("Environment file not found: {$path}");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            if (strpos($line, '#') === 0) {
                continue;
            }

            if (strpos($line, '=') === false) {
                continue;
            }

            list($name, $value) = array_map('trim', explode('=', $line, 2));
            
            if (empty($name)) {
                continue;
            }

            // Remove quotes if present
            if (preg_match('/^"(.+)"$/', $value, $matches)) {
                $value = $matches[1];
            } elseif (preg_match("/^'(.+)'$/", $value, $matches)) {
                $value = $matches[1];
            }

            // Convert special values
            switch (strtolower($value)) {
                case 'true':
                case '(true)':
                    $value = true;
                    break;
                case 'false':
                case '(false)':
                    $value = false;
                    break;
                case 'null':
                case '(null)':
                    $value = null;
                    break;
            }

            self::$cache[$name] = $value;
            putenv("{$name}={$value}");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }

        self::$loaded = true;
    }

    public static function get(string $key, $default = null)
    {
        self::ensureLoaded();

        // First check our cache
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        // Then check environment
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }

        return $default;
    }

    public static function set(string $key, $value): void
    {
        self::ensureLoaded();

        self::$cache[$key] = $value;
        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }

    public static function has(string $key): bool
    {
        self::ensureLoaded();
        return isset(self::$cache[$key]) || getenv($key) !== false;
    }

    private static function ensureLoaded(): void
    {
        if (!self::$loaded) {
            self::load();
        }
    }
}
