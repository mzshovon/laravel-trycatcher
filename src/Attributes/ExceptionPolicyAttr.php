<?php

namespace Mzshovon\LaravelTryCatcher\Attributes;

use Attribute;
use Mzshovon\LaravelTryCatcher\Policies\ExceptionPolicy;

#[Attribute(Attribute::TARGET_METHOD)]
class ExceptionPolicyAttr
{
    public function __construct(public ExceptionPolicy $policy, public array $options = []) {}
}
