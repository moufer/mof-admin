<?php

namespace mof\annotation;

use Attribute;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_PROPERTY)]
class Inject
{
    public function __construct()
    {
    }

}