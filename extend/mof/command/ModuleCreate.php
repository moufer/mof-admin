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
        $this->create($module, empty($title) ? $module : $title);
        $output->writeln("<info>Module created successfully.</info>");
    }

    private function create($name, $title = ''): void
    {
        $moduleNames = ['admin', 'system', 'mof'];
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

        //创建module.json文件
        $jsonFilePath = $modulePath . '/module.json';
        $data = [
            'name'        => $name,
            'version'     => '1.0.0',
            "title"       => $title,
            'description' => '模块描述',
            'author'      => '模块作者',
            'keywords'    => [],
            'is_kernel'   => false,
            'requires'    => [],
            'parent'      => '',
            'perms'       => []
        ];

        $content = json_encode($data, JSON_UNESCAPED_UNICODE);
        if (!file_put_contents($jsonFilePath, $content)) {
            throw new LogicException(sprintf("创建module.json文件失败：%s", $jsonFilePath));
        }

    }

}