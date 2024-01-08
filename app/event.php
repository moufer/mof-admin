<?php
// 事件定义文件
return [
    'bind' => [
    ],

    'listen' => [
        'AppInit'  => [],
        'HttpRun'  => [
            \app\event\GetConfig::class
        ],
        'HttpEnd'  => [],
        'LogLevel' => [],
        'LogWrite' => [],

        'AdminLogin' => [
            \app\event\LoginLog::class
        ],
    ],

    'subscribe' => [
    ],
];
