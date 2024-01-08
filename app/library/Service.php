<?php

namespace app\library;

class Service extends \think\Service
{
    public function register(): void
    {
        $this->app->event->listenEvents([
            'HttpRun'    => [
                \app\event\GetConfig::class
            ],
            'AdminLogin' => [
                \app\event\LoginLog::class
            ]
        ]);
    }
}