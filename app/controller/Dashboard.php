<?php
/**
 * Project: MofAdmin
 * Author: moufer <moufer@163.com>
 * Date: 2024/7/16 12:49
 */

namespace app\controller;

use app\library\Controller;
use mof\annotation\AdminPerm;
use mof\ApiResponse;
use think\response\Json;

#[AdminPerm(
    title: '控制台', url: 'system/dashboard', actions: '*',
    sort: 1, icon: 'Odometer', group: 'root'
)]
class Dashboard extends Controller
{
    public function index(): Json
    {
        return ApiResponse::success('ok');
    }
}