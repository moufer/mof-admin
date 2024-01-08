<?php

namespace mof\command;

use mof\Module;
use think\migration\command\seed\Run;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option as InputOption;
use think\console\Output;

class SeedRun extends Run
{
    protected string $module;

    protected function configure()
    {
        // 指令配置
        $this->setName('mof-seed:run')
            ->setDescription('运行数据库填充')
            ->addArgument('module', Argument::REQUIRED, '指定模块')
            ->addOption('--seed', '-s', InputOption::VALUE_REQUIRED, '指定填充器的名称')
            ->setHelp(<<<EOT
                The <info>mof-seed:run</info> command runs all available or individual seeders
<info>php think catch-seed:run module</info>
<info>php think catch-seed:run -s UserSeeder</info>
<info>php think catch-seed:run -v</info>

EOT
            );

    }

    protected function execute(Input $input, Output $output)
    {
        $this->module = strtolower($input->getArgument('module'));
        $seed = $input->getOption('seed');

        // run the seed(ers)
        $start = microtime(true);
        $this->seed($seed);
        $end = microtime(true);
        $this->seeds = null;
        $output->writeln('<comment>All Done. Took ' . sprintf('%.4fs', $end - $start) . '</comment>');

    }

    /**
     * Run seeders from the specified path.
     * @return string
     */
    protected function getPath(): string
    {
        return Module::getModuleSeedPath($this->module);
    }

}
