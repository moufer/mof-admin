<?php

namespace mof;

class ModuleService extends \think\Service
{
    /**
     * 公共事件监听
     * @var array
     */
    protected array $events = [];
    /**
     * 公共中间件
     * @var array
     */
    protected array $middleware = [];
    /**
     * 命令行
     * @var array
     */
    protected array $commands = [];

    public function register(): void
    {
        //注册事件监听
        if ($this->events) {
            $this->app->event->listenEvents(
                $this->events
            );
        }
        //注册中间件
        if ($this->middleware) {
            $this->app->middleware->import($this->middleware);
        }
        //注册命令
        if ($this->commands) {
            $this->commands($this->commands);
        }
    }
}