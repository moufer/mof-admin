<?php
declare (strict_types=1);

namespace app\command;

use app\model\AdminMenu;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class Menu extends Command
{
    protected function configure(): void
    {
        // 指令配置
        //选项配置
        // --name dashboard --title 仪表盘 --type url --action add,del,edit,index,multi
        $this->setName('mof-admin:menu')
            ->setDescription('添加后台菜单')
            ->addArgument('action', Argument::OPTIONAL, 'create or delete')
            ->addOption('name', null, Option::VALUE_REQUIRED, 'name', '')
            ->addOption('title', null, Option::VALUE_OPTIONAL, 'title', '')
            ->addOption('type', null, Option::VALUE_OPTIONAL, 'type', '')
            ->addOption('action', null, Option::VALUE_OPTIONAL, 'action', 'add,del,edit,index,multi')
            ->addOption('parent', null, Option::VALUE_OPTIONAL, 'parent name', '');
    }

    protected function execute(Input $input, Output $output): void
    {
        //命了类型，create or delete
        $action = $input->getArgument('action') ?: 'create'; //默认create
        $options = $input->getOptions();
        if (!$options['name']) {
            $output->writeln('name is required');
            return;
        }
        if ($action == 'delete') {
            AdminMenu::deleteMenu($options['name']);
            $output->writeln('delete success');
        } else {
            if ('group' == $options['type']) {
                $options['action'] = '';
            }
            $menu = AdminMenu::addMenu($options);
            $output->writeln($menu ? 'success' : 'fail');
        }
    }
}
