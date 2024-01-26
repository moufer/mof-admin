<?php

namespace module\miniapp\library;

use EasyWeChat\MiniApp\Application;
use InvalidArgumentException;
use module\miniapp\model\MiniApp;
use mof\exception\LogicException;

class WechatMiniappApplication
{
    protected static array $apps = [];

    protected array       $config;
    protected Application $wechatApp;

    public static function make(MiniApp $miniapp)
    {
        $config = [
            'app_id'  => $miniapp->appid,
            'secret'  => $miniapp->app_secret,
            'token'   => $miniapp->app_token,
            'aes_key' => $miniapp->app_aes_key,
        ];

        if (!isset(self::$apps[$config['app_id']])) {
            self::$apps[$config['app_id']] = new static($config);
        }

        return self::$apps[$config['app_id']];
    }

    public function __construct($config)
    {
        try {
            $this->config = $config;
            $this->wechatApp = new Application($this->config);
        } catch (\EasyWeChat\Kernel\Exceptions\InvalidArgumentException $e) {
            throw new LogicException($e->getMessage());
        }
    }

    public function handler(): Application
    {
        return $this->wechatApp;
    }
}