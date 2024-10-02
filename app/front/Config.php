<?php

namespace app\front;

use app\library\Sms;
use mof\annotation\Description;

class Config extends \mof\front\Config
{
    #[Description('基础设置')]
    public function groupBase(array $values): array
    {
        return [
            [
                "label"  => "站点名称",
                "prop"   => "site_name",
                "type"   => "input",
                "value"  => $values['site_name'] ?? "",
                "intro"  => "请填写站点名称。",
                "_extra" => ['client_cfg' => 1],
            ], [
                "label"  => "备案号",
                "prop"   => "site_icp",
                "type"   => "input",
                "value"  => $values['site_icp'] ?? "",
                "intro"  => "请填写备案号。",
                "_extra" => ['client_cfg' => 1],
            ], [
                "label" => "禁止访问IP",
                "prop"  => "site_banip",
                "type"  => "textarea",
                "value" => $values['site_banip'] ?? "",
                "intro" => "禁止登录的IP，一行一条记录。"
            ], [
                "label"  => "关闭网站",
                "prop"   => "site_close",
                "type"   => "switch",
                "value"  => boolval($values['site_close'] ?? 0),
                "intro"  => "禁止访客访问网站。",
                "_extra" => ['client_cfg' => 1],
            ]
        ];
    }

    #[Description('附件设置')]
    public function groupStorage(array $values): array
    {
        return [
            [
                "label"   => "上传平台",
                "prop"    => "storage_provider",
                "type"    => "select",
                "intro"   => "选择附件上传平台，相关配置请到 config/filesystem.php 文件中配置。",
                "value"   => $values['storage_driver'] ?? "public",
                "options" => [
                    ["label" => "本地", "value" => "public"],
                    ["label" => "腾讯云", "value" => "qcloud"],
                    ["label" => "阿里云", "value" => "oss"],
                    ["label" => "七牛云", "value" => "qiniu"],
                ],
            ], [
                "label"  => "附件域名",
                "prop"   => "storage_domain",
                "type"   => "input",
                "value"  => $values['storage_domain'] ?? "",
                "intro"  => "选择上传本地时，请留空。附件域名格式如 https://cdn.domain.com",
                "_extra" => ['client_cfg' => 1],
            ], [
                "label"  => "允许上传的图片格式",
                "prop"   => "storage_image_ext",
                "type"   => "input",
                "intro"  => "图片文件类型，如jpg,png,gif等，多个用逗号分隔",
                "value"  => $values['storage_image_ext'] ?? "jpg,png,jpeg,gif",
                "_extra" => ['client_cfg' => 1],
            ], [
                "label"  => "允许上传的媒体格式",
                "prop"   => "storage_media_ext",
                "type"   => "input",
                "value"  => $values['storage_media_ext'] ?? "mp4,mp3",
                "intro"  => "多媒体文件类型，如mp4,avi,mp3,wav等",
                "_extra" => ['client_cfg' => 1],
            ], [
                "label"  => "允许上传的附件格式",
                "prop"   => "storage_file_ext",
                "type"   => "input",
                "value"  => $values['storage_file_ext'] ?? "pdf",
                "intro"  => "其他文件类型，如pdf,zip,rar等",
                "_extra" => ['client_cfg' => 1],
            ], [
                "label"  => "图片文件最大限制",
                "prop"   => "storage_image_size",
                "type"   => "input",
                "intro"  => "限制图片文件大小，单位为MB，留空不限制",
                "value"  => $values['storage_image_size'] ?? 5,
                "append" => "MB",
                "_extra" => ['client_cfg' => 1],
            ], [
                "label"  => "媒体文件最大限制",
                "prop"   => "storage_media_size",
                "type"   => "input",
                "intro"  => "同上",
                "value"  => $values['storage_media_size'] ?? 5,
                "append" => "MB",
                "_extra" => ['client_cfg' => 1],
            ], [
                "label"  => "其他附件最大限制",
                "prop"   => "storage_file_size",
                "type"   => "input",
                "intro"  => "同上",
                "value"  => $values['storage_file_size'] ?? 5,
                "append" => "MB",
                "_extra" => ['client_cfg' => 1],
            ], [
                "label"  => "上传图片最大尺寸限制",
                "prop"   => "storage_image_wh",
                "type"   => "input",
                "intro"  => "设置图片最大尺寸，大于限制时，系统会缩小后保存。填写格式为宽x高，如：1000x1000，留空为不限制。",
                "value"  => $values['storage_image_wh'] ?? '1000x1000',
                "append" => "PX",
                "_extra" => ['client_cfg' => 1],
            ]
        ];
    }

