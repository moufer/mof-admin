<?php

namespace mof;

use mof\command\MigrateCreate;
use mof\command\MigrateRollback;
use mof\command\MigrateRun;
use mof\command\SeedCreate;
use mof\command\SeedRun;

class Mof
{
    /**
     * 获取文件访问地址
     * @param $path
     * @param string $provider
     * @return string
     */
    public static function storageUrl($path, string $provider = ''): string
    {
        empty($provider) && $provider = config('filesystem.default');
        $path = str_replace('\\', '/', $path);
        try {
            $url = app('filesystem')->disk($provider)->url($path);
        } catch (\Exception $e) {
            $url = $path;
        }
        if (str_starts_with($url, '/')) {
            $url = app('request')->domain() . $url;
        }
        return $url;
    }

    /**
     * 获取框架提供的命令行
     * @return string[]
     */
    public static function getCommands(): array
    {
        return [
            MigrateCreate::class,
            MigrateRun::class,
            MigrateRollback::class,
            SeedCreate::class,
            SeedRun::class,
        ];
    }
}