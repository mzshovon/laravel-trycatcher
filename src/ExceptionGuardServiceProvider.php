<?php

namespace Mzshovon\LaravelTryCatcher;

use Illuminate\Support\ServiceProvider;
use Mzshovon\LaravelTryCatcher\Services\ExceptionGuard;

class ExceptionGuardServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/exception-guard.php', 'exception-guard');

        $this->app->singleton(ExceptionGuard::class, function($app){
            return new ExceptionGuard($app['config']->get('exception-guard'));
        });

        // optional alias
        $this->app->alias(ExceptionGuard::class, 'exception-guard');
    }

    /**
     * Bootstrap the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $timestamp_part = date('Y_m_d_His');
        // publish config and migrations
        $this->publishes([
            __DIR__.'/config/exception-guard.php' => config_path('exception-guard.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/migrations/2025_10_01_193656_create_error_logs_table.php' =>
                database_path("migrations/{$timestamp_part}_create_error_logs_table.php")
            ], 'migrations');

        // load helpers
        if (file_exists(__DIR__ . '/Helpers/helpers.php')) {
            require_once __DIR__ . '/Helpers/helpers.php';
        }
    }
}
