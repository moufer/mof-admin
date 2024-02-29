<?php

namespace module\miniapp\table;

use mof\utils\FormComponentOptions;
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
            "form"  => FormComponentOptions::fill(['type' => 'upload:image', 'order' => 98]),
        ];
    }

    public function columnQrcodeImg(): array
    {
        return [
            "prop"    => "qrcode_img",
            "label"   => "二维码",
            "search"  => false,
            "visible" => false,
            "form"    => FormComponentOptions::fill(['type' => 'upload:image', 'order' => 99]),
        ];
    }

    public function columnType(): array
    {
        return [
            "prop"    => "type",
            "label"   => "类型",
            "width"   => 120,
            "form"    => true,
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
            "form"   => true,
            "search" => true,
        ];
    }

    public function columnIntro(): array
    {
        return [
            "prop"    => "intro",
            "label"   => "描述",
            "width"   => '*',
            "form"    => true,
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
            "form"   => true,
            "search" => false,
        ];
    }

    public function columnAppSecret(): array
    {
        return [
            "prop"    => "app_secret",
            "label"   => "App secret",
            "form"    => true,
            "search"  => false,
            "visible" => false,
        ];
    }

    public function columnOriginalId(): array
    {
        return [
            "prop"    => "original_id",
            "label"   => "原始ID",
            "form"    => true,
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
            "form"    => true,
        ];
    }

    protected function columnCreateAt(): array
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