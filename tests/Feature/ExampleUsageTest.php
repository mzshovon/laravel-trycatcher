<?php

namespace Mzshovon\LaravelTryCatcher\Tests\Feature;

use Mzshovon\LaravelTryCatcher\Tests\TestCase;
use Mzshovon\LaravelTryCatcher\Policies\ExceptionPolicy;
use Mzshovon\LaravelTryCatcher\Models\ErrorLog;
use Exception;
use Mzshovon\LaravelTryCatcher\Attributes\ExceptionPolicyAttr;
use Symfony\Component\HttpFoundation\Response;

class ExampleUsageTest extends TestCase
{
    /**
     * Test basic usage example from documentation
     */
    public function test_basic_usage_example()
    {
        // Example: Basic exception handling
        $result = guarded(
            fn() => throw new Exception('Something went wrong'),
            ExceptionPolicy::LOG
        );
        $data = $result->getData();
        $this->assertInstanceOf(Response::class, $result);
        $this->assertTrue($data->error);
        $this->assertEquals('Something went wrong', $data->message);

        // Verify error was logged
        $this->assertDatabaseHas('error_logs', [
            'message' => 'Something went wrong'
        ]);

        ErrorLog::whereMessage("Something went wrong")->delete();
    }

    /**
     * Test API endpoint example
     */
    public function test_api_endpoint_example()
    {
        // Example: API endpoint with exception handling
        $userData = ['name' => 'John Doe', 'email' => 'john@example.com'];

        $result = guarded(
            function() use ($userData) {
                // Simulate API logic that might fail
                if (empty($userData['email'])) {
                    throw new Exception('Email is required');
                }
                return ['success' => true, 'user' => $userData];
            },
            ExceptionPolicy::PROD_SAFE,
            ['message' => 'Unable to process request']
        );
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals($userData, $result['user']);
    }

    /**
     * Test database operation example
     */
    public function test_database_operation_example()
    {
        // Example: Database operation with exception handling
        $result = guarded(
            function() {
                // Simulate database operation
                ErrorLog::create([
                    'level' => 'info',
                    'message' => 'Test log entry',
                    'context' => ['test' => true]
                ]);
                return 'Database operation successful';
            },
            ExceptionPolicy::LOG_AND_THROW
        );

        $this->assertEquals('Database operation successful', $result);

        // Verify the log entry was created
        $this->assertDatabaseHas('error_logs', [
            'message' => 'Test log entry',
            'level' => 'info'
        ]);

        ErrorLog::whereMessage("Test log entry")->delete();
    }

    /**
     * Test method with attribute example
     */
    public function test_method_with_attribute_example()
    {
        $testClass = new ExampleClass();

        // This should work with the attribute
        $result = guarded([$testClass, 'methodWithAttribute']);

        $this->assertEquals('Method executed successfully', $result);
    }

    /**
     * Test production environment example
     */
    public function test_production_environment_example()
    {
        // Set production environment
        $this->app['config']->set('app.env', 'production');

        $result = guarded(
            fn() => throw new Exception('Sensitive error information'),
            ExceptionPolicy::PROD_SAFE,
            ['message' => 'An error occurred. Please try again.']
        );
        $data = $result->getData();
        $this->assertInstanceOf(Response::class, $result);
        $this->assertTrue($data->error);
        $this->assertEquals('An error occurred. Please try again.', $data->message);
        $this->assertStringNotContainsString('Sensitive error information', $data->message);

        ErrorLog::whereMessage("Sensitive error information")->delete();
    }

    /**
     * Test custom context example
     */
    public function test_custom_context_example()
    {
        $userId = 123;
        $action = 'user_login';

        $result = guarded(
            fn() => throw new Exception('Login failed'),
            ExceptionPolicy::LOG,
            [
                'level' => 'warning',
                'context' => [
                    'user_id' => $userId,
                    'action' => $action,
                    'ip_address' => '192.168.1.1',
                    'user_agent' => 'Mozilla/5.0...'
                ]
            ]
        );

        $this->assertInstanceOf(Response::class, $result);

        // Verify context was logged
        $errorLog = ErrorLog::where('message', 'Login failed')->first();
        $this->assertNotNull($errorLog);
        $this->assertEquals('warning', $errorLog->level);
        $this->assertEquals($userId, $errorLog->context['user_id']);
        $this->assertEquals($action, $errorLog->context['action']);

        ErrorLog::whereMessage("Login failed")->delete();
    }

    /**
     * Test different policies example
     */
    public function test_different_policies_example()
    {
        $policies = [
            ExceptionPolicy::LOG,
            ExceptionPolicy::LOG_WITH_TRACE,
            ExceptionPolicy::EXCEPTION_ONLY,
            ExceptionPolicy::PROD_SAFE,
            ExceptionPolicy::LOG_AND_THROW,
            ExceptionPolicy::THROW
        ];

        foreach ($policies as $policy) {
                try {
                    $result = guarded(
                        fn() => throw new Exception("Test with $policy->value policy"),
                        $policy,
                        ['message' => 'Custom error message']
                    );

                    if ($policy !== ExceptionPolicy::THROW && $policy !== ExceptionPolicy::LOG_AND_THROW) {
                        $this->assertInstanceOf(Response::class, $result);
                        $this->assertTrue($result->getData()->error);
                    }
                } catch (Exception $e) {
                    $this->assertInstanceOf(Exception::class, $e);
                } finally {
                    ErrorLog::where('message', 'like', "%Test with%")->delete();
                }
        }

    }

    /**
     * Test helper function example
     */
    public function test_helper_function_example()
    {
        // Test isProdEnv helper
        $this->app['config']->set('app.env', 'production');
        $this->assertTrue(isProdEnv());

        $this->app['config']->set('app.env', 'local');
        $this->assertFalse(isProdEnv());
    }

    /**
     * Test facade usage example
     */
    public function test_facade_usage_example()
    {
        $result = \Mzshovon\LaravelTryCatcher\Facades\Guard::run(
            fn() => 'Facade test successful',
            ExceptionPolicy::THROW
        );

        $this->assertEquals('Facade test successful', $result);
    }
}

/**
 * Example class for testing attributes
 */
class ExampleClass
{
    #[ExceptionPolicyAttr(ExceptionPolicy::LOG)]
    public function methodWithAttribute()
    {
        return 'Method executed successfully';
    }
}

