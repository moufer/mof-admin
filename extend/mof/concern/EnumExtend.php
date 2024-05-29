<?php

namespace mof\concern;

/**
 * 枚举扩展
 * Trait EnumExtend
 * @package mof\traits
 */
trait EnumExtend
{
    /**
     * 获取枚举选项
     * @return array
     */
    public function options(): array
    {
        $result = [];
        $reflectionClass = new \ReflectionClass($this);
        foreach ($reflectionClass->getConstants() as $key => $value) {
            $result[] = [
                'label' => $this->getDescription($key, $reflectionClass),
                'value' => $value
            ];
        }
        return $result;
    }

    /**
     * 获取枚举描述
     * @param string $key
     * @param \ReflectionClass|null $reflectionClass
     * @return string
     */
    private function getDescription(string $key='', ?\ReflectionClass $reflectionClass = null): string
    {
        !$key && $key = $this->name;
        !$reflectionClass && $reflectionClass = new \ReflectionClass($this);
        return $reflectionClass->getReflectionConstant($key)->getAttributes()[0]->getArguments()[0];
    }
}