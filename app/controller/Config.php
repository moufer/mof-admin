<?php

namespace app\controller;

use app\library\Controller;
use mof\annotation\AdminPerm;

#[AdminPerm(
    title: '系统设置',
    url: 'system/config',
    actions: 'options,submit',
    sort: 1,
    icon: 'Setting',
    group: 'system'
)]
class Config extends Controller
{
    use \app\concern\Config;
}