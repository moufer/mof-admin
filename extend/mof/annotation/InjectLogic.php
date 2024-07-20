<?php

namespace mof\annotation;

use Attribute;
use mof\Logic;
use ReflectionAttribute;
use ReflectionProperty;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_PROPERTY)]
class InjectLogic
{
    public function __construct($name = '')
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
        //模型类
        $modelClass = $attribute->getArguments()[0];
        //Logic类
        /** @var Logic $abstract */
        $abstract = $property->getType()->getName();
        return $abstract::make(new $modelClass);
    }
}