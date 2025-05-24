<?php

namespace app\front\table;

use app\model\Module;
use mof\enum\StatusEnum;
use mof\front\Table;
use mof\utils\DictArray;
use mof\utils\ElementData;

class PermTable extends Table
{
    /** @var bool 是否使用树形表格 */
    protected bool   $useTreeTable   = true;
    protected bool   $showSearch     = false;
    protected array  $toolbarButtons = ['refresh', 'add', 'delete', 'status'];
    protected bool   $showPagination = false;
    protected string $sortField      = 'sort';
    protected string $sort           = 'asc';

    protected ElementData $elSgModules;

    protected function init(): void
    {
        parent::init();
        $this->elSgModules = DictArray::make(Module::modulesList())->toElementData();
        $this->tabs = $this->elSgModules->toTabs('label', 'value');
        $this->activeTab = $this->elSgModules->data()[0]['value'];
        $this->tabProp = 'category';
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

    public function columnTitle(): array
    {
        return [
            "prop"   => "title",
            "label"  => "名称",
            "width"  => '*',
            "align"  => "left",
            "search" => true,
        ];
    }

    public function columnType(): array
    {
        return [
            "prop"    => "type",
            "label"   => "类型",
            "width"   => 150,
            "search"  => true,
            "type"    => "select",
            "options" => [
                ['label' => '菜单组', 'value' => 'group'],
                ['label' => '菜单', 'value' => 'menu'],
                ['label' => '行为', 'value' => 'action'],
            ]
        ];
    }

    public function columnIcon(): array
    {
        return [
            "prop"  => "icon",
            "label" => "图标",
            "type"  => "icon"
        ];
    }

    public function columnModule(): array
    {
        return [
            "prop"    => "module",
            "label"   => "模块",
            "visible" => true,
            "search"  => true,
            "type"    => "select",
            "options" => ElementData::make(Module::enabledModules())
                ->toSelectOptions('title', 'name'),
        ];
    }

    public function columnStatus(): array
    {
        return [
            "prop"    => "status",
            "label"   => "状态",
            "search"  => true,
            "type"    => "select",
            "options" => StatusEnum::toDict()
        ];
    }

    public function columnSort(): array
    {
        return [
            "prop"  => "sort",
            "label" => "排序",
        ];
    }
}
