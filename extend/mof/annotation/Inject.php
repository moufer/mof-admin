<?php

namespace mof\annotation;

use Attribute;
use ReflectionAttribute;
use ReflectionProperty;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_PROPERTY)]
class Inject
{
    public function __construct($name = '', $toContainer = true)
    {
    }

    /**
     * 实例化注入类
     * @param ReflectionAttribute $attribute
     * @param ReflectionProperty $property
     * @return mixed
     */
    public static function make(ReflectionAttribute $attribute,
                                ReflectionProperty  $property): mixed
    {
        if (!$abstract = ($attribute->getArguments()[0] ?? false)) {
            $abstract = $property->getType()->getName();
        }
        //是否注入容器中
        $toContainer = $attribute->getArguments()[1] ?? true;
        return $toContainer ? app()->make($abstract) : new $abstract();
    }
}