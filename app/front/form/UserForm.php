<?php

namespace app\front\form;

use app\model\Admin;
use app\model\Module;
use app\model\Role;
use app\validate\AdminValidate;
use mof\front\Form;
use mof\Model;
use mof\utils\ElementData;
use mof\utils\FormComponentOptions;

class UserForm extends Form
{
    protected array $validate = [
        'param' => [
            'username', 'password', 'name', 'avatar', 'email', 'role_id/a', 'status/d'
        ],
        'rule'  => AdminValidate::class
    ];

    public function elements(Model $model = null): array
    {
        $values = $model ? $model->toArray() : [];
        return [
            [
                "prop"  => "username",
                "label" => "用户名",
                "value" => $values['username'] ?? '',
                "rules" => [
                    ["required" => true],
                ]
            ],
            [
                "prop"      => "password",
                "label"     => "密码",
                "type"      => "password",
                "introEdit" => "不修改密码请留空",
                "rules"     => empty($model) ? [
                    ["required" => true],
                ] : []
            ],
            [
                "prop"  => "avatar",
                "label" => "头像",
                'type'  => 'upload:image',
                "value" => $values['avatar'] ?? '',
            ],
            [
                "prop"  => "name",
                "label" => "姓名",
                "value" => $values['name'] ?? '',
                "rules" => [
                    ["required" => true],
                ],
                "colSpan" => 12,
            ],
            [
                "prop"  => "email",
                "label" => "邮箱",
                "value" => $values['email'] ?? '',
                "rules" => [
                    ["required" => true],
                ],
                "colSpan" => 12,
            ],
            [
                "prop"    => "role_id",
                "label"   => "角色",
                "type"    => "cascader",
                "value"   => $values['role_id'] ?? 0,
                "rules"   => [
                    ["required" => true],
                ],
                'options' => $this->getRolesOptions(),
            ],
            [
                "prop"          => "status",
                "label"         => "状态",
                "type"          => "select",
                "value"         => $values['status'] ?? 1,
                "options"       => Admin::statusOptions(),
                '_defaultValue' => 1
            ]

        ];
    }

    private function getRolesOptions(): array
    {
        $rows = Role::where('status', '=', 1)
            ->order('id', 'asc')
            ->select()
            ->toArray();

        $sgPermModules = Module::sgPermModules();
        foreach ($rows as $key => $item) {
            $rows[$key]['pid'] = -($sgPermModules[$item['category']]['id'] ?? 0);
        }
        foreach ($sgPermModules as $item) {
            $rows[] = [
                'id'   => -($item['id']),
                'pid'  => 0,
                'name' => $item['title'],
            ];
        }
        return ElementData::make($rows)
            ->toCascaderOptions('id', 'name');
    }
}