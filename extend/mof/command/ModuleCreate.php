<?php
/**
 * Project: MofAdmin
 * Author: moufer <moufer@163.com>
 * Date: 2024/6/22 11:44
 */

namespace mof\command;

use mof\exception\LogicException;
use mof\Module;
use think\console\Command;
use think\console\input\Argument as InputArgument;
use think\console\Input;
use think\console\Output;

class ModuleCreate extends Command
{
    protected string $module;

    protected function configure(): void
    {
        $this->setName('mof-module:create')
            ->setDescription('创建一个自定义模块')
            ->addArgument('name', InputArgument::REQUIRED, ' 模块名称')
            ->addArgument('title', InputArgument::OPTIONAL, ' 模块标题');
    }

    protected function execute(Input $input, Output $output): void
    {
        $module = $input->getArgument('name');
        $title = $input->getArgument('title');
        $files = $this->create($module, empty($title) ? $module : $title);
        $output->writeln("<info>Module created successfully.</info>");
        array_map(fn($file) => $output->writeln("<info>$file</info>"), $files);
    }

    private function create($name, $title = ''): array
    {
        $moduleNames = ['admin', 'system', 'console', 'api', 'mof', 'moufer'];
        if (in_array($name, $moduleNames)) {
            throw new LogicException(sprintf("%s 为内置关键字，禁止创建", $name));
        }
        //
        $modulePath = Module::path($name);
        if (is_dir($modulePath) || is_file($modulePath)) {
            throw new LogicException(sprintf("%s 模块已存在", $modulePath));
        } else if (!mkdir($modulePath, 0755, true)) {
            throw new LogicException(sprintf("创建目录失败：%s", $modulePath));
        }

        $this->module = $name;

        //写入module.json
        $newFiles[] = $this->createModuleJson($modulePath, [
            'name'  => $name,
            "title" => $title,
        ]);
        $newFiles[] = $this->createRoute($modulePath);

        return $newFiles;
    }

    private function createModuleJson($modulePath, array $data): string
    {
        //获取模版
        $file = __DIR__ . '/stubs/module.json';
        $defaultInfo = json_decode(file_get_contents($file), true);

        //写入
        $content = json_encode(array_merge($defaultInfo, $data), JSON_UNESCAPED_UNICODE);
        $filePath = $modulePath . 'module.json';
        if (!file_put_contents($filePath, $content)) {
            throw new LogicException(sprintf("创建module.json文件失败：%s", $filePath));
        }
        return $filePath;
    }

    private function createRoute($modulePath): string
    {
        //获取模版
        $file = __DIR__ . '/stubs/route.stub';
        $template = file_get_contents($file);

        $match = [
            '{%module%}' => $this->module,
        ];
        $filePath = $modulePath . 'route.php';
        $content = str_replace(array_keys($match), array_values($match), $template);
        if (!file_put_contents($filePath, $content)) {
            throw new LogicException(sprintf("创建route.php文件失败：%s", $modulePath . '/route.php'));
        }

        return $filePath;
    }

}