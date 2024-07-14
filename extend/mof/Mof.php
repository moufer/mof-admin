<?php

namespace mof;

use mof\command\ControllerCreate;
use mof\command\MigrateCreate;
use mof\command\MigrateRollback;
use mof\command\MigrateRun;
use mof\command\ModelCreate;
use mof\command\ModuleCreate;
use mof\command\SeedCreate;
use mof\command\SeedRun;

class Mof
{
    public static function namespaceToDir($namespace): string
    {
        $dir = str_replace('\\', DIRECTORY_SEPARATOR, $namespace);
        return app()->getRootPath() . $dir;
    }

    /**
     * 获取文件访问地址
     * @param $path
     * @param string $provider
     * @return string
     */
    public static function storageUrl($path, string $provider = ''): string
    {
        if (empty(trim($path))) return '';
        empty($provider) && $provider = config('filesystem.default');
        $path = str_replace('\\', '/', $path);
        if (str_starts_with($path, 'http')) {
            return $path;
        }
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
     * 去掉附件链接前缀
     * @param $url
     * @param string $provider
     * @return string
     */
    public static function removeStorageUrl($url, string $provider = ''): string
    {
        if (str_starts_with($url, 'http')) {
            $config = config('filesystem');
            empty($provider) && $provider = $config['default'];
            $preUrl = $config['disks'][$provider]['url'] ?? '/';
            if (!str_starts_with($preUrl, 'http')) {
                $preUrl = app('request')->domain() . $preUrl;
            }
            $preUrl = rtrim($preUrl, '/') . '/';
            $url = str_replace($preUrl, '', $url);
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
            ModuleCreate::class,
            ControllerCreate::class,
            ModelCreate::class,
            MigrateCreate::class,
            MigrateRun::class,
            MigrateRollback::class,
            SeedCreate::class,
            SeedRun::class,
        ];
    }
}