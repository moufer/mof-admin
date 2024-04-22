<?php

namespace app\model;

use mof\Mof;

class Config extends \mof\Model
{
    protected $name = 'system_config';

    protected $type = [
        'extra' => 'json'
    ];

    protected function setValueAttr($value, $data): float|bool|int|string
    {
        //获取参数类型
        $type = $data['type'] ?? 'text';
        //根据参数类型进行转换
        return match ($type) {
            'input-number',
            'number' => trim($value),
            'switch' => $value ? 1 : 0,
            'input-dict',
            'select-multiple',
            'checkbox',
            'keyvalue' => $value ? json_encode($value, JSON_UNESCAPED_UNICODE) : '',
            default => $value,
        };
    }

    protected function getValueAttr($value, $data)
    {
        //获取参数类型
        $type = $data['type'] ?? 'text';
        //根据参数类型进行转换
        return match ($type) {
            'input-number',
            'number' => floatval($value),
            'switch' => (bool)$value,
            'input-dict',
            'select-multiple' => empty($value) ? [] : json_decode($value, true),
            'checkbox',
            'keyvalue' => $value ? json_decode($value, true) : [],
            default => $value,
        };
    }

}