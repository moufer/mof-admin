<?php
/**
 * Project: MofAdmin
 * Author: moufer <moufer@163.com>
 * Date: 2024/5/8 21:31
 */

namespace app\controller;

use mof\ApiController;
use mof\ApiResponse;
use think\response\Json;

class Client extends ApiController
{
    public function config(): Json
    {
        $config = config('system');
        $data = [
            'storageUrl' => empty($config['storage_domain']) ? rtrim(storage_url('/'), '/') : '',
        ];
        return ApiResponse::success($data);
    }

}