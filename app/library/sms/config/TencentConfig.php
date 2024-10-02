<?php
/**
 * Project: MofAdmin
 * Author: moufer <moufer@163.com>
 * Date: 2024/9/22 12:58
 */

namespace app\library\sms\config;

use app\library\sms\ConfigAbstract;

class TencentConfig extends ConfigAbstract
{
    public function getName(): string
    {
        return '腾讯云短信';
    }

    public function getFlag(): string
    {
        return 'tencent';
    }

    public function getConfigForm(?array $values): array
    {
        return [
            [
                "label" => "SecretId",
                "prop"  => "secret_id",
                "value" => $values['secret_id'] ?? "",
                "type"  => "input",
                "intro" => "SecretId 与<a href='https://console.cloud.tencent.com/cam/capi' target='_blank'>API密钥管理</a>获取",
            ],
            [
                "label" => "SecretKey",
                "prop"  => "secret_key",
                "value" => $values['secret_key'] ?? "",
                "type"  => "input",
                "intro" => "SecretKey 仅支持在创建时查看。如遗忘，需重新创建密钥",
            ],
            [
                "label" => "SDK AppID",
                "prop"  => "sdk_app_id",
                "value" => $values['sdk_app_id'] ?? "",
                "type"  => "input",
                "intro" => "SDK AppID 在<a href='https://console.cloud.tencent.com/smsv2/app-manage' target='_blank'>应用列表</a>获取",
            ],
            [
                "label" => "App Key",
                "prop"  => "app_key",
                "value" => $values['app_key'] ?? "",
                "type"  => "input",
                "intro" => "App Key 与SDK AppID在同一个位置获取",
            ],
            [
                "label" => "短信签名",
                "prop"  => "sign_name",
                "value" => $values['sign_name'] ?? "",
                "type"  => "input",
                "intro" => "短信签名必须填写已审核通过的签名，例如：腾讯云",
            ],
            [
                "label" => "地域信息",
                "prop"  => "region",
                "value" => $values['region'] ?? "ap-guangzhou",
                "type"  => "input",
                "intro" => "默认地域信息：ap-guangzhou",
            ],
        ];
    }
}