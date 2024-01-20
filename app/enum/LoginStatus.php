<?php

namespace app\enum;

use mof\annotation\Description;
use mof\concern\EnumExtend;

enum LoginStatus: int
{
    use EnumExtend;

    #[Description('用户不存在')]
    case NOT_FOUND = -1;

    #[Description('密码错误')]
    case PASSWORD_WRONG = -2;

    #[Description('不是模块独立权限管理员')]
    case NOT_MODULE_ADMIN = -3;

    #[Description('登录成功')]
    case SUCCESS = 1;

    public function label(): string
    {
        return $this->getDescription($this->name);
    }
}