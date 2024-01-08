<?php

namespace app\logic;

use mof\command\MigrateRollback;
use mof\command\MigrateRun;
use mof\command\SeedRun;
use mof\exception\LogicException;
use mof\InstallModule;
use mof\Logic;

class ModuleLogic extends Logic
{
    /**
     * 载入命令行
     * 用于添加/删除模块数据
     * @return void
     */
    public function loadCommand(): void
    {
        $this->app->console->addCommands([
            MigrateRun::class,
            MigrateRollback::class,
            SeedRun::class,
        ]);
    }

    /**
     * @param array $params
     * @return array
     */
    public function list(array $params = []): array
    {
        $localModules = \mof\Module::list();
        $rows = $this->model->order('order')->column('*', 'name');
        //检测本地模块是否已经安装
        $modules = [];
        foreach ($localModules as $name => $module) {
            $module['status'] = -1; //-1未安装 0已禁用 1已安装
            if (isset($rows[$name])) {
                $module['status'] = $rows[$name]['status'];             //状态
                $module['installed_version'] = $rows[$name]['version']; //已安装的版本
                $module['order'] = $rows[$name]['order'];               //排序
            }
            $modules[$name] = $module;
        }
        //$modules排序,根据order排序, order越小越靠前
        uasort($modules, function ($a, $b) {
            if ($a['order'] == $b['order']) {
                return 0;
            }
            return ($a['order'] < $b['order']) ? -1 : 1;
        });

        //过滤
        return array_filter(array_values($modules), function ($module) use ($params) {
            if (!empty($params['status']) && $module['status'] != $params['status']) {
                return false;
            }
            if (!empty($params['author']) && !str_contains($module['author'], $params['author'])) {
                return false;
            }
            if (!empty($params['title']) && !str_contains($module['title'], $params['title'])) {
                return false;
            }
            return true;
        });
    }

    /**
     * @param $name
     * @return \mof\Model|\think\Model
     * @throws \Exception
     */
    public function install($name)
    {
        $module = $this->model->getByName($name);
        if ($module) {
            throw new LogicException('模块已安装');
        }
        if (!\mof\Module::verifyIntegrity($name)) {
            throw new LogicException('模块不存在或文件不完整');
        }
        $info = \mof\Module::info($name);
        if (!$info || $info['name'] !== $name) {
            throw new LogicException('模块信息不存在或模块名称不一致');
        }

        $install = new InstallModule($name);
        $install->install(); //安装模块（数据库）

        $module = $this->model->create([
            'name'       => $name,
            'title'      => $info['title'] ?? '', //模块名称
            'order'      => $info['order'] ?? 99, //排序
            'version'    => $info['version'] ?? '1.0.0', //版本
            'status'     => 1, //已安装
            'install_at' => date('Y-m-d H:i:s'), //安装时间
            'sg_perm'    => $info['sg_perm'] ?? 0, //使用独立的权限分类
        ]);

        //安装模块权限
        \app\model\Module::installPerms($info);

        //触发安装模块事件
        event('ModuleInstallAfter', $module);

        $this->model = $module;
        return $module;
    }

    /**
     * @param $name
     * @return bool
     */
    public function uninstall($name): bool
    {
        if (!$this->model = $this->model->getByName($name)) {
            throw new LogicException('模块未安装');
        }
        if ($this->model->name === 'admin') {
            throw new LogicException('禁止卸载核心模块');
        }

        $install = new InstallModule($name);
        $install->uninstall();

        //卸载模块权限
        \app\model\Module::uninstallPerms($name);
        $this->model->delete();

        //触发卸载模块事件
        event('ModuleUninstallAfter', $this->model);

        return true;
    }

    /**
     * @param $name
     * @return bool
     */
    public function disable($name): bool
    {
        if (!$module = $this->model->getByName($name)) {
            throw new LogicException('模块未安装');
        }
        if ($module->name === 'admin') {
            throw new LogicException('禁止停用核心模块');
        }
        $module->save(['status' => 0]);

        //禁用权限规则
        \app\model\Module::disablePerms($name);
        //触发停用模块事件
        event('ModuleDisableAfter', $module);

        $this->model = $module;
        return true;
    }

    /**
     * @param $name
     * @return bool
     */
    public function enable($name): bool
    {
        if (!\mof\Module::verifyIntegrity($name)) {
            throw new LogicException('模块不存在或文件不完整。');
        }
        $info = \mof\Module::info($name);
        if (!$info || $info['name'] !== $name) {
            throw new LogicException('模块信息不存在或模块名称不一致');
        }
        if (!$module = $this->model->getByName($name)) {
            throw new LogicException('模块未安装');
        }
        $module->save(['status' => 1]);

        //禁用权限规则
        \app\model\Module::enablePerms($name);
        //触发启用模块事件
        event('ModuleEnableAfter', $module);

        $this->model = $module;
        return true;
    }
}