<?php
/**
 * Project: MofAdmin
 * Author: moufer <moufer@163.com>
 * Date: 2024/6/22 15:35
 */

namespace mof\command;

use mof\exception\LogicException;
use mof\Module;
use think\console\Command;
use think\console\input\Argument as InputArgument;
use think\console\Input;
use think\console\Output;
use think\helper\Str;

class ControllerCreate extends Command
{
    protected function configure(): void
    {
        $this->setName('mof-controller:create')
            ->setDescription('创建一个控制器类')
            ->addArgument('module', InputArgument::REQUIRED, ' 模块名')
            ->addArgument('name', InputArgument::REQUIRED, ' 控制器类名')
            ->addOption('--force', '-f', InputArgument::OPTIONAL, '强制覆盖已存在的控制器类');
    }

    protected function execute(Input $input, Output $output): void
    {
        $module = $input->getArgument('module');
        $name = Str::studly($input->getArgument('name'));
        $force = $input->getOption('force');
        $file = $this->create($module, $name, $force);
        $output->writeln("<info>Controller created successfully.</info>");
        $output->writeln($file);
    }

    private function create($module, $name, $force): string
    {
        $modulePath = Module::path($module);
        $controllerPath = $modulePath . 'controller';

        $controllerFilePath = $controllerPath . DIRECTORY_SEPARATOR . $name . '.php';
        if (is_file($controllerFilePath) && !$force) {
            throw new LogicException(sprintf("%s 控制器类已存在", $controllerFilePath));
        }

        if (!is_dir($modulePath)) {
            throw new LogicException(sprintf("%s 模块不存在", $modulePath));
        }
        if (!is_dir($controllerPath)) {
            if (!mkdir($controllerPath, 0755, true)) {
                throw new LogicException(sprintf("创建目录失败：%s", $controllerPath));
            }
        }

        $template = file_get_contents($this->getTemplate());
        $match = [
            '{%date%}'   => date('Y-m-d'),
            '{%module%}' => $module,
            '{%name%}'   => $name,
        ];
        $content = str_replace(array_keys($match), array_values($match), $template);
        if (!file_put_contents($controllerFilePath, $content)) {
            throw new LogicException(sprintf("创建控制器类失败：%s", $controllerFilePath));
        }

        return $controllerFilePath;
    }

    private function getTemplate(): string
    {
        return __DIR__ . '/stubs/controller.stub';
    }
}