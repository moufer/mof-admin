<?php

namespace mof\command;

use mof\Module;
use Phinx\Migration\MigrationInterface;
use think\migration\command\migrate\Rollback;

use think\console\Input;
use think\console\Output;
use think\console\input\Argument;
use think\console\input\Option as InputOption;

class MigrateRollback extends Rollback
{
    protected $module;

    protected function configure()
    {
        $this->setName('mof-migrate:rollback')
            ->setDescription('滚上一次或特定的迁移')
            ->addArgument('module', Argument::REQUIRED, '指定要回滚的模块')
            ->addOption('--target', '-t', InputOption::VALUE_REQUIRED, '要回滚到的版本号')
            ->addOption('--date', '-d', InputOption::VALUE_REQUIRED, '要回滚到的日期')
            ->addOption('--force', '-f', InputOption::VALUE_NONE, '强制回滚以忽略断点')
            ->setHelp(<<<EOT
The <info>mof-migrate:rollback</info> command reverts the last migration, or optionally up to a specific version

<info>php think catch-migrate:rollback</info>
<info>php think catch-migrate:rollback module -t 20111018185412</info>
<info>php think catch-migrate:rollback module -d 20111018</info>
<info>php think catch-migrate:rollback -v</info>

EOT
            );
    }

    /**
     * Rollback the migration.
     *
     * @param Input $input
     * @param Output $output
     * @return void
     * @throws \Exception
     */
    protected function execute(Input $input, Output $output)
    {
        $this->module = $input->getArgument('module');
        $version = $input->getOption('target');
        $date = $input->getOption('date');
        $force = !!$input->getOption('force');

        // rollback the specified environment
        $start = microtime(true);
        if (null !== $date) {
            $this->rollbackToDateTime(new \DateTime($date), $force);
        } else {
            if (!$version) {
                $migrations = glob($this->getPath() . DIRECTORY_SEPARATOR . '*.php');
                foreach ($migrations as $migration) {
                    $version = explode('_', basename($migration))[0];
                    $this->rollback($version, $force);
                }
            } else {
                $this->rollback($version, $force);
            }
        }
        $end = microtime(true);
        $this->migrations = null;
        $output->writeln('');
        $output->writeln('<comment>All Done. Took ' . sprintf('%.4fs', $end - $start) . '</comment>');
    }

    /**
     * 获取 migration path
     *
     * @time 2019年12月03日
     * @return string
     */
    protected function getPath(): string
    {
        return Module::getModuleMigrationPath($this->module);
    }

    /**
     *
     * @time 2020年01月21日
     * @param null $version
     * @param bool $force
     * @return void
     */
    protected function rollback($version = null, $force = false)
    {
        $migrations = $this->getMigrations();
        $versionLog = $this->getVersionLog();
        $versions = array_keys($versionLog);

        if ($version) {
            $this->executeMigration($migrations[$version], MigrationInterface::DOWN);
        } else {
            foreach ($migrations as $key => $migration) {
                if (in_array($key, $versions)) {
                    $this->executeMigration($migration, MigrationInterface::DOWN);
                }
            }
        }
    }
}