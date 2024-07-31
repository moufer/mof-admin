<?php

namespace app\controller;

use app\front\form\RoleForm;
use app\library\Controller;
use app\logic\RoleLogic;
use mof\annotation\AdminPerm;
use mof\annotation\Inject;
use mof\ApiResponse;
use think\response\Json;

#[AdminPerm(
    title: '角色管理', url: 'system/role', actions: '*,!permission',
    sort: 3, icon: 'Avatar', group: 'perm'
)]
class Role extends Controller
{
    #[Inject]
    protected RoleLogic $logic;

    #[Inject]
    protected RoleForm $form;

    public function index(): Json
    {
        return ApiResponse::success(
            $this->logic->paginate($this->request->searcher())
        );
    }

    public function create(): Json
    {
        return ApiResponse::success($this->form->withDefaultValue('.get')->build());
    }

    public function edit($id): Json
    {
        return ApiResponse::success($this->form->build($this->logic->read($id)));
    }

    public function save(): Json
    {
        $this->logic->save($this->form->get());
        return ApiResponse::success();
    }

    public function read($id): Json
    {
        return ApiResponse::success(
            $this->logic->read($id)->append(['perms', 'perm_ids'])
        );
    }

    public function update($id): Json
    {
        $this->logic->update($id, $this->form->withFixed(['id' => $id])->get());
        return ApiResponse::success();
    }

    public function delete($id): Json
    {
        $this->logic->delete($id);
        return ApiResponse::success();
    }

    public function deletes(): Json
    {
        $this->logic->deletes($this->request->getPostIds());
        return ApiResponse::success();
    }

    public function permission($id): Json
    {
        $role = $this->logic->read($id);

        if ($this->request->isPost()) {
            if (!$data = $this->request->param('perms/a')) {
                return ApiResponse::fail('未提供权限配置');
            }
            $role->setPermission($data);
            return ApiResponse::success();
        } else if ($this->request->isGet()) {
            return ApiResponse::success($role->perms);
        } else {
            return ApiResponse::fail('请求方式错误');
        }
    }
}