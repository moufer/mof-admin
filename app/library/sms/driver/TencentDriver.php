<?php
/**
 * Project: AIGC
 * Author: moufer <moufer@163.com>
 * Date: 2024/9/22 13:57
 */

namespace app\library\sms\driver;

use app\library\sms\DriverAbstract;
use mof\exception\LogicException;
use TencentCloud\Common\Credential;
use TencentCloud\Sms\V20210111\Models\SendSmsRequest;
use TencentCloud\Sms\V20210111\SmsClient;
use think\facade\Log;

class TencentDriver extends DriverAbstract
{
    protected SmsClient $client;

    /**
     * @inheritDoc
     */
    public function setConfig(array $config): void
    {
        parent::setConfig($config);

        if (empty($config['secret_id']) || empty($config['secret_key'])) {
            throw new LogicException('短信配置参数错误');
        }
        try {
            $cred = new Credential($config['secret_id'], $config['secret_key']);
            $this->client = new SmsClient($cred, $config['region'] ?: "ap-nanjing");
        } catch (\Exception $e) {
            Log::error("腾讯云：" . $e->getMessage());
            throw new LogicException('短信配置失败');
        }
    }

    /**
     * @inheritDoc
     */
    public function send(string $mobile, string $template, array $params): bool
    {
        //找$template对应的模板ID
        $templateId = $this->getTemplateId($template);

        try {
            $req = new SendSmsRequest();
            $req->SmsSdkAppId = $this->config['sdk_app_id'];
            $req->SignName = $this->options['sign_name'] ?? '';
            $req->TemplateId = $templateId;
            if ($params) {
                //检测key是下标序号还是字符串（腾讯云只支持序号，不支持字符串变量）
                $keys = array_keys($params);
                if (!is_numeric($keys[0])) $params = array_keys($params);
                $req->TemplateParamSet = $params;
            }
            // 下发手机号码，采用 E.164 标准，+[国家或地区码][手机号]，单次请求最多支持200个手机号
            $req->PhoneNumberSet = [$this->getE164MobileNo($mobile)];

            $resp = $this->client->SendSms($req);
            // 输出json格式的字符串回包
            $result = json_decode($resp->toJsonString(), true);
            if ($result['SendStatusSet'][0]['Code'] !== 'Ok') {
                $this->sendErrorRecord($result['SendStatusSet'][0]);
            }
            return true;//$result['SendStatusSet'][0];
        } catch (\Exception $e) {
            Log::error(
                sprintf("\n%s(%s)\nmobile:%s\ntemplateId:%s\n",
                    $e->getMessage(), $e->getCode(), $mobile, $templateId)
            );
            throw new LogicException('短信发送失败');
        }

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
        throw match ($status['Code']) {
            'FailedOperation.InsufficientBalanceInSmsPackage' => new LogicException('腾讯云：账号短信额度不足'),
            'UnsupportedOperation.ChineseMainlandTemplateToGlobalPhone	' => new LogicException('腾讯云：国内短信模板不支持发送国际/港澳台手机号'),
            'UnsupportedOperation.UnsupportedRegion' => new LogicException('腾讯云：不支持该地区手机号'),
            default => new LogicException('腾讯云：' . $status['Code']),
        };
    }
}