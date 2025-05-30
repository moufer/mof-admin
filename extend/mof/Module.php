<?php

namespace mof;

use mof\front\Config;
use mof\front\ConfigInterface;
use think\facade\Route;

class Module
{
    /**
     * 获取模块路径
     * @param $name
     * @return string
     */
    public static function path($name): string
    {
        if ('system' == $name) {
            return app_path();
        }
        return root_path('module') . $name . DIRECTORY_SEPARATOR;
    }

    /**
     * 获取模块命名空间
     * @param $name
     * @return string
     */
    public static function namespace($name): string
    {
        if ('system' == $name) {
            return '\\app\\';
        }
        return '\\module\\' . $name . '\\';
    }

    /**
     * 获取模块列表
     * @return array
     */
    public static function list(): array
    {
        //从module目录下获取所有目录
        $dirs = scandir(root_path('module'));
        $modules = [];
        //要忽略的目录
        $ignore = ['.', '..', '.DS_Store', '.gitignore'];
        foreach ($dirs as $dir) {
            if (in_array($dir, $ignore) || !is_dir(static::path($dir))) {
                continue;
            }

            if ($module = self::info($dir)) {
                $module['order'] ??= 0;
                $modules[$module['name']] = $module;
            }
        }
        //根据order排序
        uasort($modules, function ($a, $b) {
            if ($a['order'] == $b['order']) {
                return 0;
            }
            return ($a['order'] < $b['order']) ? -1 : 1;
        });
        return $modules;
    }

    /**
     * 获取模块信息
     * @param $name
     * @return false|mixed
     */
    public static function info($name): mixed
    {
        //从 module/$name/module.json 中获取
        $file = static::path($name) . 'module.json';
        if (!file_exists($file)) {
            return false;
        }
        $json = file_get_contents($file);
        $module = $json ? json_decode($json, true) : false;
        if (!$module || $module['name'] !== $name) {
            return false;
        }
        return $module;
    }

    /**
     * 加载模块路由
     * @return void
     */
    public static function loadRoutes(): void
    {
        $modules = self::getEnabledModules();
        //加载模块路由
        foreach ($modules as $module) {
            $routeFile = static::path($module) . 'route.php';
            if (is_file($routeFile)) {
                Route::group($module, function () use ($routeFile) {
                    include $routeFile;
                });
            }
        }
    }

    /**
     * 检测模块的完整性
     * @param $name
     * @return bool
     */
    public static function verifyIntegrity($name): bool
    {
        if (!self::info($name)) {
            return false;
        }
        $files = [
            'controller',
            'module.json',
            'route.php',
        ];
        foreach ($files as $file) {
            $file = static::path($name) . $file;
            if (!file_exists($file)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 写入已启用的模块缓存
     * @param $modules
     * @return false|int
     */
    public static function writeEnabledModules($modules): bool|int
    {
        $result = array_map(fn ($module) => $module['name'], $modules);
        //写入一个php文件，内容是return $result的数组
        $file = runtime_path() . 'mof' . DIRECTORY_SEPARATOR . 'modules.php';
        if (!is_dir(dirname($file))) {
            mkdir(dirname($file), 0755, true);
        }
        return file_put_contents(
            $file,
            '<?php return ' . var_export($result, true) . ';'
        );
    }

    /**
     * 获取模块的migration目录
     * @param $module string  模块名
     * @return string
     */
    public static function getModuleMigrationPath(string $module): string
    {
        return static::path($module) . 'database' . DIRECTORY_SEPARATOR . 'migrations';
    }

    /**
     * 获取模块的seed目录
     * @param string $module
     * @return string
     */
    public static function getModuleSeedPath(string $module): string
    {
        return static::path($module) . 'database' . DIRECTORY_SEPARATOR . 'seeds';
    }

    /**
     * 获取模块对外访问目录
     * @param string $module 模块标识
     * @param string $type
     * @param bool $isProjectPublicDir 是否返回public目录
     * @return string
     */
    public static function getModulePublicPath(string $module, string $type = 'resource', bool $isProjectPublicDir = false): string
    {
        if ($isProjectPublicDir) {
            $dir = $type === 'system' ? ($type . DIRECTORY_SEPARATOR . 'app') : $type;
            return app()->getRootPath() . 'public' . DIRECTORY_SEPARATOR . $dir
                . DIRECTORY_SEPARATOR . $module;
        }
        return static::path($module) . 'public' . DIRECTORY_SEPARATOR . $type;
    }

    /**
     * 获取已启用的模块
     * @return array
     */
    public static function getEnabledModules(): array
    {
        static $modules = null;
        if ($modules) {
            return $modules;
        }

        $modules = [];
        $files = runtime_path() . 'mof' . DIRECTORY_SEPARATOR . 'modules.php';
        if (file_exists($files)) {
            $modules = include $files;
        }
        //去除数组里的键名
        $modules = array_values($modules);
        //检测是否存在admin模块
        if (!in_array('system', $modules)) {
            array_unshift($modules, 'system');
        }

        return $modules;
    }

    /**
     * 获取模块的服务类
     * @return array
     */
    public static function getServices(): array
    {
        $result = [];
        $modules = self::getEnabledModules();
        foreach ($modules as $module) {
            $serviceFile = static::path($module) . 'library' . DIRECTORY_SEPARATOR . 'Service.php';
            $className = static::namespace($module) . 'library\\Service';
            if (file_exists($serviceFile) && class_exists($className)) {
                $result[] = $className;
            }
        }
        return $result;
    }

    /**
     * 获取配置选项
     * @param string $module
     * @return ConfigInterface|null
     */
    public static function loadConfig(string $module): ?ConfigInterface
    {
        $file = static::path($module) . 'front' . DIRECTORY_SEPARATOR . 'Config.php';
        $className = static::namespace($module) . 'front\\Config';
        if (!file_exists($file) || !class_exists($className)) {
            return null;
        }
        return new $className();
    }

    /**
     * 通过命名空间获取模块名称
     * @param string $namespace
     * @return string|null
     */
    public static function getNameByNameSpace(string $namespace): ?string
    {
        $list = explode('\\', trim($namespace, '\\'));
        if ($list[0] === 'app') {
            return 'system';
        }
        if ($list[0] === 'module') {
            return $list[1];
        }
        return null;
    }
}
