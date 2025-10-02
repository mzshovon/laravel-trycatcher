<?php
namespace Mzshovon\LaravelTryCatcher\Support;

use Mzshovon\LaravelTryCatcher\Handler\PolicyHandler;
use Mzshovon\LaravelTryCatcher\Policies\ExceptionPolicy;

class ExceptionHandler
{
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
