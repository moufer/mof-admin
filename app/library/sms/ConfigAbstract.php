<?php
/**
 * Project: AIGC
 * Author: moufer <moufer@163.com>
 * Date: 2024/9/22 12:56
 */

namespace app\library\sms;

use app\front\Config;

abstract class ConfigAbstract implements ConfigInterface
{
    public function getTemplatesForm(?array $values): array
    {
        global $classes;

        if (empty($classes)) {
            //获取模块列表
            $modules = \mof\Module::getEnabledModules();
            foreach ($modules as $module) {
                $configFile = \mof\Module::path($module) . 'front' . DIRECTORY_SEPARATOR . 'Config.php';
                if (!is_file($configFile)) continue;
                $className = "\\module\\{$module}\\front\\Config";
                if (class_exists($className) && method_exists($className, 'smsTemplate')) {
                    $classes[$module] = new $className;
                }
            }
            if (empty($classes)) return [];
        }

        return array_map(function (string $module, \mof\front\Config $class) use($values) {
            $prop = "templates_$module";
            $config = $class->smsTemplate($values[$prop] ?? []);
            $config['prop'] = $prop;
            return $config;
        }, array_keys($classes), array_values($classes));
    }
}