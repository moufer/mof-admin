<?php

namespace module\miniapp\front\form;

use app\front\form\UserForm;
use module\miniapp\model\MiniApp;
use module\miniapp\validate\AdminValidate;
use mof\Model;
use mof\utils\ElementData;

class AdminForm extends UserForm
{
    protected array $validate = [
        'param' => [
            'username', 'password', 'name', 'avatar/a', 'email', 'status/d',
            'miniapp_ids/a',
        ],
        'rule'  => AdminValidate::class
    ];

    public function elements(Model $model = null): array
    {
        $result = parent::elements($model);
        //过滤掉角色字段
        $result = array_filter($result, function ($item) {
            return 'role_id' !== $item['prop'];
        });
        $result[] = [
            "prop"     => "miniapp_ids",
            "label"    => "关联小程序",
            "type"     => "select",
            "value"    => $model ? $model->miniapp_ids : [],
            "options"  => $this->getMiniappIdsOptions(),
            'multiple' => true,
            "rules"    => [
                ["required" => true],
            ],
            "order"    => 6,
        ];
        return $result;
    }

    private function getMiniappIdsOptions()
    {
        $rows = MiniApp::order('id', 'asc')->select()->toArray();
        return ElementData::make($rows)->toSelectOptions('title', 'id');
    }
}