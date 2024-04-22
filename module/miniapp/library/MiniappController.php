<?php

namespace module\miniapp\library;

use app\library\Controller;
use module\miniapp\model\MiniApp;
use mof\annotation\Inject;

class MiniappController extends Controller
{
    #[Inject]
    protected MiniApp $miniapp; //获取在中间件中已挂载的小程序模型

}