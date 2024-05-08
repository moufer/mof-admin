<?php

namespace app\event;

use app\model\Config;

class GetConfig
{
    public function handle(): void
    {
        // 获取配置信息
        $cacheKey = 'admin_config';
        $config = app('cache')->get($cacheKey);
        if (!$config) {
            $config = [];
            $rows = (new Config)->where(['module' => 'system'])->select();
            $rows->each(function ($row) use (&$config) {
                $config[$row['name']] = $row['value'];
            });
            app('cache')->set($cacheKey, $config);
        }
        //写入配置
        app('config')->set($config, 'system');
    }
}