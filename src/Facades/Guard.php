<?php

namespace Mzshovon\LaravelTryCatcher\Facades;

use Illuminate\Support\Facades\Facade;

class Guard extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Mzshovon\LaravelTryCatcher\Services\ExceptionGuard::class;
    }
}
