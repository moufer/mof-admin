<?php
/**
 * Project: MofAdmin
 * Author: moufer <moufer@163.com>
 * Date: 2024/7/16 13:32
 */

namespace app\command;

use app\library\InstallPerm;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;


class Perm extends Command
{
    protected function configure(): void
    {
        $this->setName('mof:perm')
            ->setDescription('系统菜单更新')
            ->addArgument('module', Argument::REQUIRED, '菜单更新模块');
    }

    protected function execute(Input $input, Output $output): void
    {
        $module = $input->getArgument('module');
        if (!$module) {
            $output->writeln('module is required');
            return;
        }
        try {
            InstallPerm::make($module)->reinstall();
            $output->writeln('success');
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }
    }
}