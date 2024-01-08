<?php
declare (strict_types=1);

namespace mof;

use think\facade\Event;
use think\Service;

/**
 * 应用服务类
 */
class AppService extends Service
{
    public function register(): void
    {
        //注册命令
        if ($this->app->runningInConsole()) {
            //注册框架命令
            $this->commands(Mof::getCommands());
        }
        //注册模块服务
        foreach (Module::getServices() as $service) {
            $this->app->register($service);
        }
        //加载路由规则
        Event::listen('RouteLoaded', function () {
            Module::loadRoutes(); //加载模块路由
        });
    }

    public function boot()
    {
        // 服务启动
    }
}
