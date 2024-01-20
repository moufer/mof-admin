<?php

namespace mof\utils;

use app\library\FormComponentOptions;
use mof\annotation\Description;
use think\helper\Str;

abstract class ConfigOptions
{
    /**
     * 获取参数配置选项
     * @param array $values 已有的配置参数
     * @param bool $withTab 是否包含分组信息
     * @return array
     */
    public function options(array $values = [], bool $withTab = true): array
    {
        try {
            $tabs = $this->getTabs();
            $result = [];
            foreach ($tabs as $name => $label) {
                $fun = "group" . Str::studly($name);
                if ($withTab) {
                    $result[] = [
                        'label'   => $label,
                        'prop'    => $name,
                        'options' => $this->fillOptions($this->$fun($values)),
                    ];
                } else {
                    $result = array_merge($result, $this->fillOptions($this->$fun($values)));
                }
            }
            return $result;
        } catch (\ReflectionException) {
            return [];
        }
    }

    /**
     * 获取分组信息
     * @return array
     * @throws \ReflectionException
     */
    public function getTabs(): array
    {
        //获取当前类里方法名前缀是group的方法
        $methods = get_class_methods($this);
        $result = [];
        foreach ($methods as $method) {
            if (str_starts_with($method, 'group')) {
                //通过注解获取分组名称
                $tabName = Str::snake(substr($method, 5));
                $result[$tabName] = $this->getGroupTitle($method);
            }
        }
        return $result;
    }

    /**
     * 获取分组标题
     * @param string $method
     * @return string
     * @throws \ReflectionException
     */
    private function getGroupTitle(string $method): string
    {
        $ref = new \ReflectionMethod($this, $method);
        $attrs = $ref->getAttributes(Description::class);
        if ($attrs) {
            return $attrs[0]->newInstance()->title;
        }
        return $method;
    }

    /**
     * 填充默认配置选项
     * @param array $options
     * @return array
     */
    private function fillOptions(array $options): array
    {
        return array_map(function ($option) {
            return FormComponentOptions::fill($option);
        }, $options);
    }
}
