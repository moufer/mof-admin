<?php

namespace app\controller;

use mof\BaseController;
use mof\ApiResponse;
use think\helper\Str;
use think\response\Json;

class Table extends BaseController
{
    /**
     * 表格配置
     * @param $module
     * @param $name
     * @return Json
     */
    public function config($module, $name): Json
    {
        $className = \mof\Module::namespace($module) . 'table\\' . Str::studly($name) . 'Table';
        if (!class_exists($className)) {
            return ApiResponse::error('配置信息不存在');
        } else {
            $table = (new $className)->getTableConfig();
            return ApiResponse::success($table);
        }
    }
}