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
     * Translate a string using the application's language class.
     *
     * @param string $key
     * @param array $parameters
     * @return string
     */
    function __(string $key, array $parameters = []): string
    {
        static $language = null;
        
        if ($language === null) {
            // Include the Language class if not already loaded
            if (!class_exists('Language')) {
                require_once dirname(__DIR__) . '/app/Core/Language.php';
            }
            $language = new Language();
        }
        
        $translation = $language->translate($key);
        
        // Handle parameters if provided
        if (!empty($parameters)) {
            foreach ($parameters as $param => $value) {
                $translation = str_replace(":{$param}", $value, $translation);
            }
        }
        
        return $translation;
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
