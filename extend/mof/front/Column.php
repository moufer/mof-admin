<?php

namespace mof\front;

class Column
{


    /**
     * 解析#[column]注解，获取字段
     * @return array
     */
    public static function get(): array
    {
        $result = [];
        $instance = new static;
        $reflect = new \ReflectionObject($instance);
        $properties = $reflect->getProperties(\ReflectionProperty::IS_PROTECTED);
        foreach ($properties as $property) {
            if ($getAttribute = ($property->getAttributes()[0] ?? false)) {
                if ('mof\annotation\Column' === $getAttribute->getName()) {
                    $result[$property->name] = \mof\annotation\Column::get($getAttribute, $property);
                }
            }
        }

        return $result;
    }
}