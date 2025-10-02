<?php

namespace Mzshovon\LaravelTryCatcher\Policies;

enum ExceptionPolicy: string
{
    case THROW = 'throw';                   // rethrow to Laravel handler
    case LOG = 'log';                       // log w/out stack
    case LOG_WITH_TRACE = 'log_with_trace'; // log with trace
    case EXCEPTION_ONLY = 'exception_only'; // return/format exception message only
    case PROD_SAFE = 'prod_safe';           // safe message for prod (no trace)
    case LOG_AND_THROW = 'log_and_throw';   // log then rethrow
}
