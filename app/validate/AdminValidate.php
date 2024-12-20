<?php

namespace app\validate;

use mof\Validate;

class AdminValidate extends Validate
{
    protected $rule = [
        'username' => 'require|alphaDash|unique:system_admin',
        'name'     => 'require|unique:system_admin',
        'email'    => 'require|email|unique:system_admin',
        'password' => 'requireCallback:requireCheckPassword|length:6,20',
        'role_id'  => 'require',
    ];

    protected $message = [
        'username.require'   => '用户名不能为空',
        'username.alphaDash' => '用户名只能包含字母、数字和下划线',
        'username.unique'    => '用户名已存在',
        'name.require'       => '姓名不能为空',
        'name.unique'        => '姓名已存在',
        'email.require'      => '邮箱不能为空',
        'email.email'        => '邮箱格式不正确',
        'email.unique'       => '邮箱已存在',
        'password.require'   => '密码不能为空',
        'password.length'    => '密码长度为6-20位',
        'role_id.require'    => '角色不能为空',
    ];

    protected $scene = [
        'add'     => ['username', 'password', 'name', 'email', 'role_id'],
        'edit'    => ['username', 'password', 'name', 'email', 'role_id'],
        'profile' => ['password', 'name', 'email'],
    ];

    protected function requireCheckPassword($value, $data = []): bool
    {
        if (in_array($this->currentScene, ['edit', 'profile']) && empty($value)) {
            return false;
        }
        return true;
    }
}