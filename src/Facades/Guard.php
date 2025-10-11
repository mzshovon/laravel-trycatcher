<?php

namespace Mzshovon\LaravelTryCatcher\Facades;

use Illuminate\Support\Facades\Facade;
/**
 * @method static mixed run(callable $fn, ExceptionPolicy $policy, array $options = [])
 */
class Guard extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Mzshovon\LaravelTryCatcher\Services\ExceptionGuard::class;
    }
}
