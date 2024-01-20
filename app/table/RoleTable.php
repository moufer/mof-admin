<?php

namespace app\table;

use app\model\Module;
use app\model\Perm;
use mof\front\Table;
use mof\utils\ElementData;

class RoleTable extends Table
{
    protected bool $showSearch = false;

    protected ElementData $elSgModules;

    protected function init(): void
    {
        parent::init();
        $this->elSgModules = ElementData::make(array_values(Module::sgPermModules()));
        //$this->tabs = $this->elSgModules->toTabs('title');
        $this->activeTab = $this->elSgModules->data()[0]['name'];
        $this->tabProp = 'category';
    }

    public function operation(): array
    {
        return [
            'width'   => 120,
            'show'    => true,
            'label'   => '操作',
            'mode'    => 'icon',
            'buttons' => [
                'edit|disable:id<=1',
                'delete|disable:id<=1'
            ]
        ];
    }


    protected function columnId(): array
    {
        return [
            "prop"   => 'id',
            "label"  => 'ID',
            "search" => true,
        ];
    }

    public function columnCategory(): array
    {
        return [
            "prop"    => "category",
            "label"   => "分类",
            "width"   => 120,
            "search"  => true,
            "visible" => false,
            "form"    => true,
            "type"    => "select",
            "options" => $this->elSgModules->toSelectOptions('title', 'name'),
        ];
    }

    protected function columnName(): array
    {
        return [
            "prop"   => "name",
            "label"  => "角色名称",
            "width"  => 180,
            "form"   => true,
            "search" => true,
        ];
    }

    protected function columnIntro(): array
    {
        return [
            "prop"  => "intro",
            "label" => "角色描述",
            "width" => "*",
            "align" => "left",
            "form"  => true
        ];
    }

    protected function columnPerms(): array
    {
        $switch = $this->elSgModules->map(fn($tab) => [
            '_expr' => 'category=' . $tab['name'],
            'data'  => Perm::getAll('category=' . $tab['name'], true),
        ]);
        return [
            "prop"    => "perm_ids",
            "label"   => "权限",
            "visible" => false,
            "form"    => [
                "type"          => "tree",
                "nodeKey"       => "id",
                "data"          => [],
                "_visible"      => 'category=regex:/.*/',
                "_defaultValue" => [],
                //根据其他数据切换显示内容
                "_switch"       => $switch
            ],
        ];
    }

    protected function columnStatus(): array
    {
        return [
            "prop"    => "status",
            "label"   => "状态",
            "search"  => true,
            "form"    => true,
            "type"    => "select",
            "options" => [
                ["label" => "禁用", "value" => 0],
                ["label" => "启用", "value" => 1],
            ]
        ];
    }

    protected function columnCreateAt(): array
    {
        return [
            "prop"  => "create_at",
            "label" => "添加时间",
            "width" => 230,
        ];
    }
}