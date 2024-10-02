<?php
/**
 * Project: AIGC
 * Author: moufer <moufer@163.com>
 * Date: 2024/9/23 14:48
 */

namespace app\library\sms;

use mof\exception\LogicException;

abstract class DriverAbstract implements DriverInterface
{
    protected array $config;
    protected array $templates = [];

    /**
     * @inheritDoc
     */
    public function setConfig(array $config): void
    {
        if (!$config) {
            throw new LogicException('短信参数未配置');
        }
        $this->config = $config;
        //获取短信模板
        foreach ($this->config as $key => $val) {
            if (str_starts_with($key, 'templates_')) {
                $keyName = substr($key, 10);
                $this->templates[$keyName] = $val;
            }
        }
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    protected function getTemplateId(string $template): string
    {
        //找$template对应的模板ID
        list($keyName, $action) = explode('.', $template);
        if (empty($this->templates[$keyName]) || empty($this->templates[$keyName][$action])) {
            throw new LogicException("短信模板配置错误");
        } else {
            return $this->templates[$keyName][$action];
        }
    }
}