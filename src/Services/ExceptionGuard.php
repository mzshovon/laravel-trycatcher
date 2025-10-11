<?php

namespace Mzshovon\LaravelTryCatcher\Services;

use Throwable;
use Mzshovon\LaravelTryCatcher\Handler\PolicyHandler;
use Mzshovon\LaravelTryCatcher\Policies\ExceptionPolicy;

/**
 * @param callable $fn
 * @param ExceptionPolicy $policy
 * @param array $options
 */
class ExceptionGuard
{
    //* cache yet to implement
    // protected static array $policyCache = [];

    /**
     * @param callable $fn
     * @param ExceptionPolicy $policy
     * @param array $options
     *
     * @return mixed
     */
    public function run(callable $fn, ExceptionPolicy $policy, array $options = [])
    {
        try {
            return $fn();
        } catch (Throwable $ex) {
            $handlerObject = new PolicyHandler(
                $ex,
                $policy,
                $options
            );
            return $handlerObject->resolvePolicy();
        }
    }
}
