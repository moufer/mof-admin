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
        $data = [
            'upload_url'           => upload_url('{type}', false),
            'storage_url'          => rtrim(storage_url('/'), '/'),
            'storage_selector_url' => storage_selector_url(),
        ];
        return ApiResponse::success($data);
    }

}