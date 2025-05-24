<?php

namespace app\front\table;

use app\enum\ModuleStatusEnum;
use mof\front\Table;

class ModuleTable extends Table
{
    protected string $pk             = 'name';
    protected bool   $showSearch     = false;
    protected bool   $showPagination = false;
    protected bool   $tableSelection = false;
    protected array  $toolbarButtons = ['refresh', 'search'];
    protected string $sortField      = 'order';

    public function columnName(): array
    {
        return [
            "prop"    => "name",
            "label"   => "标识",
            "visible" => true,
            "width"   => 120,
        ];
    }

    public function columnTitle(): array
    {
        return [
            "prop"   => "title",
            "label"  => "名称",
            "width"  => 150,
            "search" => true,
        ];
    }

    public function columnVersion(): array
    {
        return [
            "prop"  => "version",
            "label" => "版本号",
            "width" => 100,
        ];
    }

    public function columnAuthor(): array
    {
        return [
            "prop"   => "author",
            "label"  => "作者",
            "width"  => 140,
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
            "options" => ModuleStatusEnum::toDict(),
        ];
    }
}
