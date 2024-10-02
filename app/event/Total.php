<?php
/**
 * Project: AIGC
 * Author: moufer <moufer@163.com>
 * Date: 2024/9/29 21:51
 */

namespace app\event;

/**
 * 统计事件
 * 数据累加
 */
class Total
{
    public function handle($params): void
    {
        if (!strpos($params['name'], '.')) return;
        list($module, $name) = explode('.', $params['name']);

        $where = [
            'day'    => date('Y-m-d'),
            'module' => $module,
            'name'   => $name,
        ];

        if ($row = \app\model\Total::where($where)->find()) {
            $row->count += $params['step'] ?? 1;
        } else {
            $row = new \app\model\Total();
            $row->day = date('Y-m-d');
            $row->module = $module;
            $row->name = $name;
            $row->count = $params['step'] ?? 1;
        }

        $row->save();
    }
}