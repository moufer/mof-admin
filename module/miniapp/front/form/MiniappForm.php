<?php

namespace module\miniapp\front\form;

use app\model\Module;
use mof\front\Form;
use mof\Model;
use mof\utils\ElementData;
use mof\utils\FormComponentOptions;

class MiniappForm extends Form
{
    protected array $validate = [
        'param' => [
            'type', 'title', 'appid', 'app_secret', 'original_id', 'module',
            'avatar_img', 'qrcode_img'
        ],
        'rule'  => [
            'type|类型'            => 'require|in:wechat',
            'title|名称'           => 'require',
            'appid|AppID'          => 'require|unique:miniapp',
            'app_secret|AppSecret' => 'require',
        ]
    ];

    protected function elements(?Model $model = null): array
    {
        return [
            [
                "prop"    => "type",
                "label"   => "小程序类型",
                "type"    => "select",
                "value"   => $model ? $model->type : 'wechat',
                "options" => [
                    ['label' => '微信小程序', 'value' => 'wechat'],
                ],
                "rules"   => [
                    ["required" => true],
                ],
                "colSpan" => 12,
            ],
            [
                "prop"    => "module",
                "label"   => "关联模块",
                "type"    => "select",
                "value"   => $model ? $model->module : '',
                "options" => ElementData::make(Module::enabledModules('miniapp'))
                    ->toSelectOptions('title', 'name'),
                "rules"   => [
                    ["required" => true],
                ],
                "colSpan" => 12,
            ],
            [
                "prop"  => "title",
                "label" => "名称",
                "value" => $model ? $model->title : '',
                "rules" => [
                    ["required" => true],
                ]
            ],
            [
                "prop"  => "intro",
                "label" => "描述",
                "value" => $model ? $model->intro : '',
            ],
            [
                "prop"  => "appid",
                "label" => "AppID",
                "value" => $model ? $model->appid : '',
                "rules" => [
                    ["required" => true],
                ]
            ],
            [
                "prop"  => "app_secret",
                "label" => "App secret",
                "value" => $model ? $model->app_secret : '',
                "rules" => [
                    ["required" => true],
                ]
            ],
            [
                "prop"  => "original_id",
                "label" => "原始ID",
                "value" => $model ? $model->original_id : '',
            ],
            [
                "prop"  => "avatar_img",
                "label" => "图标",
                'type' => 'upload:image',
                "value" => $model ? $model->avatar_img :'',
            ],
            [
                "prop"  => "qrcode_img",
                "label" => "二维码",
                'type' => 'upload:image',
                "value" => $model ? $model->qrcode_img : '',
            ],
        ];
    }
}