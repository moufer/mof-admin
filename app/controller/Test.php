<?php

namespace app\controller;

use app\library\AdminController;
use mof\ApiResponse;
use think\response\Json;

class Test extends AdminController
{
    public function permUpdate(): Json
    {
        \app\model\Perm::where('pid', '>', 0)->chunk(100, function ($list) {
            foreach ($list as $perm) {
                $perm->save(['pid' => $perm->pid]);
            }
        });
        return ApiResponse::success();
    }

    public function event(): Json
    {

        event('adminLogin', ['username' => '111', 'status' => 1]);

        return ApiResponse::success(
            $this->app->event->hasListener('adminLogin')
        );
    }
}