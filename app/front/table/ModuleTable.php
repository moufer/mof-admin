<?php

namespace app\front\table;

use mof\front\Table;

class ModuleTable extends Table
{
    protected string $pk             = 'name';
    protected bool   $showSearch     = false;
    protected bool   $showPagination = false;
    protected bool   $tableSelection = false;
    protected array  $toolbarButtons = ['refresh', 'add', 'search'];
    protected string $sortField      = 'order';

    public function operation(): array
    {
        $result = parent::operation();
        $result['width'] = 150;
        return $result;
    }

    public function columnTitle(): array
    {
        return [
            "prop"   => "title",
            "label"  => "模块名称",
            "width"  => 150,
            "align"  => "left",
            "search" => true,
        ];
    }

    public function columnName(): array
    {
        return [
            "prop"    => "name",
            "label"   => "标识",
            "visible" => true,
            "width"   => 150,
        ];
    }

    public function columnVersion(): array
    {
        return [
            "prop"  => "version",
            "label" => "版本号",
            "width"  => 120,
        ];
    }

    public function columnAuthor(): array
    {
        return [
            "prop"   => "author",
            "label"  => "作者",
            "width"  => 150,
            "search" => true,
        ];
    }

    public function columnDescription(): array
    {
        return [
            "prop"  => "description",
            "label" => "描述",
            "width" => '*',
            "align" => "left",
        ];
    }

    public function columnStatus(): array
    {
        return [
            "prop"    => "status",
            "label"   => "状态",
            "type"    => "select",
            "search"  => true,
            "options" => [
                ["label" => "已停用", "value" => 0],
                ["label" => "已启用", "value" => 1],
                ["label" => "未安装", "value" => -1],
            ]
        ];
    }
}
