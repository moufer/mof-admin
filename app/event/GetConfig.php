<?php

namespace app\event;

use app\model\Config;
use app\model\Module;
use mof\utils\Arr;

class GetConfig
{
    public function handle(): void
    {
        // 获取配置信息
        if ($configs = $this->getConfig()) {
            foreach ($configs as $name => $config) {
                $convertArray = false; //需要转换成多维数组
                $moduleConfig = array_reduce($config, function ($carry, $row) use (&$convertArray) {
                    if (!$convertArray && strpos($row['name'], '.') > 0) {
                        $convertArray = true; // 存在.，则需要转换数组
                    }
                    $carry[$row['name']] = $row['value'];
                    return $carry;
                });
                //一维数组换成多维数组
                $convertArray && $moduleConfig = Arr::coverToMultidimensional($moduleConfig);
                //更新config
                $this->setConfig($moduleConfig ?? [], $name);
            }
        }
    }

    protected function getConfig(): array
    {
        $modules = array_map(fn($m) => $m['name'], Module::enabledModules());
        //找到system，从中删除
        $key = array_search('system', $modules);
        if ($key !== false) unset($modules[$key]);
        //把system放在最前面
        array_unshift($modules, 'system');

        $result = array_reduce($modules, function ($carry, $name) {
            $cacheKey = "{$name}_config";
            if (!$config = app('cache')->get($cacheKey)) {
                $rows = (new Config)->where([
                    'module' => $name, 'extend_type' => '', 'extend_id' => 0])->select();
                //一维数组转换成多位数组
                $config = $rows->toArray();
                app('cache')->set($cacheKey, $config);
            }
            if ($config || $name == 'system') {
                //确保system配置存在，不管是否有配置
                $carry[$name] = $config;
            }
            return $carry;
        });
        return $result ?? [];
    }

    protected function setConfig(array $config, $name): void
    {

        app('config')->set($config, $name);
    }
}