<?php

namespace Mzshovon\LaravelTryCatcher\Handler;

use Illuminate\Support\Facades\Log;
use Mzshovon\LaravelTryCatcher\Models\ErrorLog;
use Mzshovon\LaravelTryCatcher\Policies\ExceptionPolicy;
use Symfony\Component\HttpFoundation\Response;

class PolicyHandler {

    protected ?\Throwable $ex;
    protected ?ExceptionPolicy $policy;
    protected array $options = [];

    public function __construct(
        \Throwable $ex,
        ExceptionPolicy $policy,
        array $options = []
    ){
        $this->ex = $ex;
        $this->policy = $policy;
        $this->options = $options;
    }

    public function resolvePolicy()
    {
        switch ($this->policy) {
            case ExceptionPolicy::LOG:
                $this->logException($this->ex, false, $this->options);
                return $this->formatExceptionForReturn($this->ex, $this->options, false);
            case ExceptionPolicy::LOG_WITH_TRACE:
                $this->logException($this->ex, true, $this->options);
                return $this->formatExceptionForReturn($this->ex, $this->options, true);
            case ExceptionPolicy::EXCEPTION_ONLY:
                return $this->formatExceptionForReturn($this->ex, $this->options, true);
            case ExceptionPolicy::PROD_SAFE:
                // do not log stack by default, log minimal
                $this->logException($this->ex, false, $this->options);
                return $this->safeResponse($this->options);
            case ExceptionPolicy::LOG_AND_THROW:
                $this->logException($this->ex, true, $this->options);
                throw $this->ex;
            case ExceptionPolicy::THROW:
            default:
                throw $this->ex;
        }
    }

    protected function logException(\Throwable $ex, bool $withTrace = true, array $options = []): void
    {
        try {
            $trace = $withTrace ? $ex->getTraceAsString() : null;
            ErrorLog::create([
                'level' => $options['level'] ?? 'error',
                'message' => $ex->getMessage(),
                'trace' => $trace,
                'context' => $options['context'] ?? null,
            ]);
        } catch (\Throwable $exc) {
            // if DB logging fails, fallback to file log to avoid silent errors
            Log::error('Failed to persist ErrorLog: ' . $exc->getMessage());
        }
        // always write to Laravel log too
        $logMsg = $ex->getMessage() . ($withTrace ? "\n" . $ex->getTraceAsString() : '');
        Log::error($logMsg, $options['context'] ?? []);
    }

    protected function formatExceptionForReturn(\Throwable $ex, array $options, bool $withTrace)
    {
        // You can shape response structure used across app (for API)
        $payload = [
            'error' => true,
            'message' => $ex->getMessage(),
        ];
        if ($withTrace && config('app.debug')) {
            $payload['trace'] = $ex->getTrace();
        }
        return response()->json($payload, $options['status'] ?? Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    protected function safeResponse(array $options)
    {
        return response()->json([
            'error' => true,
            'message' => $options['message'] ?? 'Something went wrong. Please contact support.'
        ], $options['status'] ?? 500);
    }

}
