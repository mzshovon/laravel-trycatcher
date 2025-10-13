<?php

namespace Mzshovon\LaravelTryCatcher\Tests\Unit;

use Mzshovon\LaravelTryCatcher\Tests\TestCase;
use Mzshovon\LaravelTryCatcher\Handler\PolicyHandler;
use Mzshovon\LaravelTryCatcher\Policies\ExceptionPolicy;
use Mzshovon\LaravelTryCatcher\Models\ErrorLog;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class PolicyHandlerTest extends TestCase
{
    public function test_log_policy_logs_exception_without_trace()
    {
        $exception = new Exception('Test exception');
        $handler = new PolicyHandler($exception, ExceptionPolicy::LOG, ['level' => 'error']);

        $result = $handler->resolvePolicy();

        $this->assertInstanceOf(Response::class, $result);
        $this->assertTrue($result->getData()->error);
        $this->assertEquals('Test exception', $result->getData()->message);

        $errorLog = ErrorLog::where('message', 'Test exception')->first();
        $this->assertNotNull($errorLog);
        $this->assertNull($errorLog->trace);
    }

    public function test_log_with_trace_policy_logs_exception_with_trace()
    {
        $exception = new Exception('Test exception with log trace');
        $handler = new PolicyHandler($exception, ExceptionPolicy::LOG_WITH_TRACE);

        $result = $handler->resolvePolicy();

        $this->assertInstanceOf(Response::class, $result);
        $this->assertTrue($result->getData()->error);

        $errorLog = ErrorLog::where('message', 'Test exception with log trace');
        $this->assertNotNull($errorLog->latest()->first());
        $this->assertNotNull($errorLog->latest()->first()->trace);
        $errorLog->delete();
    }

    public function test_exception_only_policy_returns_formatted_response()
    {
        $exception = new Exception('Test exception');
        $handler = new PolicyHandler($exception, ExceptionPolicy::EXCEPTION_ONLY);

        $result = $handler->resolvePolicy();

        $this->assertInstanceOf(Response::class, $result);
        $this->assertTrue($result->getData()->error);
        $this->assertEquals('Test exception', $result->getData()->message);
    }

    public function test_prod_safe_policy_returns_safe_response()
    {
        $exception = new Exception('Test exception');
        $handler = new PolicyHandler(
            $exception,
            ExceptionPolicy::PROD_SAFE,
            ['message' => 'Safe error message']
        );

        $result = $handler->resolvePolicy();

        $this->assertInstanceOf(Response::class, $result);
        $this->assertTrue($result->getData()->error);
        $this->assertEquals('Safe error message', $result->getData()->message);
    }

    public function test_log_and_throw_policy_logs_then_throws()
    {
        $exception = new Exception('Test exception');
        $handler = new PolicyHandler($exception, ExceptionPolicy::LOG_AND_THROW);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test exception');

        $handler->resolvePolicy();

        // Verify error was logged before throwing
        $this->assertDatabaseHas('error_logs', [
            'message' => 'Test exception'
        ]);
    }

    public function test_throw_policy_throws_exception()
    {
        $exception = new Exception('Test exception');
        $handler = new PolicyHandler($exception, ExceptionPolicy::THROW);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test exception');

        $handler->resolvePolicy();
    }

    public function test_handler_uses_custom_status_code()
    {
        $exception = new Exception('Test Handler Status exception');
        $handler = new PolicyHandler(
            $exception,
            ExceptionPolicy::LOG,
            ['status' => 422]
        );

        $result = $handler->resolvePolicy();

        $this->assertEquals(422, $result->getStatusCode());

        ErrorLog::where('message', 'Test Handler Status exception')->delete();
    }

    public function test_handler_uses_custom_context()
    {
        $exception = new Exception('Test Handler exception');
        $handler = new PolicyHandler(
            $exception,
            ExceptionPolicy::LOG,
            ['context' => ['user_id' => 123, 'action' => 'test']]
        );

        $handler->resolvePolicy();

        $errorLog = ErrorLog::where('message', 'Test Handler exception');

        $this->assertEquals(['user_id' => 123, 'action' => 'test'], $errorLog->latest()->first()->context);

        $errorLog->delete();
    }

    public function test_handler_fallback_to_file_log_when_db_fails()
    {
        // Mock ErrorLog to throw exception
        $this->mock(ErrorLog::class, function ($mock) {
            $mock->shouldReceive('create')->andThrow(new Exception('DB Error'));
        });

        $exception = new Exception('Test exception');
        $handler = new PolicyHandler($exception, ExceptionPolicy::LOG);

        // Should not throw exception even if DB logging fails
        $result = $handler->resolvePolicy();

        $this->assertInstanceOf(Response::class, $result);
        $this->assertTrue($result->getData()->error);

        ErrorLog::where('message', 'Test exception')->delete();
    }
}

