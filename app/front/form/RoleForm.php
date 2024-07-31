<?php

namespace app\front\form;

use app\model\Module;
use app\model\Perm;
use mof\front\Form;
use mof\Model;
use mof\utils\DictArray;
use mof\utils\ElementData;

class RoleForm extends Form
{
    protected array $validate = [
        'param' => [
            'category', 'name', 'status/d', 'perm_ids/a', 'intro'
        ],
        'rule'  => [
            'name|名称'     => 'require|unique:system_role',
            'status|状态'   => 'require|in:0,1',
            'perms|权限'    => 'array',
            'category|分类' => 'require',
        ]
    ];

    protected function elements(?Model $model = null): array
    {
        $values = $model ? $model->toArray() : [];
        return [
            [
                "prop"    => "category",
                "label"   => "分类",
                "type"    => "select",
                "value"   => $values['category'] ?? $this->defaultValues['category'] ?? '',
                "options" => $this->getSgModules()->toSelectOptions(),
            ],
            [
                "prop"  => "name",
                "label" => "角色名称",
                "value" => $values['name'] ?? '',
            ],
            [
                "prop"  => "intro",
                "label" => "角色描述",
                "value" => $values['intro'] ?? '',
            ],
            [
                "prop"          => "perm_ids",
                "label"         => "权限",
                "type"          => "tree",
                "value"         => $model ? $model->perm_ids : [],
                "nodeKey"       => "id",
                "data"          => [],
                "_visible"      => 'category=regex:/.*/',
                "_defaultValue" => [],
                //根据其他数据切换显示内容
                "_switch"       => $this->getPermSwitchData(),
            ],
            [
                "prop"    => "status",
                "label"   => "状态",
                "type"    => "select",
                "value"   => $values['status'] ?? 1,
                "options" => [
                    ["label" => "禁用", "value" => 0],
                    ["label" => "启用", "value" => 1],
                ]
            ],
        ];
    }

    private function getPermSwitchData()
    {
        return $this->getSgModules()->map(fn($tab) => [
            '_expr' => 'category=' . $tab['value'],
            'data'  => Perm::getAll('category=' . $tab['value'], true),
        ]);
    }

    private function getSgModules()
    {
        global $elSgModules;
        if (!$elSgModules) {
            $elSgModules = DictArray::make(Module::modulesList())->toElementData();
        }
        return $elSgModules;
    }
}