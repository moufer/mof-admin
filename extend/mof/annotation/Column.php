<?php

namespace mof\annotation;

use Attribute;
use ReflectionAttribute;
use ReflectionProperty;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_PROPERTY)]
class Column
{
    public function __construct($params = [])
    {
    }

    public static function get(ReflectionAttribute $attribute,
                               ReflectionProperty  $property,
                               \mof\front\Column   $column): array
    {
        $result = [];
        //首个参数
        $params = $attribute->getArguments()[0] ?? [];
        foreach ($params as $key => $param) {
            $result[$key] = $param;
        }
        return $result;
    }

}