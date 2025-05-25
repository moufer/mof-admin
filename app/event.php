<?php
// 事件定义文件
return [
    'bind'   => [],
    'listen' => [
        'AppInit'  => [],
        'HttpRun'      => [
            \app\event\GetConfig::class,
        ],
        'HttpEnd'      => [],
        'LogLevel'     => [],
        'LogWrite'     => [],
        // 后台登录事件
        'AdminLogin' => [
            \app\event\LoginLog::class
        ],
        // 统计事件
        'system.total' => [
            \app\event\Total::class
        ]
    ],

    'subscribe' => [],
];
