<?php

namespace module\miniapp\library;

use module\miniapp\event\GetPerms;

class Service extends \think\Service
{
    public function register(): void
    {
        $this->app->event->listenEvents([
            'GetPerms'    => [
                GetPerms::class
            ],
        ]);
    }
}