<?php

namespace app\controller;

use mof\ApiController;
use mof\ApiResponse;
use think\helper\Str;
use think\response\Json;

class Table extends ApiController
{
    /**
     * 表格配置
     * @param $module
     * @param $name
     * @return Json
     */
    public function config($module, $name): Json
    {
        $className = \mof\Module::namespace($module) . 'front\\table\\' . Str::studly($name) . 'Table';
        if (!class_exists($className)) {
            return ApiResponse::error('配置信息不存在'.$className);
        } else {
            $table = (new $className)->getTableConfig();
            return ApiResponse::success($table);
        }
    }
}