<?php

namespace mof\command;

use mof\Module;
use Phinx\Util\Util;
use think\exception\InvalidArgumentException;
use think\migration\command\seed\Create;
use think\console\Input;
use think\console\input\Argument as InputArgument;
use think\console\Output;

class SeedCreate extends Create
{
    protected function configure()
    {
        $this->setName('mof-seed:create')
            ->setDescription('新建数据填充器')
            ->addArgument('module', InputArgument::REQUIRED, '模块名称')
            ->addArgument('name', InputArgument::REQUIRED, '数据填充器名称')
            ->setHelp(sprintf('%s创建一个数据填充器%s', PHP_EOL, PHP_EOL));
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(Input $input, Output $output)
    {
        $className = $input->getArgument('name');
        $module = $input->getArgument('module');
        $path = $this->mkdir(Module::getModuleSeedPath($module), $module);

        $this->verifyMigrationDirectory($path);

        $path = realpath($path);


        if (!Util::isValidPhinxClassName($className)) {
            throw new \InvalidArgumentException(sprintf('填充器类名 "%s" 无效. 请使用驼峰模式命名.', $className));
        }

        // Compute the file path
        $filePath = $path . DIRECTORY_SEPARATOR . $className . '.php';

        if (is_file($filePath)) {
            throw new \InvalidArgumentException(sprintf('文件 "%s" 已存在', basename($filePath)));
        }

        // inject the class names appropriate to this seeder
        $contents = file_get_contents($this->getTemplate());
        $classes = [
            'SeederClass' => $className,
        ];
        $contents = strtr($contents, $classes);

        if (false === file_put_contents($filePath, $contents)) {
            throw new \RuntimeException(sprintf('文件 "%s" 写入失败', $filePath));
        }

        $output->writeln('<info>created</info> .' . str_replace(getcwd(), '', $filePath));
    }

    protected function getTemplate()
    {
        return __DIR__ . '/stubs/seed.stub';
    }

    private function mkdir($path, $module)
    {
        if (!is_dir($path)) {
            //根据文件夹路径一层一层创建
            $i = strpos($path, DIRECTORY_SEPARATOR . $module);
            $rootPath = substr($path, 0, $i + strlen($module) + 1);
            $migratePath = substr($path, $i + strlen($module) + 1);

            if (!is_dir($rootPath)) {
                throw new InvalidArgumentException(
                    sprintf('模块 "%s" 不存在，请先创建模块', $module)
                );
            }

            $dir = $rootPath;
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