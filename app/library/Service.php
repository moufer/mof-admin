<?php

namespace app\library;

use app\command\Install;
use app\command\Perm;
use mof\ModuleService;

class Service extends ModuleService
{
    protected array $events = [
    ];

    protected array $commands = [
        Install::class,
        Perm::class
    ];
}