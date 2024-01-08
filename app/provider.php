<?php

use app\library\ExceptionHandle;
use app\library\Route;
use mof\filesystem\Filesystem;
use mof\Request;
use mof\Token;

// 容器Provider定义文件
return [
    'route'                  => Route::class,
    'think\Request'          => Request::class,
    'think\exception\Handle' => ExceptionHandle::class,
    'filesystem'             => Filesystem::class,
    'token'                  => Token::class,
];
