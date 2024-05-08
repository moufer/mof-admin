<?php

namespace app\logic;

use app\model\Role;
use mof\exception\LogicException;
use mof\Logic;
use mof\Model;
use think\Collection;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;

class RoleLogic extends Logic
{
    /**
     * @var Role 模型
     */
    protected $model;

    public function save($params): Model
    {
        $model = parent::save($params);
        $model->setPermission($params['perm_ids']);
        return $model;
    }

    /**
     * @throws DbException
     * @throws DataNotFoundException
     */
    public function update($id, $params): Model
    {
        if (1 === (int)$id) {
            throw new LogicException('禁止修改超级管理员角色');
        }
        $model = parent::update($id, $params);
        $model->setPermission($params['perm_ids']);
        return $model;
    }

    public function delete($id): bool
    {
        if (1 === (int)$id) {
            throw new LogicException('禁止删除超级管理员角色');
        }
        return parent::delete($id);
    }

    public function deletes($ids): Collection
    {
        if ($ids && in_array(1, $ids)) {
            throw new LogicException('禁止删除超级管理员角色');
        }
        return parent::deletes($ids);
    }
}