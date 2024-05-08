<?php

namespace module\miniapp\validate;

class AdminValidate extends \app\validate\AdminValidate
{
    protected $rule = [
        'username' => 'require|alphaDash|unique:system_admin',
        'name'     => 'require',
        'email'    => 'require|email|unique:system_admin',
        'password' => 'requireCallback:requireCheckPassword|length:6,20',
        'miniapp_ids'  => 'require|array',
    ];

    protected $message = [
        'username.require'   => '用户名不能为空',
        'username.alphaDash' => '用户名只能包含字母、数字和下划线',
        'username.unique'    => '用户名已存在',
        'name.require'       => '姓名不能为空',
        'email.require'      => '邮箱不能为空',
        'email.email'        => '邮箱格式不正确',
        'email.unique'       => '邮箱已存在',
        'password.require'   => '密码不能为空',
        'password.length'    => '密码长度为6-20位',
        'miniapp_ids.require'    => '未关联小程序',
        'miniapp_ids.array'      => '关联小程序格式错误',
    ];

    protected $scene = [
        'add'  => ['username', 'password', 'name', 'email', 'miniapp_ids'],
        'edit' => ['username', 'password', 'name', 'email', 'miniapp_ids'],
    ];

    protected function requireCheckPassword($value, $data = []): bool
    {
        if ('edit' === $this->currentScene && empty($value)) {
            return false;
        }
        return true;
    }
}