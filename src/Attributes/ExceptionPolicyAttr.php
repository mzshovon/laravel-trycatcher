<?php

namespace Mzshovon\LaravelTryCatcher\Attributes;

use Attribute;
use Mzshovon\LaravelTryCatcher\Policies\ExceptionPolicy;

#[Attribute(Attribute::TARGET_METHOD)]
class ExceptionPolicyAttr
{
    /**
     * @param  public ExceptionPolicy $policy
     * @param public array $options
     * @param public bool $shouldThrow
     */
    public function __construct(
        public ExceptionPolicy $policy,
        public array $options = [],
        public bool $shouldThrow = false,
    ) {}
}
