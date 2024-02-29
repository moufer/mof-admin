<?php

namespace app\front\form;

use mof\front\Form;

class PassportForm extends Form
{
    protected array $validate = [
        'param' => [
            'username', 'password', 'module'
        ],
        'rule'  => [
            'username|用户名' => 'require',
            'password|密码'   => 'require',
            'module|模块名'   => 'require',
        ],
    ];
}