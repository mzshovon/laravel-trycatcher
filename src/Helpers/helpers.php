<?php

use  Mzshovon\LaravelTryCatcher\Services\ExceptionGuard;
use  Mzshovon\LaravelTryCatcher\Attributes\ExceptionPolicyAttr;

if (! function_exists('guarded')) {
    /**
     * Wrap any callable with the ExceptionGuard.
     *
     * $callable can be:
     *  - a Closure
     *  - [$obj, 'method']
     *  - 'ClassName::method'
     *  - function name string
     *
     * If $policy is null, this helper will try to discover an ExceptionPolicyAttr on the method.
     */
    function guarded(callable|string $callable, $policy = null, array $options = [])
    {
        /** @var ExceptionGuard $guard */
        $guard = app(ExceptionGuard::class);

        // If the $callable is a string like 'Class::method', convert to callable
        if (is_string($callable) && str_contains($callable, '::')) {
            $parts = explode('::', $callable);
            $callable = [$parts[0], $parts[1]];
        }

        // Try discover attribute if method is provided and no explicit policy passed.
        if ($policy === null && is_array($callable) && count($callable) === 2) {
            [$class, $method] = $callable;
            try {
                $rm = new ReflectionMethod($class, $method);
                $attrs = $rm->getAttributes(ExceptionPolicyAttr::class);
                if (!empty($attrs)) {
                    $attr = $attrs[0]->newInstance();
                    $policy = $attr->policy;
                    $options = array_merge($options, $attr->options ?? []);
                }
            } catch (\ReflectionException $e) {
                // ignore; fallback to default
            }
        }

        // Default policy from config
        $policy = $policy ?? config('exception-guard.default_policy');

        return $guard->run(fn() => is_array($callable) ? $callable[0]->{$callable[1]}(...($options['__args'] ?? [])) : ($callable instanceof Closure ? $callable() : $callable()), $policy, $options);
    }

    if (! function_exists('isProdEnv')) {
        /**
         * @return bool
         */
        function isProdEnv() : bool
        {
            return config('app.env') == "production";
        }
    }
}
