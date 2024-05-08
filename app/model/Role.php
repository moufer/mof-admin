<?php

namespace app\model;

use mof\Model;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\model\Collection;
use think\model\relation\HasMany;

/**
 * 管理员角色模型
 * @package app\model
 * @property Collection $role_perms 菜单
 * @property array $perms 角色菜单
 */
class Role extends Model
{
    protected $name = 'system_role';

    protected array $searchFields = [
        'id'       => 'integer',
        'category' => 'string',
        'name'     => ['string', 'op' => 'like'],
        'status'   => ['integer', 'zero' => true],
    ];

    public static function onAfterDelete(Model $model): void
    {
        //删除相应的角色规则
        RolePerm::where('role_id', $model->id)->delete();
    }

    /**
     * 设置权限
     * @param array $permIds
     * @return bool
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function setPermission(array $permIds): bool
    {
        //获取完整的权限规则id集合
        //$permIds = Perm::getCompletePermIds($permIds);
        //获取已有的权限规则
        $perms = $this->rolePerms()->select();

        $ids = [];
        foreach ($perms as $perm) {
            $exists = false;
            //查找$data里是否有权限规则，如果有则更新，没有则删除
            /** @var RolePerm $perm */
            foreach ($permIds as $key => $permId) {
                if ((int)$permId === $perm->perm_id) {
                    $exists = true;
                    unset($permIds[$key]);//删除已经找到的权限规则
                    break;
                }
            }
            !$exists && $ids[] = $perm->id; //记录已有的权限规则id
        }
        //删除已取消的权限规则id
        RolePerm::where('role_id', $this->getAttr('id'))
            ->whereIn('id', $ids)
            ->delete();
        //添加新的权限规则
        $insertData = [];
        foreach ($permIds as $permId) {
            $insertData[] = [
                'role_id' => $this->getAttr('id'),
                'perm_id' => $permId,
            ];
        }
        //插入新的权限规则
        if ($insertData) {
            RolePerm::insertAll($insertData);
        }
        return true;
    }

    /**
     * 获取权限
     * @param string $category 权限分类
     * @param array|null $module 模块名
     * @return array
     * @throws DbException
     */
    public function getPerms(string $category = 'system', array $module = null): array
    {
        if ($this->getAttr('id') > 1) {
            $permIds = Perm::getCompletePermIds($this->role_perms->column('perm_id'));
            $where = [];
            $where[] = ['id', 'in', $permIds];
            $category && $where[] = ['category', '=', $category];
            $permRows = Perm::where($where)->select();
            $perms = [];
            foreach ($permRows as $perm) {
                if ($module && in_array($perm->module, $module)) continue;
                $perms[] = $perm;
            }
        } else {
            //超级管理员，获取所有菜单
            try {
                $perms = Perm::where('status', '=', 1)
                    ->where('category', '=', $category)
                    ->order('sort', 'asc')
                    ->select()
                    ->filter(fn($item) => !$module || in_array($item->module, $module))
                    ->toArray();
            } catch (DbException $e) {
                $perms = [];
            }
        }
        return $perms;
    }

    public function rolePerms(): HasMany
    {
        return $this->hasMany(RolePerm::class, 'role_id');
    }

    public function getPermsAttr(): array
    {
        if ($this->getAttr('id') > 1) {
            $rolePerms = $this->role_perms;
            $perms = [];
            foreach ($rolePerms as $rolePerm) {
                $perm = $rolePerm->perm;
                if (!$perm || $perm->status !== 1) continue;
                $perms[] = $perm;
            }
        } else {
            //超级管理员，获取所有菜单
            $perms = Perm::where('status', '=', 1)
                ->order('sort', 'asc')
                ->select()
                ->toArray();
        }
        return $perms;
    }

    public function getPermIdsAttr(): array
    {
        $permIds = [];
        foreach ($this->perms as $perm) {
            $permIds[] = $perm['id'];
        }
        return $permIds;
    }


}