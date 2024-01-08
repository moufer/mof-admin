<?php

// +----------------------------------------------------------------------
// | 缓存设置
// +----------------------------------------------------------------------

return [
    'key_id'       => 'mfsoft', //jwt加密key_id
    'key'          => '123456', //jwt加密key
    'token_expire' => 86400, //token过期时间
    'token_type'   => 'Bearer', //token类型
    'token_prefix' => 'Bearer ', //token前缀
    'token_header' => 'Authorization', //token请求头
];