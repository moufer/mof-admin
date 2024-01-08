<?php

namespace app\logic;

use app\model\Role;
use mof\annotation\InjectModel;
use mof\exception\LogicException;
use mof\Logic;
use mof\Model;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;

class RoleLogic extends Logic
{
    /** @var Role $model 模型 */
    #[InjectModel(Role::class)]
    protected Model $model;

    public function save(array $params): bool
    {
        parent::save($params);
        $this->model->setPermission($params['perms']);
        return true;
    }

    /**
     * @throws DbException
     * @throws DataNotFoundException
     */
    public function update($id, array $params): bool
    {
        if (1 === (int)$id) {
            throw new LogicException('禁止修改超级管理员角色');
        }
        parent::update($id, $params);
        $this->model->setPermission($params['perms']);
        return true;
    }

    public function delete($id): bool
    {
        if (1 === (int)$id) {
            throw new LogicException('禁止删除超级管理员角色');
        }
        return parent::delete($id);
    }

}