<?php

namespace module\miniapp\library\wechat;

use EasyWeChat\MiniApp\Application;

class MiniAppBase
{

    public function __construct(protected Application $app)
    {
    }

}