<?php

namespace Mzshovon\LaravelTryCatcher\Tests\Unit;

use Mzshovon\LaravelTryCatcher\Tests\TestCase;
use Mzshovon\LaravelTryCatcher\Services\ExceptionGuard;
use Mzshovon\LaravelTryCatcher\Policies\ExceptionPolicy;
use Mzshovon\LaravelTryCatcher\Models\ErrorLog;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class ExceptionGuardTest extends TestCase
{
    protected ExceptionGuard $guard;

    protected function setUp(): void
    {
        parent::setUp();
        $this->guard = new ExceptionGuard();
    }

    public function test_guard_runs_successful_callable()
    {
        $result = $this->guard->run(
            fn() => 'success',
            ExceptionPolicy::THROW
        );

        $this->assertEquals('success', $result);
    }

    public function test_guard_handles_exception_with_throw_policy()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test exception');

        $this->guard->run(
            fn() => throw new Exception('Test exception'),
            ExceptionPolicy::THROW
        );
    }

    public function test_guard_handles_exception_with_log_policy()
    {
        $result = $this->guard->run(
            fn() => throw new Exception('Test exception'),
            ExceptionPolicy::LOG,
            ['level' => 'error']
        );

        $this->assertInstanceOf(Response::class, $result);
        $this->assertTrue($result->getData()->error);
        $this->assertEquals('Test exception', $result->getData()->message);

        // Check if error was logged to database
        $this->assertDatabaseHas('error_logs', [
            'message' => 'Test exception',
            'level' => 'error'
        ]);
    }

    public function test_guard_handles_exception_with_log_with_trace_policy()
    {
        $result = $this->guard->run(
            fn() => throw new Exception('Test guard handle log trace exception'),
            ExceptionPolicy::LOG_WITH_TRACE
        );

        $this->assertInstanceOf(Response::class, $result);
        $this->assertTrue($result->getData()->error);
        $this->assertEquals('Test guard handle log trace exception', $result->getData()->message);

        // Check if error was logged with trace
        $errorLog = ErrorLog::where('message', 'Test guard handle log trace exception');
        $this->assertNotNull($errorLog->latest()->first()->trace);

        $errorLog->delete();
    }

    public function test_guard_handles_exception_with_exception_only_policy()
    {
        $result = $this->guard->run(
            fn() => throw new Exception('Test exception'),
            ExceptionPolicy::EXCEPTION_ONLY
        );

        $this->assertInstanceOf(Response::class, $result);
        $this->assertTrue($result->getData()->error);
        $this->assertEquals('Test exception', $result->getData()->message);
    }

    public function test_guard_handles_exception_with_prod_safe_policy()
    {
        $result = $this->guard->run(
            fn() => throw new Exception('Test exception'),
            ExceptionPolicy::PROD_SAFE,
            ['message' => 'Safe error message']
        );

        $this->assertInstanceOf(Response::class, $result);
        $this->assertTrue($result->getData()->error);
        $this->assertEquals('Safe error message', $result->getData()->message);
    }

    public function test_guard_handles_exception_with_log_and_throw_policy()
    {
        // First check that error is logged
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test exception');

        $this->guard->run(
            fn() => throw new Exception('Test exception'),
            ExceptionPolicy::LOG_AND_THROW
        );

        // Verify error was logged before throwing
        $this->assertDatabaseHas('error_logs', [
            'message' => 'Test exception'
        ]);
    }

    public function test_guard_passes_options_to_handler()
    {
        $result = $this->guard->run(
            fn() => throw new Exception('Test exception'),
            ExceptionPolicy::LOG,
            [
                'level' => 'critical',
                'context' => ['user_id' => 123],
                'status' => 422
            ]
        );

        $this->assertEquals(422, $result->getStatusCode());

        $errorLog = ErrorLog::where('message', 'Test exception')->first();
        $this->assertEquals('critical', $errorLog->level);
        $this->assertEquals(['user_id' => 123], $errorLog->context);
    }
}

