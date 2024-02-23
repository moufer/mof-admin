<?php

namespace app\controller;

use app\library\Controller;
use mof\ApiResponse;
use think\db\exception\DbException;
use think\facade\Cache;
use think\response\Json;

class Config extends Controller
{
    /**
     * 获取参数选项
     * @param $module string 模块名称
     * @return Json
     */
    public function options(string $module): Json
    {
        if (!$class = \mof\Module::loadConfig($module)) {
            return ApiResponse::error('配置选项不存在');
        }
        $values = $this->values($module);
        $options = $class->options($values);
        return ApiResponse::success($options);
    }

    /**
     * 提交配置参数
     * @param $module
     * @return Json
     */
    public function submit($module): Json
    {
        if (!$class = \mof\Module::loadConfig($module)) {
            return ApiResponse::error('配置选项不存在');
        }
        $rows = $this->request->param('rows/a', []);
        if (!$rows) {
            return ApiResponse::error('未提供任何配置参数');
        }
        //获取配置参数选项
        $options = $class->options([], false);
        $saved = false;
        foreach ($options as $option) {
            $prop = $option['prop'];
            if (isset($rows[$prop])) {
                $model = (new \app\model\Config)
                    ->where(['module' => $module, 'name' => $prop])->findOrEmpty();
                //如果配置参数不存在，创建新的配置参数
                if ($model->isEmpty()) {
                    $model->setAttr('module', $module);
                    $model->setAttr('name', $prop);
                }
                //设置配置参数的值
                $model->setAttr('type', $option['type']);
                $model->setAttr('value', $rows[$prop]);
                $model->save();
                $saved = true;
            }
        }
        if ($saved) {
            Cache::delete("{$module}_config");
        }
        return ApiResponse::success();
    }

    /**
     * 获取配置参数的值
     * @param $module
     * @return array
     */
    private function values($module): array
    {
        try {
            $result = [];
            //为了触发setValueAttr，必须使用select方法
            $rows = (new \app\model\Config())->where(['module' => $module])->select();
            $rows->each(function ($item) use (&$result) {
                $result[$item->name] = $item->value;
            });
            return $result;
        } catch (DbException $e) {
            return [];
        }
    }

}