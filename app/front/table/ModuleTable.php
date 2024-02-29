<?php

namespace app\table;

use mof\front\Table;

class ModuleTable extends Table
{
    protected string|int $pk             = 'name';
    protected bool       $showSearch     = false;
    protected bool       $showPagination = false;
    protected bool       $tableSelection = false;
    protected array      $toolbarButtons = ['refresh', 'add', 'search'];
    protected string     $sortField      = 'order';

    public function operation(): array
    {
        $result = parent::operation();
        $result['width'] = 150;
        return $result;
    }

    public function columnName(): array
    {
        return [
            "prop"    => "name",
            "label"   => "模块标识",
            "visible" => false
        ];
    }

    public function columnTitle(): array
    {
        return [
            "prop"   => "title",
            "label"  => "模块名称",
            "width"  => 200,
            "align"  => "left",
            "form"   => true,
            "search" => true,
        ];
    }

    public function columnVersion(): array
    {
        return [
            "prop"  => "version",
            "label" => "版本号",
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
            "form"    => true,
            "search"  => true,
            "options" => [
                ["label" => "已停用", "value" => 0],
                ["label" => "已启用", "value" => 1],
                ["label" => "未安装", "value" => -1],
            ]
        ];
    }
}
