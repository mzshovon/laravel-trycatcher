<?php

namespace Mzshovon\LaravelTryCatcher\Services;

use Throwable;
use Mzshovon\LaravelTryCatcher\Handler\PolicyHandler;
use Mzshovon\LaravelTryCatcher\Policies\ExceptionPolicy;

class ExceptionGuard
{
    //* cache yet to implement
    // protected static array $policyCache = [];

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
