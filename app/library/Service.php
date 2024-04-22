<?php

namespace app\library;

use mof\ModuleService;

class Service extends ModuleService
{
    protected array $events = [
        'HttpRun'    => [
            \app\event\GetConfig::class
        ],
        'AdminLogin' => [
            \app\event\LoginLog::class
        ]
    ];

    protected array $commands = [
        \app\command\Install::class
    ];
}