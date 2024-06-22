<?php
/**
 * Project: MofAdmin
 * Author: moufer <moufer@163.com>
 * Date: 2024/6/22 16:20
 */

namespace mof\command;

use mof\exception\LogicException;
use mof\Module;
use think\console\Command;
use think\console\input\Argument as InputArgument;
use think\console\Input;
use think\console\Output;
use think\helper\Str;

class ModelCreate extends Command
{
    protected function configure(): void
    {
        $this->setName('mof-model:create')
            ->setDescription('创建一个模型类')
            ->addArgument('module', InputArgument::REQUIRED, ' 模块名')
            ->addArgument('name', InputArgument::REQUIRED, ' 模型类名')
            ->addOption('--force', '-f', InputArgument::OPTIONAL, '强制覆盖已存在的模型类');
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
        $modelPath = $modulePath . 'model';

        $modelFilePath = $modelPath . DIRECTORY_SEPARATOR . $name . '.php';
        if (is_file($modelFilePath) && !$force) {
            throw new LogicException(sprintf("%s 模型类已存在", $modelFilePath));
        }

        if (!is_dir($modulePath)) {
            throw new LogicException(sprintf("%s 模块不存在", $modulePath));
        }
        if (!is_dir($modelPath)) {
            if (!mkdir($modelPath, 0755, true)) {
                throw new LogicException(sprintf("创建目录失败：%s", $modelPath));
            }
        }

        $template = file_get_contents($this->getTemplate());
        $match = [
            '{%date%}'   => date('Y-m-d'),
            '{%module%}' => $module,
            '{%name%}'   => $name,
            '{%table%}'  => Str::snake($module . $name)
        ];
        $content = str_replace(array_keys($match), array_values($match), $template);
        if (!file_put_contents($modelFilePath, $content)) {
            throw new LogicException(sprintf("创建模型类失败：%s", $modelFilePath));
        }
        return $modelFilePath;
    }

    private function getTemplate(): string
    {
        return __DIR__ . '/stubs/model.stub';
    }
}