<?php

namespace module\miniapp\library\wechat;

use EasyWeChat\Kernel\HttpClient\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;

class Subscribe extends MiniAppBase
{
    public function addTemplate(string $tid, array $kidList, string $sceneDesc = ''): ResponseInterface|Response
    {
        $params = [
            'tid'       => $tid,
            'kidList'   => $kidList,
            'sceneDesc' => $sceneDesc,
        ];
        return $this->app->getClient()->postJson('/wxaapi/newtmpl/addtemplate', $params);
    }

    public function sendMessage($params)
    {
        return $this->app->getClient()->postJson('/cgi-bin/message/subscribe/send', $params);
    }
}