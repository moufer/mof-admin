<?php
/**
 * Project: MofAdmin
 * Author: moufer <moufer@163.com>
 * Date: 2024/9/22 13:57
 */

namespace app\library\sms\driver;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use app\library\sms\DriverAbstract;
use mof\exception\LogicException;
use think\facade\Log;

class AliyunDriver extends DriverAbstract
{
    protected array $config;

    public function setConfig(array $config): void
    {
        parent::setConfig($config);

        if (empty($config['access_key_id']) || empty($config['access_key_secret'])) {
            throw new LogicException('短信配置参数错误');
        } else if (empty($config['sign_name'])) {
            throw new LogicException('短信签名未配置');
        }
        try {
            AlibabaCloud::accessKeyClient($config['access_key_id'], $config['access_key_secret'])
                ->regionId($config['region_id'] ?? 'cn-hangzhou')
                ->asDefaultClient();
        } catch (ClientException $e) {
            Log::error($e->getMessage());
            throw new LogicException('短信配置失败');
        }
    }

    /**
     * @inheritDoc
     */
    public function send(string $mobile, string $template, array $params): bool
    {
        $templateId = $this->getTemplateId($template);
        try {
            $result = AlibabaCloud::rpc()
                ->product('Dysmsapi')
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->options([
                    'query' => [
                        'PhoneNumbers'  => $mobile, // 接收短信的手机号码
                        'SignName'      => $this->config['sign_name'], // 短信签名
                        'TemplateCode'  => $templateId, // 短信模板ID
                        'TemplateParam' => json_encode($params) // 模板参数
                    ],
                ])
                ->request();
            if ($result->toArray()['Code'] !== 'OK') {
                $this->sendErrorRecord($result->toArray());
            }
            return true;
        } catch (\Exception $e) {
            Log::error(
                sprintf("\n%s(%s)\nmobile:%s\ntemplateId:%s\n",
                    $e->getMessage(), $e->getCode(), $mobile, $templateId)
            );
            throw new LogicException('短信发送失败');
        }
    }

    private function sendErrorRecord($status): void
    {
        throw new LogicException('阿里云短信：' . $status['Code']);
    }
}