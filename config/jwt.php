<?php

// +----------------------------------------------------------------------
// | 缓存设置
// +----------------------------------------------------------------------

return [
    'key_id' => 'mfsoft', //jwt加密key_id
    'key'    => '123456', //jwt加密key
    'expire' => 86400, //token过期时间
    'type'   => 'Bearer', //token类型
    'prefix' => 'Bearer ', //token前缀
    'header' => 'Authorization', //token请求头
];