<?php

namespace module\miniapp\library;

use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use EasyWeChat\Pay\Application;
use LogicException;
use module\miniapp\model\MiniApp;

class WechatPayApplication
{
    protected static array $apps = [];

    protected array       $config;
    protected Application $wechatApp;

    public static function make(MiniApp $miniapp)
    {
        $config = [
            'app_id'        => $miniapp->appid,
            'mch_id'        => $miniapp->pay['mch_id'],
            'private_key'   => $miniapp->pay['private_key'],
            'certificate'   => $miniapp->pay['certificate'],
            'secret_key'    => $miniapp->pay['secret_key'],
            //'v2_secret_key' => $miniapp->pay['v2_secret_key'],
        ];

        if (!isset(self::$apps[$config['mch_id']])) {
            self::$apps[$config['mch_id']] = new static($config);
        }

        return self::$apps[$config['mch_id']];
    }

    public function __construct($config)
    {
        try {
            $this->config = $config;
            $this->wechatApp = new Application($this->config);
        } catch (InvalidArgumentException $e) {
            throw new LogicException($e->getMessage());
        }
    }

    public function handler(): Application
    {
        return $this->wechatApp;
    }

    public function mchId()
    {
        return $this->config['mch_id'];
    }

    public function appId()
    {
        return $this->config['app_id'];
    }
}