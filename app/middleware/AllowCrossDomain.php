<?php
/**
 * Project: MofAdmin
 * Author: moufer <moufer@163.com>
 * Date: 2024/9/24 22:56
 */

namespace app\middleware;

class AllowCrossDomain extends \think\middleware\AllowCrossDomain
{
    protected $header = [
        'Access-Control-Allow-Credentials' => 'true',
        'Access-Control-Max-Age'           => 1800,
        'Access-Control-Allow-Methods'     => 'GET, POST, PATCH, PUT, DELETE, OPTIONS',
        'Access-Control-Allow-Headers'     => 'Authorization, Content-Type, ' .
            'If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, ' .
            'X-CSRF-TOKEN, X-Requested-With, X-Session-Id',
        'Access-Control-Expose-Headers'    => 'X-Session-Id'
    ];

}