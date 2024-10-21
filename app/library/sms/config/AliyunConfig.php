<?php
/**
 * Project: MofAdmin
 * Author: moufer <moufer@163.com>
 * Date: 2024/9/22 12:58
 */

namespace app\library\sms\config;

use app\library\sms\ConfigAbstract;

class AliyunConfig extends ConfigAbstract
{
    public function getName(): string
    {
        return '阿里云短信';
    }

    public function getFlag(): string
    {
        return 'aliyun';
    }

    public function getConfigForm(?array $values): array
    {
        return [
            [
                "label" => "AccessKey ID",
                "prop"  => "access_key_id",
                "value" => $values['access_key_id'] ?? "",
                "type"  => "input",
                "intro" => "阿里云短信平台AccessKey ID",
            ],
            [
                "label" => "AccessKey Secret",
                "prop"  => "access_key_secret",
                "value" => $values['access_key_secret'] ?? "",
                "type"  => "input",
                "intro" => "阿里云短信平台AccessKey Secret",
            ],
            [
                "label" => "地域ID",
                "prop"  => "region",
                "value" => $values['region'] ?? "cn-hangzhou",
                "type"  => "input",
                "intro" => "阿里云短信平台地域ID，默认：cn-hangzhou",
            ],
            [
                "label" => "短信签名",
                "prop"  => "sign_name",
                "value" => $values['sign_name'] ?? "",
                "type"  => "input",
                "intro" => "短信签名必须在阿里云短信平台通过审核后才能使用",
            ]
        ];
    }
}