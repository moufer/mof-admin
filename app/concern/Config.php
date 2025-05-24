<?php

namespace app\concern;

use app\library\Controller;
use mof\annotation\Description;
use mof\ApiResponse;
use mof\utils\Arr;
use think\db\exception\DbException;
use think\facade\Cache;
use think\response\Json;

/**
 * 配置参数
 * @mixin Controller
 */
trait Config
{
    /**
     * 参数选项
     * @param $module string 模块名称
     * @return Json
     */
    #[Description('参数选项')]
    public function options(string $module): Json
    {
        if (!$class = \mof\Module::loadConfig($module)) {
            return ApiResponse::error('配置选项不存在');
        }
        $values = $this->values($module);
        $form = $class->build($values);
        return ApiResponse::success($form);
    }

    /**
     * 提交配置参数
     * @param $module
     * @return Json
     */
    #[Description('提交配置参数')]
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
        $options = $class->build([], false);
        $saved = false;
        foreach ($options as $option) {
            if (!isset($option['prop'])) continue;
            $prop = $option['prop'];
            $extendInfo = $this->extendInfo();

            if (isset($rows[$prop])) {
                //查找是否已存在
                $model = (new \app\model\Config)->where([
                    'module'      => $module,
                    'name'        => $prop,
                    'extend_type' => $extendInfo['extend_type'],
                    'extend_id'   => $extendInfo['extend_id'],
                ])->findOrEmpty();
                //如果配置参数不存在，创建新的配置参数
                if ($model->isEmpty()) {
                    $model->setAttr('module', $module);
                    $model->setAttr('name', $prop);
                    $model->setAttr('extend_type', $extendInfo['extend_type']);
                    $model->setAttr('extend_id', $extendInfo['extend_id']);
                }
                //设置配置参数的值
                $model->setAttr('type', $option['type']);
                $model->setAttr('value', $rows[$prop]);
                $model->setAttr('extra', $option['_extra'] ?? []);
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
     * @param array $extraInfo
     * @return array
     */
    protected function values($module, array $extraInfo = []): array
    {
        try {
            $result = [];
            $convertArray = false;
            $where = ['module' => $module, ...$extraInfo];
            //为了触发setValueAttr，必须使用select方法
            $rows = (new \app\model\Config())->where($where)->select();
            $rows->each(function ($item) use (&$result, &$convertArray) {
                $result[$item->name] = $item->value;
                if (!$convertArray && strpos($item->name, '.') > 0) {
                    $convertArray = true; // 存在.，则需要转换数组
                }
            });
            return $convertArray ? Arr::coverToMultidimensional($result) : $result;
        } catch (DbException) {
            return [];
        }
    }

    /**
     * 扩展信息
     * @return array
     */
    protected function extendInfo(): array
    {
        return [
            'extend_type' => '',
            'extend_id'   => 0
        ];
    }
}