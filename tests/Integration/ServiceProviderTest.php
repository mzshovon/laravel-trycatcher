<?php

namespace Mzshovon\LaravelTryCatcher\Tests\Integration;

use Mzshovon\LaravelTryCatcher\Tests\TestCase;
use Mzshovon\LaravelTryCatcher\ExceptionGuardServiceProvider;
use Mzshovon\LaravelTryCatcher\Services\ExceptionGuard;
use Mzshovon\LaravelTryCatcher\Facades\Guard;
use Mzshovon\LaravelTryCatcher\Policies\ExceptionPolicy;
use Exception;

class ServiceProviderTest extends TestCase
{
    public function test_service_provider_registers_exception_guard()
    {
        $this->assertTrue($this->app->bound(ExceptionGuard::class));
        $this->assertInstanceOf(ExceptionGuard::class, $this->app->make(ExceptionGuard::class));
    }

    public function test_service_provider_registers_alias()
    {
        $this->assertTrue($this->app->bound('exception-guard'));
        $this->assertInstanceOf(ExceptionGuard::class, $this->app->make('exception-guard'));
    }

    public function test_service_provider_merges_config()
    {
        $config = $this->app['config']->get('exception-guard');

        $this->assertIsArray($config);
        $this->assertArrayHasKey('default_policy', $config);
        $this->assertArrayHasKey('prod_policy', $config);
        $this->assertArrayHasKey('log_channel', $config);
        $this->assertArrayHasKey('integrations', $config);
        $this->assertArrayHasKey('safe_prod_guard', $config);
    }

    public function test_service_provider_publishes_config()
    {
        $provider = new ExceptionGuardServiceProvider($this->app);

        $this->assertArrayHasKey('config', $provider->publishes());
        $this->assertArrayHasKey('migrations', $provider->publishes());
    }

    public function test_service_provider_loads_helpers()
    {
        // Test that the guarded helper function is available
        $this->assertTrue(function_exists('guarded'));
        $this->assertTrue(function_exists('isProdEnv'));
    }

    public function test_facade_works_correctly()
    {
        $result = Guard::run(
            fn() => 'success',
            ExceptionPolicy::THROW
        );

        $this->assertEquals('success', $result);
    }

    public function test_facade_handles_exceptions()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test exception');

        Guard::run(
            fn() => throw new Exception('Test exception'),
            ExceptionPolicy::THROW
        );
    }

    public function test_service_is_singleton()
    {
        $instance1 = $this->app->make(ExceptionGuard::class);
        $instance2 = $this->app->make(ExceptionGuard::class);

        $this->assertSame($instance1, $instance2);
    }
}

