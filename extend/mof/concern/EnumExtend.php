<?php

namespace mof\concern;

use mof\utils\DictArray;

/**
 * 枚举扩展
 * Trait EnumExtend
 * @package mof\traits
 */
trait EnumExtend
{
    /**
     * 获取枚举数组
     * 格式：[value=>Description]
     * @return array
     */
    public static function toArray(): array
    {
        $result = [];
        $reflectionClass = new \ReflectionClass(static::class);
        foreach ($reflectionClass->getConstants() as $key => $value) {
            $result[$value->value] = $reflectionClass->getReflectionConstant($key)
                                         ?->getAttributes()[0]
                                         ?->getArguments()[0];
        }

        return $result;
    }

    public static function toDict(): DictArray
    {
        return new DictArray(static::toArray());
    }

    public function label(): ?string
    {
        return (new \ReflectionClass($this))->getReflectionConstant($this->name)
                   ?->getAttributes()[0]
                   ?->getArguments()[0];
    }

}