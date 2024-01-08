<?php

namespace mof\command;

use mof\Module;
use think\migration\command\migrate\Run;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option as InputOption;
use think\console\Output;

class MigrateRun extends Run
{
    protected $module;

    public function configure(): void
    {
        $this->setName('mof-migrate:run')
            ->setDescription('执行数据库迁移')
            ->addArgument('module', Argument::REQUIRED, '指定模块名称')
            ->addOption('--target', '-t', InputOption::VALUE_REQUIRED, '指定迁移版本')
            ->addOption('--date', '-d', InputOption::VALUE_REQUIRED, '指定迁移日期')
            ->setHelp(<<<EOT
The <info>mof-migrate:run</info> command runs all available migrations, optionally up to a specific version

<info>php think catch-migrate:run module</info>
<info>php think catch-migrate:run module -t 20110103081132</info>
<info>php think catch-migrate:run module -d 20110103</info>
<info>php think catch-migrate:run -v</info>

EOT
            );
    }

    protected function execute(Input $input, Output $output): void
    {
        $this->module = strtolower($input->getArgument('module'));
        $version = $input->getOption('target');
        $date = $input->getOption('date');

        // run the migrations
        $start = microtime(true);
        if (null !== $date) {
            $this->migrateToDateTime(new \DateTime($date));
        } else {
            $this->migrate($version);
        }
        $end = microtime(true);

        // 重置 migrations 在循环冲无法重复使用
        $this->migrations = null;
        $output->writeln('');
        $output->writeln('<comment>All Done. Took ' . sprintf('%.4fs', $end - $start) . '</comment>');
    }

    protected function getPath(): string
    {
        return Module::getModuleMigrationPath($this->module);
    }
}
