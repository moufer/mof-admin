<?php

namespace module\miniapp\controller\backend;

use module\miniapp\library\MiniappController;
use mof\ApiResponse;
use mof\Module;
use think\response\Json;

/**
 * 入口页面
 */
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