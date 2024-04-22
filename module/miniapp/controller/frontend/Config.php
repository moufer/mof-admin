<?php

namespace module\miniapp\controller\frontend;

use module\miniapp\library\MiniappFrontendController;
use module\miniapp\logic\ConfigLogic;
use mof\annotation\Inject;
use mof\ApiResponse;
use think\response\Json;

class Config extends MiniappFrontendController
{
    #[Inject]
    protected ConfigLogic $logic;

    public function index(): Json
    {
        return ApiResponse::success($this->logic->getMiniappConfig());
    }
}