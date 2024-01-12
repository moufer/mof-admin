<?php

use app\library\ExceptionHandle;
use app\library\Request;
use app\library\Route;
use mof\filesystem\Filesystem;

// 容器Provider定义文件
return [
    'route'                  => Route::class,
    'think\Request'          => Request::class,
    'think\exception\Handle' => ExceptionHandle::class,
    'filesystem'             => Filesystem::class,
];
