<?php

namespace module\miniapp\model;

use app\model\Module;
use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use EasyWeChat\MiniApp\Application as MiniAppApplication;
use EasyWeChat\Pay\Application as PayApplication;
use module\miniapp\model\searcher\MiniAppSearcher;

/**
 * 小程序应用
 * @package module\miniapp\model
 * @property Module $module 模块
 * @property string $title 小程序名称
 * @property string $api_root API接口
 * @property MiniAppApplication $sdk 小程序应用
 * @property PayApplication $easyWechatPay 小程序应用
 */
class MiniApp extends \mof\Model
{
    use MiniAppSearcher;

    protected $name = 'miniapp';

    protected $type = [
        'config'     => 'json',
        'avatar_img' => 'storage',
        'qrcode_img' => 'storage',
    ];

    public function moduleInfo(): \think\model\relation\HasOne
    {
        return $this->hasOne(Module::class, 'name', 'module');
    }

    /**
     * 获取EasyWechat小程序类实例
     * @param $value
     * @param $data
     * @return MiniAppApplication|mixed
     * @throws InvalidArgumentException
     */
    public function getSdkAttr($value, $data): ?MiniAppApplication
    {
        static $app = [];

        if (empty($app[$this->id])) {
            $config = [
                'app_id'  => $data['appid'],
                'secret'  => $data['app_secret'],
                'token'   => $data['app_token'] ?? '',
                'aes_key' => $data['app_aes_key'] ?? '',
            ];
            $app[$this->id] = new MiniAppApplication($config);
        }

        return $app[$this->id];
    }

    /**
     * 获取EasyWechat支付类实例
     * @param $value
     * @param $data
     * @return PayApplication|mixed
     * @throws InvalidArgumentException
     */
    public function getEasyWechatPayAttr($value, $data): ?PayApplication
    {
        static $app = [];

        if (empty($app[$this->id])) {
            $config = [
                'app_id'         => $data['appid'],
                'mch_id'         => $data['mch_id'],

                // 商户证书
                'private_key'    => __DIR__ . '/certs/apiclient_key.pem',
                'certificate'    => __DIR__ . '/certs/apiclient_cert.pem',

                // v3 API 秘钥
                'secret_key'     => $data['v3_secret_key'],

                // v2 API 秘钥
                'v2_secret_key'  => $data['v2_secret_key'],

                // 平台证书：微信支付 APIv3 平台证书，需要使用工具下载
                // 下载工具：https://github.com/wechatpay-apiv3/CertificateDownloader
                'platform_certs' => [
                    // 请使用绝对路径
                    // '/path/to/wechatpay/cert.pem',
                ],
            ];
            $app[$this->id] = new PayApplication($config);
        }

        return $app[$this->id];
    }

    /**
     * 模块API接口
     * @param $value
     * @param $data
     * @return string
     */
    public function getApiRootAttr($value, $data): string
    {
        return url("api/{$this->module}")
            ->https()
            ->domain(true)
            ->suffix(false)
            ->build();
    }
}