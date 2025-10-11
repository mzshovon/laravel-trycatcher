<?php

namespace Mzshovon\LaravelTryCatcher\Tests\Feature;

use Mzshovon\LaravelTryCatcher\Tests\TestCase;
use Mzshovon\LaravelTryCatcher\Policies\ExceptionPolicy;
use Mzshovon\LaravelTryCatcher\Models\ErrorLog;
use Exception;

class GuardedHelperTest extends TestCase
{
    public function test_guarded_helper_with_closure()
    {
        $result = guarded(
            fn() => 'success',
            ExceptionPolicy::THROW
        );

        $this->assertEquals('success', $result);
    }

    public function test_guarded_helper_with_closure_that_throws()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test exception');

        guarded(
            fn() => throw new Exception('Test exception'),
            ExceptionPolicy::THROW
        );
    }

    public function test_guarded_helper_with_string_callable()
    {
        $result = guarded(
            "Mzshovon\LaravelTryCatcher\Tests\Feature\TestClass::staticMethod",
            ExceptionPolicy::THROW
        );

        $this->assertEquals('static method result', $result);
    }

    public function test_guarded_helper_with_array_callable()
    {
        $testClass = new TestClass();
        $result = guarded(
            [$testClass, 'instanceMethod'],
            ExceptionPolicy::THROW
        );

        $this->assertEquals('instance method result', $result);
    }

    public function test_guarded_helper_with_options()
    {
        $result = guarded(
            fn() => throw new Exception('Test exception'),
            ExceptionPolicy::LOG,
            [
                'level' => 'critical',
                'context' => ['user_id' => 123],
                'status' => 422
            ]
        );

        $this->assertEquals(422, $result->getStatusCode());
        $errorLog = ErrorLog::where('message', 'Test exception')->latest()->first();
        $this->assertEquals('critical', $errorLog->level);
        $this->assertEquals(['user_id' => 123], $errorLog->context);
    }

    public function test_is_prod_env_helper()
    {
        // Test production environment
        $this->app['config']->set('app.env', 'production');
        $this->assertTrue(isProdEnv());

        // Test non-production environment
        $this->app['config']->set('app.env', 'testing');
        $this->assertFalse(isProdEnv());

        $this->app['config']->set('app.env', 'local');
        $this->assertFalse(isProdEnv());
    }
}

class TestClass
{
    public static function staticMethod()
    {
        return 'static method result';
    }

    public function instanceMethod()
    {
        return 'instance method result';
    }
}

