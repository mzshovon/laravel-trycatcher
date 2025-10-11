<?php

namespace Mzshovon\LaravelTryCatcher\Tests\Unit;

use Mzshovon\LaravelTryCatcher\Tests\TestCase;
use Mzshovon\LaravelTryCatcher\Policies\ExceptionPolicy;

class ExceptionPolicyTest extends TestCase
{
    public function test_exception_policy_enum_values()
    {
        $this->assertEquals('throw', ExceptionPolicy::THROW->value);
        $this->assertEquals('log', ExceptionPolicy::LOG->value);
        $this->assertEquals('log_with_trace', ExceptionPolicy::LOG_WITH_TRACE->value);
        $this->assertEquals('exception_only', ExceptionPolicy::EXCEPTION_ONLY->value);
        $this->assertEquals('prod_safe', ExceptionPolicy::PROD_SAFE->value);
        $this->assertEquals('log_and_throw', ExceptionPolicy::LOG_AND_THROW->value);
    }

    public function test_exception_policy_cases()
    {
        $cases = ExceptionPolicy::cases();

        $this->assertCount(6, $cases);
        $this->assertContains(ExceptionPolicy::THROW, $cases);
        $this->assertContains(ExceptionPolicy::LOG, $cases);
        $this->assertContains(ExceptionPolicy::LOG_WITH_TRACE, $cases);
        $this->assertContains(ExceptionPolicy::EXCEPTION_ONLY, $cases);
        $this->assertContains(ExceptionPolicy::PROD_SAFE, $cases);
        $this->assertContains(ExceptionPolicy::LOG_AND_THROW, $cases);
    }

    public function test_exception_policy_from_string()
    {
        $this->assertEquals(ExceptionPolicy::THROW, ExceptionPolicy::from('throw'));
        $this->assertEquals(ExceptionPolicy::LOG, ExceptionPolicy::from('log'));
        $this->assertEquals(ExceptionPolicy::LOG_WITH_TRACE, ExceptionPolicy::from('log_with_trace'));
        $this->assertEquals(ExceptionPolicy::EXCEPTION_ONLY, ExceptionPolicy::from('exception_only'));
        $this->assertEquals(ExceptionPolicy::PROD_SAFE, ExceptionPolicy::from('prod_safe'));
        $this->assertEquals(ExceptionPolicy::LOG_AND_THROW, ExceptionPolicy::from('log_and_throw'));
    }

    public function test_exception_policy_try_from()
    {
        $this->assertEquals(ExceptionPolicy::THROW, ExceptionPolicy::tryFrom('throw'));
        $this->assertNull(ExceptionPolicy::tryFrom('invalid'));
    }
}

