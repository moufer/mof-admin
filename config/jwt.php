<?php

// +----------------------------------------------------------------------
// | 缓存设置
// +----------------------------------------------------------------------

return [
    'key_id'  => env('jwt.key_id', 'mfsoft'), //jwt加密key_id
    'key'     => env('jwt.key', '123456'), //jwt加密key
    'expires' => env('jwt.expires', 86400), //token过期时间
    'type'    => 'Bearer', //token类型
    'prefix'  => 'Bearer ', //token前缀
    'header'  => 'Authorization', //token请求头
];