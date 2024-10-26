<?php

namespace app\front\form;

use app\model\Admin;
use app\model\Module;
use app\model\Role;
use app\validate\AdminValidate;
use mof\front\Form;
use mof\Model;
use mof\utils\ElementData;

class UserForm extends Form
{
    protected array $validate = [
        'param' => [
            'username', 'password', 'name', 'avatar', 'email', 'role_id/a', 'status/d'
        ],
        'rule'  => AdminValidate::class
    ];

    protected array $profileColumns = [
        'password', 'avatar', 'name', 'email'
    ];

    public function get(string $type = 'param', bool $validate = true): array
    {
        $isProfile = $this->formValidate->getScene() === 'profile';
        $data = parent::get($type, $validate);
        if ($isProfile) {
            $data = array_filter($data, function ($key) {
                return in_array($key, $this->profileColumns);
            }, ARRAY_FILTER_USE_KEY);
        }
        return $data;
    }

    /**
     * 生成表单
     * @param Model|null $model
     * @return array
     */
    public function buildProfileForm(Model $model = null): array
    {
        $form = $this->formAttrs($model);
        $elements = array_values(array_filter($this->elements($model), function ($item) {
            return in_array($item['prop'], $this->profileColumns);
        }));

        array_unshift($elements, [
            "prop"  => "username",
            "label" => "账户名",
            "type"  => "label",
            "value" => $model->username
        ]);

        $this->improveElements($elements, $form);

        return ['form' => $form, 'elements' => $elements];
    }

    public function elements(Model $model = null): array
    {
        $values = $model ? $model->toArray() : [];
        return [
            [
                "prop"         => "username",
                "label"        => "用户名",
                "value"        => $values['username'] ?? '',
                "type"         => "input",
                "autocomplete" => "new-username",
                "rules"        => [
                    ["required" => true, "message" => "用户名不能为空"],
                ]
            ],
            [
                "prop"      => "password",
                "label"     => "密码",
                "type"      => "password",
                "autocomplete" => "new-username",
                "introEdit" => "不修改密码请留空",
                "rules"     => empty($model) ? [
                    ["required" => true, "message" => "密码不能为空"],
                ] : []
            ],
            [
                "prop"  => "avatar",
                "label" => "头像",
                'type'  => 'upload:image',
                "value" => $values['avatar'] ?? '',
            ],
            [
                "prop"    => "name",
                "label"   => "姓名",
                "value"   => $values['name'] ?? '',
                "type"    => "input",
                "rules"   => [
                    ["required" => true, "message" => "姓名不能为空"],
                ],
                "colSpan" => 12,
            ],
            [
                "prop"    => "email",
                "label"   => "邮箱",
                "value"   => $values['email'] ?? '',
                "type"    => "input",
                "rules"   => [
                    ["required" => true, "message" => "邮箱不能为空"],
                ],
                "colSpan" => 12,
            ],
            [
                "prop"    => "role_id",
                "label"   => "角色",
                "type"    => "cascader",
                "value"   => $values['role_id'] ?? 0,
                "rules"   => [
                    ["required" => true, "message" => "角色不能为空"],
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