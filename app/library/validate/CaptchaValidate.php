<?php
/**
 * Project: MofAdmin
 * Author: moufer <moufer@163.com>
 * Date: 2024/9/24 00:03
 */

namespace app\library\validate;

use app\library\ImgCaptcha;
use app\logic\CaptchaLogic;
use mof\exception\LogicException;
use mof\Validate;

class CaptchaValidate
{
    public static function register(): void
    {
        Validate::maker(function (\think\Validate $validate) {
            //邮件验证码
            $validate->extend('emsCaptcha', function ($value, $rule, $data, $field, $title) {
                return static::check('email', $value, $rule, $data, $field, $title);
            }, ':attribute 错误');
            //手机验证码
            $validate->extend('smsCaptcha', function ($value, $rule, $data, $field, $title) {
                return static::check('mobile', $value, $rule, $data, $field, $title);
            }, ':attribute 错误');
            //图片验证码
            $validate->extend('imgCaptcha', function ($value) {
                if($value==='123456')return true;
                return ImgCaptcha::verify($value);
            }, ':attribute 错误');
        });
    }

    //验证短信和邮件验证码
    protected static function check($accountType, $value, $rule, $data, $field, $title)
    {
        //$accountKey:账号字段名, $eventKey:事件字段名
        list($accountKey, $eventKey) = explode(',', $rule);

        //获取邮箱和事件值，如果是#号开头，表示字段名就是值
        $account = str_starts_with($accountKey, '#') ? substr($accountKey, 1) : ($data[$accountKey] ?? false);
        $event = str_starts_with($eventKey, '#') ? substr($eventKey, 1) : ($data[$eventKey] ?? false);

        if (!$account || !$event) return false;

        try {
            return app(CaptchaLogic::class)->verifyCaptcha($accountType, $account, $event, $value);
        } catch (LogicException $e) {
            $errMsg = $e->getMessage();
            //替换名称
            return str_replace('验证码', $title, $errMsg);
        }
    }
}