<?php

namespace app\validate;

use mof\Validate;

class AdminMenuValidate extends Validate
{
    protected $rule = [
        'parent_id' => 'require',
        'name'      => 'require|unique:admin_menu',
        'title'     => 'require',
        'type'      => 'require|in:group,link',
    ];

    protected $message = [
        'parent_id.require' => '父级菜单不能为空',
        'name.require'      => '菜单标识不能为空',
        'name.unique'       => '菜单标识已存在',
        'title.require'     => '菜单名称不能为空',
        'type.require'      => '菜单类型不能为空',
        'type.in'           => '菜单类型不正确',
    ];
}