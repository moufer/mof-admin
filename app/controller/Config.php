<?php

namespace app\controller;

use app\library\Controller;
use mof\annotation\AdminPerm;
use mof\ApiResponse;
use think\db\exception\DbException;
use think\facade\Cache;
use think\response\Json;

#[AdminPerm(
    title: '系统设置', url: 'system/config', actions: 'options,submit',
    sort: 1, icon: 'Setting', group: 'system'
)]
class Config extends Controller
{
    use \app\concern\Config;
}