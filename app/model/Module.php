<?php

namespace app\model;

use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Model;

class Module extends \mof\Model
{
    protected $createTime = 'install_at';
    protected $updateTime = false;

    /**
     * 已安装且有效的模块
     * @param string $parentModule
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function enabledModules(string $parentModule = ''): array
    {
        $rows = Module::where('status', 1)
            ->order('order', 'desc')
            ->select();
        $options = [];
        foreach ($rows as $row) {
            $info = \mof\Module::info($row->getAttr('name'));
            if (!$info) continue;
            if ($parentModule && (($info['parent'] ?? '') !== $parentModule)) continue;
            $info['id'] = $row->getAttr('id');
            $options[] = $info;
        }
        return $options;
    }

    /**
     * @param Model $model
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function onAfterWrite(Model $model): void
    {
        self::writeCache();
    }

    /**
     * @param Model $model
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function onAfterDelete(Model $model): void
    {
        //删除config表中的数据
        Config::where('module', $model->name)->delete();
        self::writeCache();
    }

    /**
     * 获取有独立权限分类的模块
     * @return array
     */
    public static function sgPermModules(): array
    {
        return self::where('sg_perm', 1)
            ->where('status', 1)
            ->order('order', 'asc')
            ->column(['id', 'name', 'title'], 'name');
    }

    /**
     * 安装权限规则
     * @param array $moduleInfo
     * @return void
     * @throws \Exception
     */
    public static function installPerms(array $moduleInfo): void
    {
        $model = new static;
        $model->startTrans();
        try {
            //先卸载旧菜单(不调用模型事件)
            self::uninstallPerms($moduleInfo['name']);
            //先新建权限组
            $group = Perm::create([
                'type'   => 'group',
                'module' => $moduleInfo['name'],
                'title'  => $moduleInfo['title'],
                'icon'   => $moduleInfo['perm_icon'] ?? '',
            ]);
            foreach ($moduleInfo['perms'] as $perm) {
                //从数组 $perm 中获取键名为perm、title、url、icon的值，组成一个新数组
                $data = array_intersect_key($perm, array_flip(['perm', 'title', 'url', 'icon', 'sort']));
                $data['pid'] = $group->id;
                $data['type'] = 'menu';
                $data['module'] = $moduleInfo['name'];
                $data['status'] = 1;
                //新建根权限
                $prem = Perm::create($data);
                //新建action类型权限
                if (isset($perm['actions']) && is_array($perm['actions'])) {
                    $actions = array_map(function ($action) use ($prem) {
                        return $prem->createAction($action);
                    }, $perm['actions']);
                }
            }
            $model->commit();
        } catch (\Exception $e) {
            $model->rollback();
            throw $e;
        }
    }

    /**
     * 卸载权限规则
     * @param string $name 模块名
     * @return void
     */
    public static function uninstallPerms(string $name): void
    {
        Perm::where('module', $name)->delete();
    }

    /**
     * 禁用模块权限规则
     * @param string $name 模块名
     * @return void
     */
    public static function disablePerms(string $name): void
    {
        Perm::where('module', $name)->update(['status' => 0]);
    }

    /**
     * 启用模块权限规则
     * @param string $name 模块名
     * @return void
     */
    public static function enablePerms(string $name): void
    {
        Perm::where('module', $name)->update(['status' => 1]);
    }

    /**
     * 缓存正在使用的模块列表
     * @return void
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public static function writeCache(): void
    {
        //获取已启用的数据
        $rows = self::where('status', 1)
            ->order('order', 'asc')
            ->select();
        //写入缓存
        \mof\Module::writeEnabledModules($rows->toArray());
    }
}