<?php
declare(strict_types=1);

use RenalTales\Core\Environment;

if (!function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function env(string $key, $default = null)
    {
        return Environment::get($key, $default);
    }
}

if (!function_exists('config')) {
    /**
     * Get a configuration value.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function config(string $key, $default = null)
    {
        static $config = null;
        
        if ($config === null) {
            $config = new RenalTales\Core\Config(dirname(__DIR__) . '/config/config.php');
        }
        
        return $config->get($key, $default);
    }
}
