<?php

namespace mof\front;

use mof\annotation\Description;
use mof\utils\FormComponentOptions;
use think\helper\Str;

abstract class Config implements ConfigInterface
{
/**
     * 获取参数配置选项
     * @param array $values 已有的配置值
     * @param bool|string $withGroup
     * @return array
     */
    public function build(array $values = [], bool|string $withGroup = true): array
    {
        try {
            $group = $this->getGroups();
            $result = [];
            foreach ($group as $name => $label) {
                $fun = "group" . Str::studly($name);
                $form = $this->fillOptions($this->$fun($values));
                if ($withGroup === true) {
                    $result[] = [
                        'label'   => $label,
                        'prop'    => $name,
                        'options' => $form,
                    ];
                } else if ($name === $withGroup) {
                    //只匹配一个group
                    $result = $form;
                    break;
                } else {
                    $result = array_merge($result, $form);
                }
            }
            return $result;
        } catch (\ReflectionException) {
            return [];
        }
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

    /**
     * 获取分组信息
     * @return array
     */
    public function getGroups(): array
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
     */
    private function getGroupTitle(string $method): string
    {
        try {
            $ref = new \ReflectionMethod($this, $method);

            $attrs = $ref->getAttributes(Description::class);
            if ($attrs) {
                return $attrs[0]->newInstance()->title;
            }
            return $method;
        } catch (\ReflectionException $e) {
            return '未知组';
        }
    }
}