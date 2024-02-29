<?php

namespace app\front\form;

use app\model\Module;
use app\validate\PermValidate;
use mof\front\Form;
use mof\Model;
use mof\utils\ElementData;

class PermForm extends Form
{
    protected array $validate = [
        'param' => [
            'title', 'icon', 'type', 'module', 'category', 'pid/a', 'url', 'perm',
            'sort/d', 'status/d',
        ],
        'rule'  => PermValidate::class,
    ];

    protected function elements(?Model $model = null): array
    {
        $values = $model ? $model->toArray() : [];
        return [
            [
                "prop"  => "title",
                "label" => "名称",
                "value" => $values['title'] ?? '',
                "rules" => [
                    ["required" => true],
                ]
            ],
            [
                "prop"    => "category",
                "label"   => "分类",
                "type"    => "select",
                "value"   => $values['category'] ?? '',
                "options" => $this->getCategoryOptions(),
            ],
            [
                "prop"    => "type",
                "label"   => "类型",
                "type"    => "select",
                "value"   => $values['type'] ?? '',
                "options" => [
                    ['label' => '菜单组', 'value' => 'group'],
                    ['label' => '菜单', 'value' => 'menu'],
                    ['label' => '行为', 'value' => 'action'],
                ]
            ],
            [
                "prop"     => "icon",
                "label"    => "图标",
                "type"     => "icon-selector",
                "value"    => $values['icon'] ?? '',
                '_visible' => 'type=group,menu',
            ],
            [
                "prop"      => "pid",
                "label"     => "上级",
                'type'      => 'cascader',
                'value'     => $values['pid'] ?? [],
                'clearable' => true,
                'props'     => [
                    'checkStrictly' => true,
                ],
                'options'   => $this->getPermsOptions(),
            ],
            [
                "prop"     => "module",
                "label"    => "模块",
                "type"     => "select",
                "value"    => $values['module'] ?? '',
                "options"  => $this->getModuleOptions(),
                '_visible' => 'type=group,menu',
            ],
            [
                "prop"     => "url",
                "label"    => "URL",
                "value"    => $values['url'] ?? '',
                'intro'    => '前端页面的访问地址',
                '_visible' => 'type=menu',

            ],
            [
                "prop"     => "perm",
                "label"    => "权限",
                "value"    => $values['perm'] ?? '',
                'intro'    => '后端API接口地址',
                '_visible' => 'type=menu,action',
            ],
            [
                "prop"    => "status",
                "label"   => "状态",
                "type"    => "select",
                "value"   => (int)($values['status'] ?? 1),
                "options" => [
                    ["label" => "禁用", "value" => 0],
                    ["label" => "启用", "value" => 1],
                ]
            ],
            [
                "prop"     => "sort",
                "label"    => "排序",
                "value"    => $values['sort'] ?? 0,
                '_visible' => 'type=menu,group',
            ]
        ];
    }

    private function getElSgModules()
    {
        global $instance;
        if (!$instance) {
            $instance = ElementData::make(array_values(Module::sgPermModules()));
        }
        return $instance;
    }

    private function getCategoryOptions()
    {
        return $this->getElSgModules()->toSelectOptions('title', 'name');
    }

    private function getModuleOptions()
    {
        return ElementData::make(Module::enabledModules())
            ->toSelectOptions('title', 'name');
    }

    private function getPermsOptions()
    {
        $perms = \app\model\Perm::where('type', 'in', ['group', 'menu'])
            ->where('status', 1)
            ->order('sort', 'asc')
            ->select();
        return ElementData::make($perms)->toCascaderOptions('id', 'title');
    }
}