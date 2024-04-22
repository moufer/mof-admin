<?php

namespace module\miniapp\library\wechat;

use EasyWeChat\Kernel\HttpClient\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;


class Security extends MiniAppBase
{
    public function msgSecCheck($params): ResponseInterface|Response
    {
        $params['version'] = 2;
        return $this->app->getClient()->postJson('/wxa/msg_sec_check', $params);
    }
}