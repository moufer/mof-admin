<?php

namespace module\miniapp\logic;

use app\library\Searcher;
use module\miniapp\model\MiniApp;
use mof\Logic;

class MiniAppLogic extends Logic
{
    public function index(Searcher $searcher): \think\Paginator
    {
        return $searcher->model(MiniApp::class)->search();
    }
}