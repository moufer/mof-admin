<?php

namespace app\model;

class AdminLoginLog extends \mof\Model
{
    protected $name = 'system_admin_login_log';

    protected array $searchFields = [
        'username' => 'string',
    ];
}