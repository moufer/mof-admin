<?php

namespace mof\utils;

class Random
{

    /**
     * 生成一个UUID
     * @return string
     */
    public static function uuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    /**
     * 生成一个纯字母的随机字符串
     * @param int $length 字符串长度
     * @return string
     */
    public static function alpha(int $length = 6): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    /**
     * 生成纯数字的字符串
     * @param int $length 字符串长度
     * @param bool $firstZero 开头是否允许为0
     * @return string
     */
    public static function num(int $length = 6, bool $firstZero = false): string
    {
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $firstZero && $i === 0 ? rand(1, 9) : rand(0, 9);
        }
        return $randomString;
    }

    /**
     * 生成数字+字符的字符串
     * @param int $length 字符串长度
     * @return string
     */
    public static function alphaNum(int $length = 6): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
}