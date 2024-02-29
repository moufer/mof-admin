<?php

namespace app\model;

use mof\Mof;

class Config extends \mof\Model
{
    protected $type = [
        'extra' => 'json'
    ];

    protected function setValueAttr($value, $data): float|bool|int|string
    {
        //获取参数类型
        $type = $data['type'] ?? 'text';
        //根据参数类型进行转换
        return match ($type) {
            'number' => (int)$value,
            'float' => (float)$value,
            'switch' => $value ? 1 : 0,
            'input-dict',
            'select-multiple',
            'checkbox',
            'keyvalue',
            'upload:image',
            'upload:file',
            'upload:media' => $value ? json_encode($value, JSON_UNESCAPED_UNICODE) : '',
            default => $value,
        };
    }

    protected function getValueAttr($value, $data)
    {
        //获取参数类型
        $type = $data['type'] ?? 'text';
        //根据参数类型进行转换
        $value = match ($type) {
            'input-dict' => empty($value) ? (object)[] : json_decode($value, true),
            'select-multiple' => empty($value) ? [] : json_decode($value, true),
            'checkbox',
            'keyvalue',
            'upload:image',
            'upload:file',
            'upload:media' => $value && $value !== '[]' ? json_decode($value, true) : '',
            default => $value,
        };
        //上传类型处理
        if ($value && is_array($value) && str_starts_with($type, 'upload')) {
            $value = array_map(fn($item) => empty($item['path']) ? false : [
                'name' => $item['name'] ?? basename($item['path']),
                'url'  => Mof::storageUrl($item['path']),
                'path' => $item['path']
            ], $value);
            //过滤
            $value = array_filter($value);
        }
        return $value;
    }
}