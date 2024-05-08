<?php

namespace app\command;

use app\library\InstallPerm;
use app\model\Admin;
use mof\exception\LogicException;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Db;

class Install extends Command
{
    protected function configure(): void
    {
        $this->setName('mof:install')
            ->setDescription('安装MofAdmin开发系统');
    }

    protected function execute(Input $input, Output $output): void
    {
        try {
            $this->checkConfig();
            $this->checkInstall();
        } catch (LogicException $e) {
            $output->error($e->getMessage());
            return;
        }

        $output->info('欢迎进入MofAdmin安装程序');

        //环境检测
        $output->info('系统环境检测...');
        $this->checkEnv($input, $output);

        $output->info('数据库检测...');
        $this->checkDatabase($input, $output);
        $this->installDatabase($input, $output);

        $output->info("\n请设置网站超级管理员账号");
        try {
            list($username, $password) = $this->createAdmin($input, $output);
            $output->info("后台账号创建成功！");
            $output->info("账号：{$username}\t密码：{$password}");
        } catch (LogicException $e) {
            $output->error($e->getMessage());
            return;
        }

        $this->writeInstalledFile();
        $output->info("\n系统安装完成！请使用上面的账号登录您的MofAdmin系统后台。");
    }

    private function checkInstall(): void
    {
        $file = app()->getRuntimePath() . 'install.lock';
        if (is_file($file)) {
            throw new LogicException("系统已安装。如需重装，请先手动删除{$file}文件。");
        }
    }

    private function checkConfig(): void
    {
        $env = app()->getRootPath() . '.env';
        if (!is_file($env)) {
            throw new ("未检测到{$env}文件，请先配置.env文件。");
        }
    }

    //检查环境
    private function checkEnv(Input $input, Output $output): void
    {
        $result = [];
        //检测php是否是8.1+
        if (version_compare(PHP_VERSION, '8.1', '<')) {
            $result[] = ("请使用 PHP8.1 及以上版本");
        }
        //检测是否安装了fileinfo
        if (!extension_loaded('fileinfo')) {
            $result[] = ("未安装扩展：fileinfo");
        }
        //检查是否安装了iconv扩展
        if (!extension_loaded('iconv')) {
            $result[] = ("未安装扩展：iconv");
        }
        //检测是否安装了PDO
        if (!extension_loaded('pdo')) {
            $result[] = ("未安装扩展：PDO");
        }
        if ($result) {
            $output->error('环境检测失败，请检查环境配置');
            foreach ($result as $index => $error) {
                $output->error(($index + 1) . '.' . $error);
            }
            exit();
        } else {
            $output->info('环境检测通过');
        }
    }

    private function checkDatabase(Input $input, Output $output): void
    {
        if ($this->databaseExists()) {
            //提示用户数据库已存在，确认是否继续安装
            $output->warning("数据库已存在，继续安装覆盖并清空已存在的表数据，是否继续？请输入：yes 或 no");
            if (strtolower(trim(fgets(STDIN))) !== 'yes') {
                $output->error("已取消安装");
                exit();
            }
        }
    }

    private function databaseExists(): bool
    {
        //数据库配置
        $database = config('database');
        $config = $database['connections'][$database['default']];

        //检测数据库和数据表admin是否存在
        $tables = Db::query('SHOW TABLES');
        $tables = array_column($tables, 'Tables_in_' . $config['database']);

        foreach (['system_admin', 'system_storage'] as $table) {
            $table = $config['prefix'] . $table;
            if (in_array($table, $tables)) return true;
        }

        return false;
    }

    private function installDatabase(Input $input, Output $output): void
    {
        $output->warning("是否开始安装系统数据库？请输入：yes 或 no");
        if (strtolower(trim(fgets(STDIN))) !== 'yes') {
            $output->warning("已取消安装");
            exit();
        }
        Db::startTrans();
        try {
            $output->info("\n创建数据表...");
            $output->writeln($this->createTables());
            $output->info("数据表创建成功！");
            $output->info("\n初始化数据...");
            $this->createData();
            $output->info("初始化数据成功！");
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            $output->error('数据表或数据创建失败！');
            $output->error($e->getMessage());
            exit();
        }
    }

    private function createTables(): string
    {
        $buffer = $this->getConsole()->call('mof-migrate:rollback', ['system']);
        $buffer = $this->getConsole()->call('mof-migrate:run', ['system']);
        return $buffer->fetch();
    }

    private function createData(): string
    {
        //加入权限菜单
        InstallPerm::make('system')->install();
        //加入默认数据
        $buffer = $this->getConsole()->call('mof-seed:run', ['system']);
        return $buffer->fetch();
    }

    private function createAdmin(Input $input, Output $output): array
    {
        while (true) {
            $output->info("请输入账号名称：");
            $username = trim(fgets(STDIN));
            if (empty($username)) {
                continue;
            } elseif (strlen($username) < 5) {
                $output->error("账号名称不能少于5个字符");
            } elseif (!preg_match('/^[a-zA-Z0-9_\-]+$/', $username)) {
                $output->error("账号名称只能包含字母和数字以及-_");
            } else {
                break;
            }
        }

        while (true) {
            $output->info("请输入账号密码：");
            $password = trim(fgets(STDIN));
            if (empty($password)) {
                continue;
            } elseif (strlen($password) < 6) {
                $output->error("密码不能少于6个字符");
            } elseif (!preg_match('/[a-zA-Z]+/', $password) || !preg_match('/[0-9]+/', $password)) {
                $output->error("密码必须同时包含字母和数字");
            } elseif (preg_match('/[\s\t\r\n]+/', $password)) {
                $output->error("密码不能包含空格、制表符、换行符、回车符");
            } else {
                break;
            }
        }

        Admin::create([
            'username' => $username,
            'nickname' => $username,
            'password' => $password,
            'role_id'  => 1,
        ]);

        return [$username, $password];
    }

    private function writeInstalledFile(): void
    {
        $file = app()->getRuntimePath() . 'install.lock';
        if (!is_file($file)) {
            file_put_contents($file, '');
        }
    }
}