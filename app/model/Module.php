<?php

namespace app\model;

use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Model;

class Module extends \mof\Model
{
    protected $name = 'system_module';

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
     * 模块列表kv
     * @return array
     */
    public static function modulesList(): array
    {
        $data = array_values(Module::sgPermModules());
        $result = [];
        foreach ($data as $item) {
            $result[$item['name']] = $item['title'];
        }
        return $result;
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