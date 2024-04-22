<?php

namespace mof;

use app\library\InstallPerm;
use mof\exception\LogicException;
use mof\utils\Dir;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Console;

class InstallModule
{
    protected InstallPerm $installPerm;

    /**
     * @param string $moduleName 模块名称
     * @return InstallModule
     */
    public static function make(string $moduleName): static
    {
        $moduleInfo = \mof\Module::info($moduleName);
        if (!$moduleInfo) {
            throw new LogicException('模块不存在');
        }
        return new static($moduleInfo);
    }

    /**
     * @param array $moduleInfo 模块信息
     */
    public function __construct(protected array $moduleInfo)
    {
        $this->installPerm = new InstallPerm($moduleInfo);
    }

    /**
     * 安装
     * @return void
     * @throws DbException
     */
    public function install(): void
    {
        if (!\mof\Module::verifyIntegrity($this->moduleInfo['name'])) {
            throw new LogicException('模块不存在或文件不完整');
        }
        $this->checkRequires();        //检测依赖
        $this->installConsole();       //安装数据库
        $this->installPerm->install(); //安装权限
        $this->copyResource();         //复制资源文件
    }

    /**
     * 卸载
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function uninstall(): void
    {
        $this->checkChildren('uninstall');  //检测依赖
        $this->removeResource();            //删除资源文件
        $this->installPerm->uninstall();    //卸载权限
        $this->uninstallConsole();          //删除数据
    }

    /**
     * 禁用
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function disable(): void
    {
        $this->checkChildren('disable');        //检测依赖
        $this->installPerm->disable();          //禁用权限
    }

    /**
     * 启用
     * @return void
     * @throws DbException
     */
    public function enable(): void
    {
        $this->checkRequires();       //检测依赖
        $this->installPerm->enable(); //启用权限
    }

    /**
     * 依赖检测
     * @return void
     * @throws DbException
     */
    protected function checkRequires(): void
    {
        $requireModules = $this->moduleInfo['requires'] ?? []; //获取依赖的模块
        $defectModules = [];                                   //缺失的模块
        foreach ($requireModules as $module) {
            $info = \mof\Module::info($module);
            $exists = \app\model\Module::where(['name' => $module, 'status' => 1])->count() > 0;
            if ($info && $exists) continue;
            $defectModules[] = $info ? $info['title'] : $module;
        }
        if ($defectModules) {
            throw new LogicException(
                sprintf('依赖模块【%s】未安装或未启用', implode('、', $defectModules))
            );
        }
    }

    /**
     * 删除前检测是否存在依赖我的模块
     * @param string $type
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    protected function checkChildren(string $type): void
    {
        $children = [];
        if ($type === 'uninstall') {
            $modules = \app\model\Module::select();
        } elseif ($type === 'disable') {
            $modules = \app\model\Module::where('status', 1)->select();
        } else {
            throw new LogicException('无效的checkChildren类型');
        }
        $modules->each(function ($module) use (&$children) {
            $info = \mof\Module::info($module->getAttr('name'));
            if ($info && in_array($this->moduleInfo['name'], $info['requires'] ?? [])) {
                $children[] = $info['title'];
            }
        });
        if ($children) {
            throw new LogicException(
                sprintf('模块被依赖【%s】，无法停用或删除。', implode('、', $children))
            );
        }
    }

    /**
     * 复制资源文件到对外访问目录中
     * @return void
     */
    public function copyResource(): void
    {
        $resourcePath = Module::getModuleResourcesPath($this->moduleInfo['name']);
        if (is_dir($resourcePath)) {
            //获取对外访问目录
            $destPath = Module::getModuleResourcesPath($this->moduleInfo['name'], true);
            //复制过去
            Dir::copyDir($resourcePath, $destPath);
        }
    }

    /**
     * 删除资源
     * @return void
     */
    public function removeResource(): void
    {
        $resourcePath = Module::getModuleResourcesPath($this->moduleInfo['name']);
        $destPath = Module::getModuleResourcesPath($this->moduleInfo['name'], true);
        if (is_dir($resourcePath) && is_dir($destPath)) {
            //删除对外访问目录里的同名文件（不直接删除目录是未了避免删除用户自己添加）
            Dir::removeRedundantFiles($resourcePath, $destPath);
        }
        //删除空文件夹
        Dir::removeEmptySubdirs($destPath);
    }

    protected function installConsole(): void
    {
        Console::call('mof-migrate:run', [$this->moduleInfo['name']]); // 执行模块的数据库迁移
        Console::call('mof-seed:run', [$this->moduleInfo['name']]);    // 执行模块的数据填充
    }

    protected function uninstallConsole(): void
    {
        Console::call('mof-migrate:rollback', [$this->moduleInfo['name']]); // 执行模块的数据库回滚
    }
}