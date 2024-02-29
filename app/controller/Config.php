<?php

namespace app\controller;

use app\library\Controller;
use mof\ApiResponse;
use think\db\exception\DbException;
use think\facade\Cache;
use think\response\Json;

class Config extends Controller
{
    use \app\concern\Config;
}