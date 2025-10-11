<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Test Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration for the package test suite.
    | It defines test environments, database settings, and other
    | testing-related configurations.
    |
    */

    'test_environment' => env('TEST_ENV', 'testing'),

    'database' => [
        'default' => env('DB_CONNECTION', 'mysql'),
        'connections' => [
            'sqlite' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ],
            'mysql' => [
                'driver' => 'mysql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => env('DB_DATABASE', 'trycatcher_test'),
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', ''),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
            ],
        ],
    ],

    'logging' => [
        'default' => env('LOG_CHANNEL', 'stack'),
        'channels' => [
            'stack' => [
                'driver' => 'stack',
                'channels' => ['single'],
            ],
            'single' => [
                'driver' => 'single',
                'path' => storage_path('logs/test.log'),
                'level' => 'debug',
            ],
        ],
    ],

    'exception_guard' => [
        'default_policy' => \Mzshovon\LaravelTryCatcher\Policies\ExceptionPolicy::THROW,
        'prod_policy' => \Mzshovon\LaravelTryCatcher\Policies\ExceptionPolicy::PROD_SAFE,
        'log_channel' => env('EXCEPTION_GUARD_LOG_CHANNEL', 'stack'),
        'integrations' => [
            'sentry' => env('SENTRY_DSN') ? true : false,
            'slack' => env('EXCEPTION_GUARD_SLACK_WEBHOOK') ? true : false,
        ],
        'safe_prod_guard' => 1,
    ],

    'performance' => [
        'max_execution_time' => 30, // seconds
        'max_memory_usage' => '128M',
        'max_iterations' => 1000,
    ],

    'coverage' => [
        'threshold' => 80, // percentage
        'exclude' => [
            'src/Migrations',
            'src/Helpers/helpers.php',
        ],
    ],

    'quality' => [
        'psr12_compliance' => true,
        'security_checks' => true,
        'performance_checks' => true,
        'documentation_checks' => true,
    ],
];

