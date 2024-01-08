<?php

namespace mof;

use think\facade\Console;

class InstallModule
{
    protected string $module;

    public function __construct($module)
    {
        $this->module = $module;
    }

    public function install(): void
    {
        Console::call('mof-migrate:run', [$this->module]); // 执行模块的数据库迁移
        Console::call('mof-seed:run', [$this->module]); // 执行模块的数据填充
    }

    public function uninstall(): void
    {
        Console::call('mof-migrate:rollback', [$this->module]); // 执行模块的数据库回滚
    }
}