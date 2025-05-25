<?php

// 应用公共文件
use mof\Mof;

/**
 * 获取上传地址
 * @param string $method 上传方法
 * @return string 上传地址
 */
function upload_url(string $method = 'image', $fullUrl = true): string
{
    $uploadUrl = config('system.upload_url', '/system/upload') . '/' . $method;
    return $fullUrl ? url($uploadUrl, [], false, true) : $uploadUrl;
}

/**
 * 获取存储选择器地址
 * @return string
 */
function storage_selector_url(): string
{
    return config('system.storage_selector_url', '/system/storage/selector');
}

/**
 * 附件链接
 * @param string $path
 * @return string
 */
function storage_url(string $path): string
{
    return Mof::storageUrl($path);
}

/**
 * 获取上传验证规则
 * @param string $type 上传文件类型，image|file|media
 * @param array $rules 额外的验证规则
 * @param array $messages 验证消息
 * @return array [$rules,$messages]
 */
function upload_validate_rules(string $type, array $rules = [], array $messages = []): array
{
    $_rules = ['require', 'file'];
    if (in_array($type, ['image', 'media', 'file'])) {
        $ext = trim(config('system.storage_' . $type . '_ext', ''), ',');
        $exts = $ext ? explode(',', $ext) : [];
        foreach ($exts as $k => $v) {
            if (in_array(strtolower($v), [
                'asp',
                'php',
                'jsp',
                'jspx',
                'jspx',
                'php3',
                'php4',
                'php5',
                'php7',
                'phtml',
                'cgi',
                'py',
                'js',
                'html',
                'htm',
                'shtml',
                'vbs'
            ])) {
                unset($exts[$k]);
            }
        }
        $ext = implode(',', $exts);
        $size = (int)config('system.storage_' . $type . '_size', 0);
        $ext && $_rules[] = 'fileExt:' . $ext;                  //文件后缀验证
        $size && $_rules[] = 'fileSize:' . $size * 1024 * 1024; //文件data大小验证
    }
    //验证规则
    $rules = array_merge($_rules, $rules);
    //验证消息
    $messages = array_merge([
        'file.require'  => '请上传文件',
        'file.file'     => '请上传文件',
        'file.fileExt'  => '不支持上传的文件类型',
        'file.fileSize' => '文件大小超过了最大限制',
    ], $messages);

    return [$rules, $messages];
}