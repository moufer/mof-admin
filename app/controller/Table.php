<?php

namespace app\controller;

use app\library\Request;
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
     * @param Request $request
     * @return Json
     */
    public function config($module, $name, Request $request): Json
    {
        $className = \mof\Module::namespace($module) . 'front\\table\\' . Str::studly($name) . 'Table';
        if (!class_exists($className)) {
            return ApiResponse::error('配置信息不存在' . $className);
        } else {
            $table = (new $className($request->param()))->getTableConfig();
            return ApiResponse::success($table);
        }
    }
}