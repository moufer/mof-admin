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
        $class = new \ReflectionClass($this);
        foreach ($class->getConstants() as $key => $value) {
            $result[] = [
                'label' => $this->getDescription($key),
                'value' => $value
            ];
        }
        return $result;
    }

    /**
     * 获取枚举描述
     * @param string $key
     * @return string
     */
    private function getDescription(string $key): string
    {
        $class = new \ReflectionClass($this);
        try {
            $property = $class->getProperty($key);
            $attributes = $property->getAttributes();
            foreach ($attributes as $attribute) {
                if ($attribute->getName() == 'mof\attributes\Description') {
                    return $attribute->getArguments()[0];
                }
            }
        } catch (\ReflectionException $e) {
        }
        return $key;
    }
}