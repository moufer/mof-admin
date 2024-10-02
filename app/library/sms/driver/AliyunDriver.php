<?php
/**
 * Project: AIGC
 * Author: moufer <moufer@163.com>
 * Date: 2024/9/22 13:57
 */

namespace app\library\sms\driver;

use app\library\sms\DriverInterface;
use mof\exception\LogicException;
use TencentCloud\Common\Credential;
use TencentCloud\Sms\V20210111\SmsClient;
use think\facade\Log;

class AliyunDriver implements DriverInterface
{
    protected array     $config;



    public function setConfig(array $config): void
    {

    }

    /**
     * @inheritDoc
     */
    public function send(string $mobile, string $template, array $params): bool
    {
        list($module,$action) = explode('.', $template);
    }

    private function getE164MobileNo($mobile): string
    {
        //正则判断是否国内手机号
        if (preg_match("/^1[3456789]\d{9}$/", $mobile)) {
            return "+86" . $mobile;
        }
        return $mobile;
    }

    private function sendErrorRecord($status): void
    {
        switch ($status['Code']) {
            case 'FailedOperation.InsufficientBalanceInSmsPackage':
                Log::error('腾讯云：账号短信额度不足');
                break;
            case 'UnsupportedOperation.ChineseMainlandTemplateToGlobalPhone	':
                Log::error('腾讯云：国内短信模板不支持发送国际/港澳台手机号');
                break;
            case 'UnsupportedOperation.UnsupportedRegion':
                Log::error('腾讯云：不支持该地区手机号');
                break;
            default:
                Log::error('腾讯云：' . $status['Code']);
        }
    }
}