<?php

namespace mof\utils;

use think\helper\Str;

class FormComponentOptions
{
    protected string $type = '';

    /**
     * 填充合并表单组件配置
     * @param $options
     * @return array|null
     */
    public static function fill($options): ?array
    {
        static $instance = null;

        empty($instance) && $instance = new self();

        $type = $options['type'] ?: 'input';
        //找$type是否存在冒号
        if (Str::contains($type, ':')) {
            list($type,) = explode(':', $type, 2);
        }

        $result = [];
        //检测类里面有没有对应的函数名
        $fun = 'opt' . Str::studly($type);
        if (method_exists($instance, $fun)) {
            $result = call_user_func([$instance, $fun], $options['type']);
            return array_merge($result, $options);
        }
        return $options;
    }

    /**
     * 上传组件配置信息
     * @param $type
     * @return array
     */
//    protected function optUpload($type): array
//    {
//        $accept = [
//            'image' => 'image/*',
//            'video' => "video/*",
//            'audio' => "audio/*",
//            'file'  => '*/*',
//        ];
//        $type = substr($type, 7); //upload:image，upload:file，upload:media
//        return [
//            "accept"       => $accept[$type],
//            "action"       => upload_url($type),
//            "limit"        => 1, //默认单文件
//            "headers"      => ['Authorization' => app('request')->header('Authorization')],
//            "limitExt"     => config("admin.storage_{$type}_ext", ''),
//            "limitSize"    => (int)config("admin.storage_{$type}_size", 0) * 1024 * 1024, //MB->字节
//            "listType"     => $type === 'image' ? "picture-card" : 'text',
//            "showFileList" => true
//        ];
//    }

    protected function optUploadRaw($type): array
    {
        $accept = [
            'image' => 'image/*',
            'media' => "video/*,audio/*",
            'file'  => '*/*',
        ];
        $type = substr($type, 7); //upload:image，upload:file，upload:media
        return [
            "accept"       => $accept[$type],
            "action"       => upload_url($type),
            "limit"        => 1, //默认单文件
            "headers"      => ['Authorization' => app('request')->header('Authorization')],
            "limitExt"     => config("admin.storage_{$type}_ext", ''),
            "limitSize"    => (int)config("admin.storage_{$type}_size", 0) * 1024 * 1024, //MB->字节
            "listType"     => $type === 'image' ? "picture-card" : 'text',
            "showFileList" => true
        ];
    }

    protected function optWangEditor($type = ''): array
    {
        return [
            'toolbarConfig' => [],
            'editorConfig'  => [],
        ];
    }

}