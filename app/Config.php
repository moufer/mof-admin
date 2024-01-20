<?php

namespace app;

use mof\annotation\Description;
use mof\utils\ConfigOptions;

class Config extends ConfigOptions
{
    #[Description('基础设置')]
    public function groupBase(array $values): array
    {
        return [
            [
                "label" => "站点名称",
                "prop"  => "site_name",
                "type"  => "input",
                "value" => $values['site_name'] ?? "",
                "intro" => "请填写站点名称。"
            ], [
                "label" => "站点LOGO",
                "prop"  => "site_logo",
                "type"  => "upload:image",
                "value" => $values['site_logo'] ?? "",
                "intro" => "请上传站点LOGO。",
            ], [
                "label" => "站点协议",
                "prop"  => "site_protocol",
                "type"  => "upload:file",
                "value" => $values['site_protocol'] ?? "",
                "intro" => "请上传站点协议。",
            ], [
                "label" => "站点视频",
                "prop"  => "site_video",
                "type"  => "upload:media",
                "value" => $values['site_video'] ?? "",
                "intro" => "请上传站点视频。",
            ], [
                "label" => "备案号",
                "prop"  => "site_icp",
                "type"  => "input",
                "value" => $values['site_icp'] ?? "",
                "intro" => "请填写备案号。"
            ], [
                "label" => "禁止访问IP",
                "prop"  => "site_banip",
                "type"  => "textarea",
                "value" => $values['site_banip'] ?? "",
                "intro" => "禁止登录的IP，一行一条记录。"
            ], [
                "label" => "关闭网站",
                "prop"  => "site_close",
                "type"  => "switch",
                "value" => boolval($values['site_close'] ?? 0),
                "intro" => "禁止访客访问网站。"
            ]
        ];
    }

    #[Description('邮箱设置')]
    public function groupMail(array $values): array
    {
        return [
            [
                "label"   => "邮件发送方式",
                "prop"    => "email_type",
                "type"    => "select",
                "intro"   => "邮件发送方式。",
                "value"   => $values['email_type'] ?? "",
                "options" => [
                    ["value" => "", "label" => "请选择"],
                    ["label" => "SMTP", "value" => "smtp"]
                ]
            ], [
                "label" => "SMTP服务器",
                "prop"  => "smtp_server",
                "type"  => "input",
                "intro" => "SMTP服务器。",
                "value" => $values['smtp_server'] ?? ""
            ], [
                "label" => "SMTP端口",
                "prop"  => "smtp_port",
                "type"  => "input",
                "intro" => "SMTP端口。",
                "value" => $values['smtp_port'] ?? ""
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
                "label" => "SMTP发件人邮箱",
                "prop"  => "smtp_sender_email",
                "type"  => "input",
                "intro" => "SMTP发件人邮箱。",
                "value" => $values['smtp_sender_email'] ?? ""
            ], [
                "label"   => "SMTP加密方式",
                "prop"    => "smtp_encryption",
                "type"    => "select",
                "intro"   => "SMTP加密方式。",
                "value"   => $values['smtp_encryption'] ?? "",
                "options" => [
                    ["value" => "", "label" => "请选择"],
                    ["label" => "SSL", "value" => "ssl"],
                    ["label" => "TLS", "value" => "tls"]
                ]
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
                "intro"   => "选择附件上传平台，相关配置请到 config/storage.php 文件中配置。",
                "value"   => $values['storage_driver'] ?? "public",
                "options" => [
                    ["label" => "本地", "value" => "public"],
                    ["label" => "腾讯云", "value" => "qcloud"],
                    ["label" => "阿里云", "value" => "oss"],
                    ["label" => "七牛云", "value" => "qiniu"],
                ],
            ], [
                "label" => "附件域名",
                "prop"  => "storage_domain",
                "type"  => "input",
                "value" => $values['storage_domain'] ?? "",
                "intro" => "选择上传本地时，请留空。附件域名格式如 https://cdn.domain.com"
            ], [
                "label" => "允许上传的图片格式",
                "prop"  => "storage_image_ext",
                "type"  => "input",
                "intro" => "图片文件类型，如jpg,png,gif等，多个用逗号分隔",
                "value" => $values['storage_image_ext'] ?? "jpg,png,jpeg,gif"
            ], [
                "label" => "允许上传的媒体格式",
                "prop"  => "storage_media_ext",
                "type"  => "input",
                "value" => $values['storage_media_ext'] ?? "mp4",
                "intro" => "多媒体文件类型，如mp4,avi,mav等"
            ], [
                "label" => "允许上传的附件格式",
                "prop"  => "storage_file_ext",
                "type"  => "input",
                "value" => $values['storage_file_ext'] ?? "pdf",
                "intro" => "其他文件类型，如pdf,zip,rar等"
            ], [
                "label"  => "图片文件最大限制",
                "prop"   => "storage_image_size",
                "type"   => "input",
                "intro"  => "限制图片文件大小，单位为MB，留空不限制",
                "value"  => $values['storage_image_size'] ?? 5,
                "append" => "MB",
            ], [
                "label"  => "媒体文件最大限制",
                "prop"   => "storage_media_size",
                "type"   => "input",
                "intro"  => "同上",
                "value"  => $values['storage_media_size'] ?? 5,
                "append" => "MB",
            ], [
                "label"  => "其他附件最大限制",
                "prop"   => "storage_file_size",
                "type"   => "input",
                "intro"  => "同上",
                "value"  => $values['storage_file_size'] ?? 5,
                "append" => "MB",
            ], [
                "label"  => "上传图片最大尺寸限制",
                "prop"   => "storage_image_wh",
                "type"   => "input",
                "intro"  => "设置图片最大尺寸，大于限制时，系统会缩小后保存。填写格式为宽x高，如：1000x1000，留空为不限制。",
                "value"  => $values['storage_image_wh'] ?? '1000x1000',
                "append" => "PX",
            ]
        ];
    }

}