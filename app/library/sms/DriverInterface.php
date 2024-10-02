<?php
/**
 * Project: MofAdmin
 * Author: moufer <moufer@163.com>
 * Date: 2024/9/22 13:51
 */

namespace app\library\sms;

interface DriverInterface
{
    /**
     * 设置配置参数
     * @param array $config
     * @return void
     */
    public function setConfig(array $config): void;

    /**
     * 获取配置参数
     * @return array
     */
    public function getConfig(): array;

    /**
     * 发送一条短信
     * @param string $mobile 手机号
     * @param string $template 模板标识，格式：模块名.动作标识，如：user.register
     * @param array $params 参数数组
     * @return bool
     */
    public function send(string $mobile, string $template, array $params): bool;
}