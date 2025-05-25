<?php

namespace app\front\table;

use mof\front\Table;
use app\model\Module;
use mof\enum\StatusEnum;
use mof\utils\DictArray;
use mof\utils\ElementData;

class RoleTable extends Table
{
    protected bool $showSearch = false;

    protected ElementData $elSgModules;

    protected function init(): void
    {
        parent::init();

        $typeList = Module::modulesList();
        $this->elSgModules = DictArray::make($typeList)->toElementData();

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
        $result = parent::operation();
        $result['width'] = 150;
        $result['buttons'] = [
            'edit|disable:id<=1',
            'delete|disable:id<=1'
        ];
        return $result;
    }

    protected function manageOptions(): array
    {
        return [
            'addActionAppendQuery' => true,
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

    protected function columnName(): array
    {
        return [
            "prop"   => "name",
            "label"  => "角色名称",
            "width"  => 150,
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
            "options" => StatusEnum::toDict()
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
