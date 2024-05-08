<?php

namespace module\miniapp\event;

use app\model\Admin;
use app\model\Perm;
use JetBrains\PhpStorm\ArrayShape;
use module\miniapp\model\AdminRelation;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 获取模块独立权限
 */
class GetPerms
{
    /**
     * @param $params array [admin,module]
     * @return array|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function handle(#[ArrayShape([0 => Admin::class, 1 => 'string'])] array $params): ?array
    {
        list($admin, $module) = $params;
        if ('miniapp' === $module) {
            return $this->getPerms($admin);
        }
        return null;
    }

    /**
     * 获取权限列表
     * @param Admin $admin
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    protected function getPerms(Admin $admin): array
    {
        $where[] = ['category', '=', 'miniapp'];
        if (!$admin->is_super_admin) {
            if ('miniapp' === $admin->module) {
                $modules = AdminRelation::where(['admin_id' => $admin->id])->column('module');
                $modules = array_merge($modules, ['system']);
                $where[] = ['module', 'in', $modules];
            } else {
                return $admin->role->getPerms('miniapp');
            }
        }
        return Perm::where($where)->order('sort', 'asc')->select()->toArray();
    }
}