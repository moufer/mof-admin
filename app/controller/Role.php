<?php

namespace app\controller;

use app\library\AdminController;
use app\logic\RoleLogic;
use mof\annotation\Inject;
use mof\ApiResponse;
use think\response\Json;

class Role extends AdminController
{
    protected array $validateRules = [
        'name|名称'   => 'require|unique:role',
        'status|状态' => 'require|in:0,1',
        'perms|权限'  => 'array',
    ];

    #[Inject]
    protected RoleLogic $roleLogic;

    public function index(): Json
    {
        return ApiResponse::success(
            $this->roleLogic->paginate($this->request->searcher())
        );
    }

    public function read($id): Json
    {
        return ApiResponse::success(
            $this->roleLogic->read($id)->append(['perms', 'perm_ids'])
        );
    }

    public function save(): Json
    {
        $this->roleLogic->save(
            $this->validate($this->request->param(), $this->validateRules)
        );
        return ApiResponse::success();
    }

    public function update($id): Json
    {
        $this->roleLogic->update(
            $id,
            $this->validate($this->request->param(), $this->validateRules)
        );
        return ApiResponse::success();
    }

    public function delete($id): Json
    {
        $this->roleLogic->delete($id);
        return ApiResponse::success();
    }

    public function deletes(): Json
    {
        $this->roleLogic->deletes($this->request->post('ids/d', []));
        return ApiResponse::success();
    }

    public function permission(): Json
    {
        $roleId = $this->request->get('id/d');
        $role = $this->model->find($roleId);
        if (!$role) {
            return ApiResponse::dataNotFound();
        }
        if ($this->request->isPost()) {
            $data = $this->request->param('perms/a');
            if (!$data) {
                return ApiResponse::fail('未提供权限配置');
            }
            $role->setPermission($data);
        } else if ($this->request->isGet()) {
            return ApiResponse::success($role->perms);
        }

        return ApiResponse::success();
    }
}