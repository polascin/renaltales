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

if (!function_exists('__')) {
    /**
     * Translate a string using the application's language manager.
     *
     * @param string $key
     * @param array $parameters
     * @return string
     */
    function __(string $key, array $parameters = []): string
    {
        static $languageManager = null;
        
        if ($languageManager === null) {
            $config = new RenalTales\Core\Config(dirname(__DIR__) . '/config/config.php');
            $languageManager = new RenalTales\Core\LanguageManager($config);
            $languageManager->initialize();
        }
        
        return $languageManager->translate($key, $parameters);
    }
}

if (!function_exists('lang')) {
    /**
     * Alias for the __ function for convenience.
     *
     * @param string $key
     * @param array $parameters
     * @return string
     */
    function lang(string $key, array $parameters = []): string
    {
        return __($key, $parameters);
    }
}
