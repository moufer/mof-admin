<?php

namespace app\front\table;

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

        $typeList = Module::modulesList();
        $this->elSgModules = ElementData::make($typeList);

        //tabs
        $this->tabProp = 'category';
        $this->tabs = $this->elSgModules->toTabs('label', 'value');
        $this->activeTab = array_keys($typeList)[0];

        parent::init();

    }

    protected function getPermModules(): array
    {
        $data = array_values(Module::sgPermModules());
        $result = [];
        foreach ($data as $item) {
            $result[$item['name']] = $item['title'];
        }
        return $result;
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
        ];
    }

    protected function columnStatus(): array
    {
        return [
            "prop"    => "status",
            "label"   => "状态",
            "search"  => true,
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