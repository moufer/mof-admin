<?php
/**
 * Project: MofAdmin
 * Author: moufer <moufer@163.com>
 * Date: 2024/9/22 13:31
 */

namespace app\library;

use app\library\sms\ConfigInterface;
use app\library\sms\DriverInterface;
use mof\exception\LogicException;
use think\exception\ClassNotFoundException;
use think\helper\Str;

class Sms
{
    /**
     * 获取短信配置类
     * @param string $name
     * @return ConfigInterface
     */
    public static function getConfig(string $name): ConfigInterface
    {
        if (!$drivers = self::getConfigs($name)) {
            throw new ClassNotFoundException('sms config not found', $name);
        }
        return array_pop($drivers);
    }

    /**
     * 获取短信驱动类
     * @param string $name
     * @return DriverInterface
     */
    public static function getDriver(string $name): DriverInterface
    {
        $config = config('system.sms_' . $name);
        if (empty($config)) {
            throw new LogicException('短信参数未配置');
        }
        if (!$drivers = self::getDrivers($name)) {
            throw new ClassNotFoundException('sms driver not found', $name);
        }
        $class = array_pop($drivers);
        $class->setConfig($config);
        return $class;
    }

    /**
     * @param string $name
     * @return ConfigInterface[]|null
     */
    public static function getConfigs(string $name = ''): ?array
    {
        return self::getClasses('config', $name);
    }

    /**
     * @param string $name
     * @return DriverInterface[]|null
     */
    public static function getDrivers(string $name = ''): ?array
    {
        return self::getClasses('driver', $name);
    }

    /**
     * @param string $type 类类型(config, driver)
     * @param string $name 类名
     * @return array|null
     */
    protected static function getClasses(string $type, string $name = ''): ?array
    {
        $namespace = __NAMESPACE__ . '\\sms\\' . $type . '\\';
        $dir = __DIR__ . DIRECTORY_SEPARATOR . 'sms' . DIRECTORY_SEPARATOR . $type;
        $appendExt = ucfirst($type) . '.php';

        $files = scandir($dir);
        $classes = [];
        foreach ($files as $file) {
            if (in_array($file, ['.', '..'])) continue;
            if (!str_ends_with($file, $appendExt)) continue; //检查后缀
            if (!$name || $name === Str::snake(substr($file, 0, -strlen($appendExt)))) {
                $class = $namespace . basename($file, '.php');
                $classes[] = new $class;
            }
        }

        return $classes ?: null;
    }
}