<?php
/**
 * Project: MofAdmin
 * Author: moufer <moufer@163.com>
 * Date: 2024/10/18 21:47
 */

namespace app\logic;

use app\model\AdminLoginLog;
use mof\annotation\Inject;
use mof\Logic;


class AdminLoginLogLogic extends Logic
{
    #[Inject(AdminLoginLog::class)]
    protected $model;


}