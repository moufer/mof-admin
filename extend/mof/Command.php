<?php

/**
 * Author: moufer <moufer@163.com>
 * Date: 2024/12/4 00:35
 */

namespace mof;

use think\console\Input;
use think\console\Output;

class Command extends \think\console\Command
{
    protected function initialize(Input $input, Output $output): void
    {
        $this->app->event->trigger('ConsoleInit');
    }
}
