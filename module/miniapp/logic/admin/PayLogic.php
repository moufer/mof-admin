<?php

namespace module\miniapp\logic\admin;

use module\miniapp\model\MiniApp;
use mof\annotation\Inject;
use mof\exception\LogicException;
use mof\Logic;

class PayLogic extends Logic
{
    #[Inject]
    protected MiniApp $miniapp;

    /**
     * 小程序打包配置表单
     * @return array[]
     * @throws LogicException
     */
    public function form(): array
    {
        $values = $this->miniapp->pay ?? [];
        return $this->getFormConfig($values);
    }

    /**
     * 提交打包
     * @throws \Exception
     */
    public function submit(array $postData): array
    {
        $this->miniapp->save([
            'pay' => $postData
        ]);
        return $this->miniapp->pay;
    }


    /**
     * 表单选项
     * @param array $values
     * @return array[]
     * @throws LogicException
     */
    private function getFormConfig(array $values): array
    {
        return [
            [
                "label" => "商户ID",
                "prop"  => "mch_id",
                "type"  => "input",
                "value" => $values['mch_id'] ?? '',
                "intro" => "填写商户ID"
            ],
            [
                "label" => "API秘钥v3",
                "prop"  => "secret_key",
                "type"  => "input",
                "value" => $values['secret_key'] ?? '',
                "intro" => "填写API秘钥v3"
            ],
            [
                "label" => "商户证书密钥",
                "prop"  => "private_key",
                "type"  => "textarea",
                "value" => $values['private_key'] ?? '',
                "intro" => "填写API商户证书密钥(从apiclient_key.pem中复制)"
            ],
            [
                "label" => "商户证书",
                "prop"  => "certificate",
                "type"  => "textarea",
                "value" => $values['certificate'] ?? '',
                "intro" => "填写API商户证书内容(从apiclient_cert.pem中复制)"
            ]
        ];
    }
}