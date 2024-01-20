<?php

namespace app\table;

use app\library\FormComponentOptions;
use app\model\Admin;
use app\model\Module;
use app\model\Role;
use mof\front\Table;
use mof\utils\ElementData;

class UserTable extends Table
{
    protected bool $showSearch = false;

    protected function columnId(): array
    {
        return [
            "prop"   => 'id',
            "label"  => 'ID',
            "search" => true,
        ];
    }

    protected function columnAvatar(): array
    {
        return [
            "prop"  => "avatar",
            "label" => "头像",
            "type"  => "avatar",
            "form"  => FormComponentOptions::fill(['type' => 'upload:image']),
        ];
    }

    protected function columnUsername(): array
    {
        return [
            "prop"   => "username",
            "label"  => "用户名",
            "width"  => "*",
            "search" => true,
            "form"   => [
                "rules" => [
                    ["required" => true],
                ]
            ]
        ];
    }

    protected function columnPassword(): array
    {
        return [
            "prop"    => "password",
            "label"   => "密码",
            "visible" => false,
            "form"    => [
                "type"      => "password",
                "introEdit" => "不修改密码请留空",
                "rulesAdd"  => [
                    ["required" => true],
                ]
            ]
        ];
    }

    protected function columnName(): array
    {
        return [
            "prop"  => "name",
            "label" => "姓名",
            "width" => "*",
            "form"  => [
                "rules" => [
                    ["required" => true],
                ]
            ],
        ];
    }

    protected function columnEmail(): array
    {
        return [
            "prop"  => "email",
            "label" => "邮箱",
            "width" => 250,
            "form"  => [
                "rules" => [
                    ["required" => true],
                ]
            ],
        ];
    }

    protected function columnRoleId(): array
    {
        $rows = Role::where('status', '=', 1)
            ->order('id', 'asc')->select()->toArray();
        $selectOptions = ElementData::make($rows)->toSelectOptions('name', 'id');

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
        $cascaderOptions = ElementData::make($rows)->toCascaderOptions('id', 'name');

        return [
            "prop"      => "role.name",
            "propAlias" => "role_id",
            "label"     => "角色",
            "type"      => "select",
            "options"   => $selectOptions,
            "search"    => [
                'type'      => 'select',
                'clearable' => true
            ],
            "form"      => [
                "type"    => "cascader",
                "rules"   => [
                    ["required" => true],
                ],
                'options' => $cascaderOptions,
            ],
        ];
    }

    protected function columnStatus(): array
    {
        return [
            "prop"    => "status",
            "label"   => "状态",
            "type"    => "select",
            "options" => Admin::statusOptions(),
            "search"  => [
                'type'      => 'select',
                'clearable' => true,
            ],
            "form"    => [
                '_defaultValue' => 1
            ]
        ];
    }

    protected function columnCreateAt(): array
    {
        return [
            "prop"   => "create_at",
            "label"  => "添加时间",
            "type"   => "datetime",
            "search" => [
                'type' => 'datetimerange',
            ]
        ];
    }
}