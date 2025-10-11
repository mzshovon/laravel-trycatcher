<?php

namespace Mzshovon\LaravelTryCatcher\Tests\Unit;

use Mzshovon\LaravelTryCatcher\Tests\TestCase;
use Mzshovon\LaravelTryCatcher\Models\ErrorLog;

class ErrorLogTest extends TestCase
{
    public function test_error_log_model_fillable_fields()
    {
        $errorLog = new ErrorLog();
        $fillable = $errorLog->getFillable();

        $this->assertContains('level', $fillable);
        $this->assertContains('message', $fillable);
        $this->assertContains('trace', $fillable);
        $this->assertContains('context', $fillable);
    }

    public function test_error_log_model_casts()
    {
        $errorLog = new ErrorLog();
        $casts = $errorLog->getCasts();

        $this->assertArrayHasKey('context', $casts);
        $this->assertEquals('array', $casts['context']);
    }

    public function test_error_log_can_be_created()
    {
        $errorLog = ErrorLog::create([
            'level' => 'error',
            'message' => 'Test error message',
            'trace' => 'Stack trace here',
            'context' => ['user_id' => 123]
        ]);

        $this->assertInstanceOf(ErrorLog::class, $errorLog);
        $this->assertEquals('error', $errorLog->level);
        $this->assertEquals('Test error message', $errorLog->message);
        $this->assertEquals('Stack trace here', $errorLog->trace);
        $this->assertEquals(['user_id' => 123], $errorLog->context);
    }

    public function test_error_log_context_is_casted_to_array()
    {
        $errorLog = ErrorLog::create([
            'level' => 'error',
            'message' => 'Test error message',
            'context' => ['key' => 'value']
        ]);

        $this->assertIsArray($errorLog->context);
        $this->assertEquals(['key' => 'value'], $errorLog->context);
    }

    public function test_error_log_uses_has_factory_trait()
    {
        $this->assertTrue(method_exists(ErrorLog::class, 'factory'));
    }
}

