<?php

return [
    'default_policy' => \Mzshovon\LaravelTryCatcher\Policies\ExceptionPolicy::LOG,
    'prod_policy' => \Mzshovon\LaravelTryCatcher\Policies\ExceptionPolicy::PROD_SAFE,
    'log_channel' => env('EXCEPTION_GUARD_LOG_CHANNEL', 'stack'),
    'integrations' => [
        'sentry' => env('SENTRY_DSN') ? true : false,
        'slack' => env('EXCEPTION_GUARD_SLACK_WEBHOOK') ? true : false,
    ],
    'safe_prod_guard' => 1
];
