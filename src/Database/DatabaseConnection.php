<?php
declare(strict_types=1);

namespace RenalTales\Database;

use PDO;
use PDOException;
use RenalTales\Core\Config;

class DatabaseConnection
{
    private static ?PDO $instance = null;
    private static ?Config $config = null;

    private function __construct()
    {
        // Private constructor to prevent instantiation
    }

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::$instance = self::createConnection();
        }

        return self::$instance;
    }

    private static function createConnection(): PDO
    {
        try {
            $config = self::getConfig();
            
            $dsn = sprintf(
                "%s:host=%s;dbname=%s;charset=%s",
                $config->get('database.driver'),
                $config->get('database.host'),
                $config->get('database.database'),
                $config->get('database.charset')
            );

            $pdo = new PDO(
                $dsn,
                $config->get('database.username'),
                $config->get('database.password'),
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_PERSISTENT => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ]
            );

            return $pdo;
        } catch (PDOException $e) {
            throw new PDOException(
                "Database connection failed: " . $e->getMessage(),
                (int)$e->getCode()
            );
        }
    }

    private static function getConfig(): Config
    {
        if (self::$config === null) {
            self::$config = new Config(dirname(__DIR__, 2) . '/config/config.php');
        }

        return self::$config;
    }

    public static function reconnect(): PDO
    {
        self::$instance = null;
        return self::getInstance();
    }

    public static function closeConnection(): void
    {
        self::$instance = null;
    }

    // Prevent cloning
    private function __clone()
    {
    }

    // Prevent unserialization
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }
}
