<?php

namespace app\front\table;

use app\model\Role;
use app\model\Admin;
use mof\front\Table;
use mof\enum\StatusEnum;
use mof\utils\ElementData;

class UserTable extends Table
{
    protected bool  $showSearch     = false;
    protected array $toolbarButtons = ['add', 'refresh', 'search'];
    protected bool  $tableSelection = false;

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
        ];
    }

    protected function columnUsername(): array
    {
        return [
            "prop"   => "username",
            "label"  => "用户名",
            "width"  => "*",
            "search" => true,
        ];
    }

    protected function columnName(): array
    {
        return [
            "prop"  => "name",
            "label" => "姓名",
            "width" => "*",
        ];
    }

    protected function columnEmail(): array
    {
        return [
            "prop"  => "email",
            "label" => "邮箱",
            "width" => 150,
        ];
    }

    protected function columnRoleId(): array
    {
        $rows = Role::where('status', '=', 1)
            ->order('id', 'asc')
            ->select()
            ->toArray();

        return [
            "prop"      => "role_id",
            "label"     => "角色",
            "type"      => "select",
            "options"   => ElementData::make($rows)->toSelectOptions('name', 'id'),
        ];
    }

    protected function columnStatus(): array
    {
        return [
            "prop"    => "status",
            "label"   => "状态",
            "type"    => "select",
            "options" => StatusEnum::toDict(),
            "search"  => [
                'type'      => 'select',
                'clearable' => true,
            ],
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