    #[Description('邮箱设置')]
    public function groupMail(array $values): array
    {
        return [
            [
                "label"   => "发送方式",
                "prop"    => "email_type",
                "type"    => "select",
                "intro"   => "邮件发送方式。",
                "value"   => $values['email_type'] ?? "",
                "options" => [
                    ['label' => '不启用', 'value' => ''],
                    ["label" => "SMTP", "value" => "smtp"]
                ]
            ], [
                "label"   => "SMTP服务器",
                "prop"    => "smtp_server",
                "type"    => "input",
                "intro"   => "SMTP服务器。",
                "value"   => $values['smtp_server'] ?? "",
                "colSpan" => 8
            ], [
                "label"   => "SMTP加密方式",
                "prop"    => "smtp_encryption",
                "type"    => "select",
                "intro"   => "SMTP加密方式。",
                "value"   => $values['smtp_encryption'] ?? "",
                "options" => [
                    ["label" => "SSL", "value" => "ssl"],
                    ["label" => "TLS", "value" => "tls"]
                ],
                "colSpan" => 8
            ], [
                "label"   => "SMTP端口",
                "prop"    => "smtp_port",
                "type"    => "input",
                "intro"   => "SSL加密时一般使用465端口，如果使用TLS加密，则端口可能为587",
                "value"   => $values['smtp_port'] ?? "",
                "colSpan" => 8
            ], [
                "label" => "SMTP账号",
                "prop"  => "smtp_account",
                "type"  => "input",
                "intro" => "SMTP账号。",
                "value" => $values['smtp_account'] ?? ""
            ], [
                "label" => "SMTP密码",
                "prop"  => "smtp_password",
                "type"  => "password",
                "intro" => "SMTP密码。",
                "value" => $values['smtp_password'] ?? ""
            ], [
                "label" => "发件人邮箱",
                "prop"  => "smtp_sender_email",
                "type"  => "input",
                "intro" => "SMTP发件人邮箱。",
                "value" => $values['smtp_sender_email'] ?? ""
            ], [
                "label" => "发件人名称",
                "prop"  => "smtp_sender_name",
                "type"  => "input",
                "intro" => "SMTP发件人名称。",
                "value" => $values['smtp_sender_name'] ?? ""
            ]
        ];
    }

    #[Description('短信设置')]
    public function groupSms(array $values): array
    {
        $classes = Sms::getConfigs();

        $options = array_map(function ($item) {
            return [
                'label' => $item->getName(),
                'value' => $item->getFlag()
            ];
        }, $classes);

        $configOptions = []; //平台对应的配置数组
        foreach ($classes as $class) {
            $keyName = "sms_" . $class->getFlag();
            $oneValues = $values[$keyName] ?? [];  //获取对应的数据
            $cfgData = $class->getConfigForm($oneValues);
            $tplData = $class->getTemplatesForm($oneValues);
            //获取选项并合并
            array_push($configOptions, ...array_map(function ($item) use ($class, $keyName) {
                //prop 加上 sms_xxx 前缀，前缀相同的为一个数组
                $item['prop'] = $keyName . '.' . $item['prop'];
                $item['_visible'] = (empty($item['_visible']) ? '' : '&') . "sms_driver=" . $class->getFlag();
                return $item;
            }, array_merge($cfgData, $tplData)));
        }

        return [
            [
                "label"   => "短信平台",
                "prop"    => "sms_driver",
                "type"    => "select",
                "intro"   => "选择短信平台。",
                "value"   => $values['sms_driver'] ?? "",
                "options" => [['label' => '不启用', 'value' => ''], ...$options]
            ],
            ...$configOptions,
        ];
    }
}