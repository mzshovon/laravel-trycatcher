<?php
namespace Mzshovon\LaravelTryCatcher\Support;

use Mzshovon\LaravelTryCatcher\Handler\PolicyHandler;
use Mzshovon\LaravelTryCatcher\Policies\ExceptionPolicy;

/**
 * @param \Throwable $ex
 * @param ExceptionPolicy $policy
 * @param array $options
 */
class ExceptionHandler
{
    /**
     * @param \Throwable $ex
     * @param ExceptionPolicy $policy
     * @param array $options
     *
     * @return mixed
     */
    public static function handle(\Throwable $ex, ExceptionPolicy $policy, array $options = [])
    {
        $handlerObject = new PolicyHandler(
            $ex,
            $policy,
            $options
        );
        return $handlerObject->resolvePolicy();
    }
}
