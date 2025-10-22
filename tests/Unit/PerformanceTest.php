<?php

namespace Mzshovon\LaravelTryCatcher\Tests\Unit;

use Mzshovon\LaravelTryCatcher\Tests\TestCase;
use Mzshovon\LaravelTryCatcher\Services\ExceptionGuard;
use Mzshovon\LaravelTryCatcher\Policies\ExceptionPolicy;
use Mzshovon\LaravelTryCatcher\Models\ErrorLog;
use Exception;

class PerformanceTest extends TestCase
{
    private ExceptionGuard $guard;

    protected function setUp(): void
    {
        parent::setUp();
        $this->guard = new ExceptionGuard();
    }

    public function test_guard_performance_with_successful_operations()
    {
        $iterations = 1000;
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        for ($i = 0; $i < $iterations; $i++) {
            $this->guard->run(
                fn() => "success_$i",
                ExceptionPolicy::THROW
            );
        }

        $endTime = microtime(true);
        $endMemory = memory_get_usage();

        $executionTime = $endTime - $startTime;
        $memoryUsed = $endMemory - $startMemory;

        // Performance assertions
        $this->assertLessThan(1.0, $executionTime, "Execution time should be less than 1 second for $iterations iterations");
        $this->assertLessThan(10 * 1024 * 1024, $memoryUsed, "Memory usage should be less than 10MB for $iterations iterations");

        echo "\nPerformance Test Results:\n";
        echo "Iterations: $iterations\n";
        echo "Execution Time: " . number_format($executionTime * 1000, 2) . "ms\n";
        echo "Memory Used: " . number_format($memoryUsed / 1024, 2) . "KB\n";
        echo "Average per operation: " . number_format(($executionTime * 1000) / $iterations, 4) . "ms\n";
    }

    public function test_guard_performance_with_exceptions()
    {
        $iterations = 100;
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        for ($i = 0; $i < $iterations; $i++) {
            $this->guard->run(
                fn() => throw new Exception("Test Guard Performance exception $i"),
                ExceptionPolicy::LOG
            );
        }

        $endTime = microtime(true);
        $endMemory = memory_get_usage();

        $executionTime = $endTime - $startTime;
        $memoryUsed = $endMemory - $startMemory;

        // Performance assertions
        $this->assertLessThan(2.0, $executionTime, "Execution time should be less than 2 seconds for $iterations exception handling iterations");
        $this->assertLessThan(20 * 1024 * 1024, $memoryUsed, "Memory usage should be less than 20MB for $iterations exception handling iterations");

        echo "\nException Handling Performance Test Results:\n";
        echo "Iterations: $iterations\n";
        echo "Execution Time: " . number_format($executionTime * 1000, 2) . "ms\n";
        echo "Memory Used: " . number_format($memoryUsed / 1024, 2) . "KB\n";
        echo "Average per operation: " . number_format(($executionTime * 1000) / $iterations, 4) . "ms\n";

        $this->removeTestErrorLogs("Test Guard Performance exception");
    }

    public function test_memory_leak_prevention()
    {
        $iterations = 500;
        $memorySnapshots = [];

        for ($i = 0; $i < $iterations; $i++) {
            $this->guard->run(
                fn() => throw new Exception("Memory test exception $i"),
                ExceptionPolicy::LOG
            );

            // Take memory snapshot every 50 iterations
            if ($i % 50 === 0) {
                $memorySnapshots[] = memory_get_usage();
            }
        }

        // Check for memory leaks by comparing snapshots
        $firstSnapshot = $memorySnapshots[0];
        $lastSnapshot = end($memorySnapshots);
        $memoryGrowth = $lastSnapshot - $firstSnapshot;

        // Allow for some memory growth but not excessive
        $this->assertLessThan(5 * 1024 * 1024, $memoryGrowth, "Memory growth should be less than 5MB over $iterations iterations");

        echo "\nMemory Leak Test Results:\n";
        echo "Iterations: $iterations\n";
        echo "Memory Growth: " . number_format($memoryGrowth / 1024, 2) . "KB\n";
        echo "Average Growth per 50 iterations: " . number_format($memoryGrowth / count($memorySnapshots) / 1024, 2) . "KB\n";

        $this->removeTestErrorLogs("Memory test exception");
    }

