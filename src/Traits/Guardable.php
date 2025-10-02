<?php

namespace Mzshovon\LaravelTryCatcher\Traits;

use Mzshovon\LaravelTryCatcher\Attributes\ExceptionPolicyAttr;
use Mzshovon\LaravelTryCatcher\Policies\ExceptionPolicy;
use Mzshovon\LaravelTryCatcher\Services\ExceptionGuard;
use Mzshovon\LaravelTryCatcher\Support\ExceptionHandler;
use ReflectionMethod;

trait Guardable
{
    public function __call($method, $arguments)
    {
        if (!method_exists($this, $method)) {
            throw new \BadMethodCallException("Method {$method} does not exist.");
        }

        $ref = new ReflectionMethod($this, $method);
        $attributes = $ref->getAttributes(ExceptionPolicyAttr::class);

        if (!empty($attributes)) {
            /** @var ExceptionPolicyAttr $attr */
            $attr = $attributes[0]->newInstance();
            $policy = $attr->policy;

            try {
                return $ref->invokeArgs($this, $arguments);
            } catch (\Throwable $e) {
                return ExceptionHandler::handle($e, $policy);
            }
        }

        // fallback: normal method call if no attribute
        return $ref->invokeArgs($this, $arguments);
    }

    protected function runGuardedMethod(string $method, array $args = [], $policy = null, array $options = [])
    {
        // Resolve reflection for the actual declaring class method
        $rm = new ReflectionMethod($this, $method);

        // Try to find attribute if no explicit policy provided
        if ($policy === null) {
            $attrs = $rm->getAttributes(ExceptionPolicyAttr::class);
            if (!empty($attrs)) {
                $attr = $attrs[0]->newInstance();
                $policy = $attr->policy;
                $options = array_merge($options, $attr->options ?? []);
            } else {
                // no attribute, use default config policy
                $policy = config('exception-guard.default_policy') ?? ExceptionPolicy::THROW;
            }
        }

        // Prepare callable that invokes the method (allows protected/private)
        $callable = function () use ($rm, $args) {
            // allow calling protected/private by using ReflectionMethod::setAccessible
            if (! $rm->isPublic()) {
                $rm->setAccessible(true);
            }
            return $rm->invokeArgs($this, $args);
        };

        /** @var ExceptionGuard $guard */
        $guard = app(ExceptionGuard::class);

        return $guard->run($callable, $policy, $options);
    }
}
