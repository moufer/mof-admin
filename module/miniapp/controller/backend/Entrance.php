<?php

namespace module\miniapp\controller\backend;

use module\miniapp\library\MiniappController;
use mof\annotation\AdminPerm;
use mof\annotation\Description;
use mof\ApiResponse;
use mof\Module;
use think\response\Json;

#[AdminPerm(
    title: '入口页面', url: 'miniapp/entrance', actions: 'index',
    sort: 2, icon: 'Guide', group: 'common', category: 'miniapp'
)]
class Entrance extends MiniappController
{
    /**
     * @return Json
     */
    public function index(): Json
    {
        $module = $this->miniapp->getData('module');
        if (!$moduleInfo = Module::info($module)) {
            return ApiResponse::error('模块不存在');
        }
        $result = $moduleInfo['entrance'] ?? [];
        return ApiResponse::success($result);
    }
}