    public function test_database_performance_with_logging()
    {
        $iterations = 50;
        $startTime = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $this->guard->run(
                fn() => throw new Exception("DB performance test $i"),
                ExceptionPolicy::LOG_WITH_TRACE,
                ['context' => ['iteration' => $i, 'timestamp' => time()]]
            );
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Check database performance
        $this->assertLessThan(5.0, $executionTime, "Database logging should complete within 5 seconds for $iterations operations");

        $errorData = ErrorLog::where("message","like","%DB performance test%");

        // Verify all records were created
        $this->assertEquals($iterations, $errorData->count());

        echo "\nDatabase Performance Test Results:\n";
        echo "Iterations: $iterations\n";
        echo "Execution Time: " . number_format($executionTime * 1000, 2) . "ms\n";
        echo "Average per operation: " . number_format(($executionTime * 1000) / $iterations, 4) . "ms\n";
        echo "Records Created: " . ErrorLog::count() . "\n";
        $errorData->delete();
    }

    public function test_concurrent_operations_simulation()
    {
        $iterations = 100;
        $startTime = microtime(true);

        // Simulate concurrent operations by running multiple operations in sequence
        $results = [];
        for ($i = 0; $i < $iterations; $i++) {
            $results[] = $this->guard->run(
                fn() => $i % 10 === 0 ? throw new Exception("Concurrent test $i") : "success_$i",
                ExceptionPolicy::LOG
            );
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Performance assertions
        $this->assertLessThan(2.0, $executionTime, "Concurrent simulation should complete within 2 seconds");
        $this->assertCount($iterations, $results);

        echo "\nConcurrent Operations Test Results:\n";
        echo "Iterations: $iterations\n";
        echo "Execution Time: " . number_format($executionTime * 1000, 2) . "ms\n";
        echo "Average per operation: " . number_format(($executionTime * 1000) / $iterations, 4) . "ms\n";

        $this->removeTestErrorLogs("Concurrent test");
    }

    public function test_large_context_data_performance()
    {
        $largeContext = [
            'user_data' => array_fill(0, 1000, 'test_data'),
            'metadata' => array_fill(0, 500, ['key' => 'value', 'nested' => ['deep' => 'data']]),
            'timestamps' => array_fill(0, 100, time())
        ];

        $iterations = 10;
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        for ($i = 0; $i < $iterations; $i++) {
            $this->guard->run(
                fn() => throw new Exception("Large context test $i"),
                ExceptionPolicy::LOG,
                ['context' => $largeContext]
            );
        }

        $endTime = microtime(true);
        $endMemory = memory_get_usage();

        $executionTime = $endTime - $startTime;
        $memoryUsed = $endMemory - $startMemory;

        // Performance assertions
        $this->assertLessThan(3.0, $executionTime, "Large context operations should complete within 3 seconds");
        $this->assertLessThan(50 * 1024 * 1024, $memoryUsed, "Memory usage should be reasonable for large context data");

        echo "\nLarge Context Performance Test Results:\n";
        echo "Iterations: $iterations\n";
        echo "Context Size: " . number_format(strlen(serialize($largeContext)) / 1024, 2) . "KB\n";
        echo "Execution Time: " . number_format($executionTime * 1000, 2) . "ms\n";
        echo "Memory Used: " . number_format($memoryUsed / 1024, 2) . "KB\n";

        $this->removeTestErrorLogs("Large context test");
    }

    private function removeTestErrorLogs(string $message)
    {
        ErrorLog::where('message', 'like', "%{$message}%")->delete();
    }
}

