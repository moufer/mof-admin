<?php

namespace app\table;

use app\model\Module;
use mof\front\Table;
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
        $this->elSgModules = ElementData::make(array_values(Module::sgPermModules()));
        $this->tabs = $this->elSgModules->toTabs('title');
        $this->activeTab = $this->elSgModules->data()[0]['name'];
        $this->tabProp = 'category';
    }

    public function columnTitle(): array
    {
        return [
            "prop"   => "title",
            "label"  => "名称",
            "width"  => '*',
            "align"  => "left",
            "form"   => [
                "rules" => [
                    ["required" => true],
                ]
            ],
            "search" => true,
        ];
    }

    public function columnCategory(): array
    {
        return [
            "prop"    => "category",
            "label"   => "分类",
            "width"   => 120,
            "form"    => true,
            "search"  => true,
            "visible" => false,
            "type"    => "select",
            "options" => $this->elSgModules->toSelectOptions('title', 'name'),
        ];
    }

    public function columnType(): array
    {
        return [
            "prop"    => "type",
            "label"   => "类型",
            "width"   => 150,
            "form"    => true,
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
            "type"  => "icon",
            "form"  => [
                'type'     => 'icon-selector',
                '_visible' => 'type=group,menu',
            ]
        ];
    }

    public function columnPid(): array
    {
        $perms = \app\model\Perm::where('type', 'in', ['group', 'menu'])
            ->where('status', 1)
            ->order('sort', 'asc')
            ->select();
        return [
            "prop"    => "pid",
            "label"   => "上级",
            "visible" => false,
            "form"    => [
                'type'      => 'cascader',
                'clearable' => true,
                'props'     => [
                    'checkStrictly' => true,
                ],
                'options'   => ElementData::make($perms)->toCascaderOptions('id', 'title'),
            ]
        ];
    }

    public function columnModule(): array
    {
        return [
            "prop"    => "module",
            "label"   => "模块",
            "visible" => true,
            "form"    => [
                'type'     => 'select',
                '_visible' => 'type=group,menu',
            ],
            "search"  => true,
            "type"    => "select",
            "options" => ElementData::make(Module::enabledModules())
                ->toSelectOptions('title', 'name'),
        ];
    }

    public function columnUrl(): array
    {
        return [
            "prop"    => "url",
            "label"   => "URL",
            "visible" => false,
            "form"    => [
                '_visible' => 'type=menu',
                'intro'    => '前端页面的访问地址'
            ]
        ];
    }

    public function columnPerm(): array
    {
        return [
            "prop"    => "perm",
            "label"   => "权限",
            "visible" => false,
            "form"    => [
                '_visible' => 'type=menu,action',
                'intro'    => '后端API接口地址'
            ]
        ];
    }

    public function columnStatus(): array
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

    public function columnSort(): array
    {
        return [
            "prop"  => "sort",
            "label" => "排序",
            "form"  => [
                '_visible' => 'type=menu,group',
            ],
        ];
    }
}