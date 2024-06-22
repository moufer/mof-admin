<?php

namespace mof\command;

use mof\Module;
use RuntimeException;
use think\helper\Str;
use think\migration\command\migrate\Create;
use Phinx\Util\Util;
use think\console\Input;
use think\console\input\Argument as InputArgument;
use think\console\Output;
use think\exception\InvalidArgumentException;

class MigrateCreate extends Create
{
    protected function configure()
    {
        /**
         * php think mof-migrate:create module name
         */
        $this->setName('mof-migrate:create')
            ->setDescription('新建数据库迁移')
            ->addArgument('module', InputArgument::REQUIRED, '模块名称')
            ->addArgument('name', InputArgument::REQUIRED, '数据库迁移名称')
            ->setHelp(sprintf('%s创建一个数据库迁移文件%s', PHP_EOL, PHP_EOL));
    }

    protected function execute(Input $input, Output $output)
    {
        $module = $input->getArgument('module');
        $className = $input->getArgument('name');
        $className = Str::studly($module . '_' . $className);
        $path = $this->create($module, $className);
        $output->writeln('<info>created</info> .' . str_replace(getcwd(), '', realpath($path)));
    }

    protected function create($module, $className): string
    {
        $path = $this->mkdir(Module::getModuleMigrationPath($module), $module);

        if (!Util::isValidPhinxClassName($className)) {
            throw new InvalidArgumentException(sprintf('文件名 "%s" 无效. 请使用驼峰命名模式命名.', $className));
        }

        if (!Util::isUniqueMigrationClassName($className, $path)) {
            throw new InvalidArgumentException(sprintf('数据库迁移类 "%s" 已存在.', $className));
        }

        // Compute the file path
        $fileName = Util::mapClassNameToFileName($className);

        $filePath = $path . DIRECTORY_SEPARATOR . $fileName;

        if (is_file($filePath)) {
            throw new InvalidArgumentException(sprintf('文件 %s 已存在', $filePath));
        }

        // Verify that the template creation class (or the aliased class) exists and that it implements the required interface.
        $aliasedClassName = null;

        // Load the alternative template if it is defined.
        $contents = file_get_contents($this->getTemplate());

        // inject the class names appropriate to this migration
        $contents = strtr($contents, [
            'MigratorClass' => $className,
        ]);

        if (false === file_put_contents($filePath, $contents)) {
            throw new RuntimeException(sprintf('文件 %s 无法写入', $path));
        }

        return $filePath;
    }

    protected function getTemplate()
    {
        return __DIR__ . '/stubs/migrate.stub';
    }

    private function mkdir($path, $module)
    {
        if (!is_dir($path)) {
            //根据文件夹路径一层一层创建
            $modulePath = Module::path($module);
            $migratePath = str_replace($modulePath, '', $path);

            if (!is_dir($modulePath)) {
                throw new InvalidArgumentException(
                    sprintf('模块 "%s" 不存在，请先创建模块', $module)
                );
            }

            $dir = $modulePath;
            foreach (explode(DIRECTORY_SEPARATOR, $migratePath) as $d) {
                $dir .= DIRECTORY_SEPARATOR . $d;
                if (!is_dir($dir)) {
                    if (!mkdir($dir, 0755, true)) {
                        throw new InvalidArgumentException(
                            sprintf('创建目录 "%s" 失败', $dir)
                        );
                    }
                }
            }
        }
        return $path;
    }

}