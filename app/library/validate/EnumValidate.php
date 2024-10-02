<?php
/**
 * Project: AIGC
 * Author: moufer <moufer@163.com>
 * Date: 2024/9/23 23:42
 */

namespace app\library\validate;

use mof\Validate;

class EnumValidate
{
    public static function register(): void
    {
        Validate::maker(function ($validate) {
            $validate->extend('enum', function ($value, $rule) {
                return static::check($value, $rule);
            }, ':attribute 不是有效值');
        });
    }

    /**
     * @param $value
     * @param string $rule Enum类名
     * @return bool|string
     */
    protected static function check($value, string $rule): bool|string
    {
        $enum = call_user_func("{$rule}::tryFrom", $value);
        return !empty($enum);
    }
}