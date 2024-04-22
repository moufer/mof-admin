<?php

namespace module\miniapp\library\wechat;

use EasyWeChat\Kernel\HttpClient\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;

class Mobile extends MiniAppBase
{
    public function getNumber($code): ResponseInterface|Response
    {
        $params = ['code' => $code];
        return $this->app->getClient()->postJson('/wxa/business/getuserphonenumber', $params);
    }
}