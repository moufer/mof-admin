<?php

namespace module\miniapp\front\table;

use app\model\Module;
use mof\front\Table;
use mof\utils\ElementData;

class MiniappTable extends Table
{
    protected string $serverBaseUrl  = '/{module}/backend/manage';
    protected bool   $tableSelection = false;
    protected bool   $showSearch     = false;
    protected array  $toolbarButtons = ['refresh', 'add'];
    protected bool   $showPagination = false;
    protected string $sort           = 'asc';

    public function columnAvatarImg(): array
    {
        return [
            "prop"  => "avatar_img",
            "label" => "图标",
            "type"  => "image",
            "width" => 120
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
                ['label' => '微信小程序', 'value' => 'wechat'],
            ]
        ];
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

    public function columnIntro(): array
    {
        return [
            "prop"    => "intro",
            "label"   => "描述",
            "width"   => '*',
            "search"  => false,
            "visible" => false,
        ];
    }

    public function columnAppid(): array
    {
        return [
            "prop"   => "appid",
            "label"  => "AppID",
            "width"  => 200,
            "search" => false,
        ];
    }

    public function columnOriginalId(): array
    {
        return [
            "prop"    => "original_id",
            "label"   => "原始ID",
            "search"  => false,
            "visible" => false,
        ];
    }

    public function columnModule(): array
    {
        return [
            "prop"    => "module",
            "label"   => "应用模块",
            "type"    => "select",
            "options" => ElementData::make(Module::enabledModules('miniapp'))
                ->toSelectOptions('title', 'name'),
            "search"  => true,
            "width" => 150
        ];
    }

    public function columnCreateAt(): array
    {
        return [
            "order"  => 8,
            "prop"   => "create_at",
            "label"  => "添加时间",
            "type"   => "datetime",
            "search" => true
        ];
    }
}