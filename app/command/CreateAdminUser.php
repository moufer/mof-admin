<?php
declare (strict_types=1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;

class CreateAdminUser extends Command
{
    protected function configure(): void
    {
        // 指令配置
         $this->setName('mof-admin:create-admin-user')
            ->addArgument('user', Argument::OPTIONAL, "your username")
            ->addArgument('password', Argument::OPTIONAL, "your password")
            ->setDescription('create admin user');
    }

    protected function execute(Input $input, Output $output): void
    {
        // 指令输出
        $username = $input->getArgument('user');
        $password = $input->getArgument('password');
        if (!$username) {
            $output->writeln('please input username');
        } else if (!$password) {
            $output->writeln('please input password');
        } else {
            $admin = new \app\model\Admin;
            $admin->save([
                'username' => $username,
                'password' => $password,
            ]);
            $output->writeln("create admin user[$username] success!");
        }
    }
}
