<?php

/**
 * Renal Tales - Database Configuration
 * 
 * This file contains database configuration settings.
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    */
    'default' => 'mysql',

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    */
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'renaltales'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => env('DB_PREFIX', ''),
            'strict' => true,
            'engine' => null,
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        ],

        'testing' => [
            'driver' => 'mysql',
            'host' => env('TEST_DB_HOST', 'localhost'),
            'port' => env('TEST_DB_PORT', '3306'),
            'database' => env('TEST_DB_DATABASE', 'renaltales_test'),
            'username' => env('TEST_DB_USERNAME', 'root'),
            'password' => env('TEST_DB_PASSWORD', ''),
            'charset' => env('TEST_DB_CHARSET', 'utf8mb4'),
            'collation' => env('TEST_DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => env('TEST_DB_PREFIX', ''),
            'strict' => true,
            'engine' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    */
    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases (if needed in future)
    |--------------------------------------------------------------------------
    */
    'redis' => [
        'client' => 'phpredis',
        'options' => [
            'cluster' => 'redis',
            'prefix' => 'renaltales_database_',
        ],
        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],
        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],
    ],
];

/**
 * Helper function to get environment variables
 * This would be replaced by a proper environment loader in a full framework
 */
function env($key, $default = null) {
    $value = getenv($key);
    if ($value === false) {
        return $default;
    }
    
    // Convert boolean strings
    if (in_array(strtolower($value), ['true', 'false'])) {
        return strtolower($value) === 'true';
    }
    
    // Convert null string
    if (strtolower($value) === 'null') {
        return null;
    }
    
    return $value;
}
