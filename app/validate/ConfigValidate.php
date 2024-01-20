<?php

namespace app\validate;

use mof\Validate;

class ConfigValidate extends Validate
{
    protected $rule = [
        'name'  => 'require|alphaDash|unique:config',
        'title' => 'require',
        'group' => 'require',
        'type'  => 'require|in:text,textarea,number,float,switch,kv,uploadimage,uploadfile',
    ];

    protected $message = [
        'name.require'   => '参数名称不能为空',
        'name.alphaDash' => '参数名称只能为字母、数字、下划线',
        'title.require'  => '参数标题不能为空',
        'group.require'  => '参数分组不能为空',
        'type.require'   => '参数类型不能为空',
        'type.in'        => '参数类型不正确',
    ];

}