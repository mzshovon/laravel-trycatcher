<?php

namespace Mzshovon\LaravelTryCatcher;

use Illuminate\Support\ServiceProvider;
use Mzshovon\LaravelTryCatcher\Services\ExceptionGuard;

class ExceptionGuardServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/exception-guard.php', 'exception-guard');

        $this->app->singleton(ExceptionGuard::class, function($app){
            return new ExceptionGuard($app['config']->get('exception-guard'));
        });

        // optional alias
        $this->app->alias(ExceptionGuard::class, 'exception-guard');
    }

    public function boot()
    {
        // publish config and migrations
        $this->publishes([
            __DIR__.'/config/exception-guard.php' => config_path('exception-guard.php'),
        ], 'config');

        $this->publishes([__DIR__.'Migrations/' => database_path('migrations')], 'migrations');

        // load helpers
        if (file_exists(__DIR__ . '/Helpers/helpers.php')) {
            require_once __DIR__ . '/Helpers/helpers.php';
        }
    }
}
