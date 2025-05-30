<?php

namespace app\controller;

use app\front\form\UserForm;
use app\library\Controller;
use app\logic\AdminLogic;
use mof\annotation\AdminPerm;
use mof\annotation\Inject;
use mof\ApiResponse;
use think\response\Json;

#[AdminPerm(
    title: '管理员管理', url: 'system/user', actions: '*',
    sort: 1, icon: 'UserFilled', group: 'perm'
)]
class User extends Controller
{
    #[Inject]
    protected AdminLogic $logic;

    #[Inject]
    protected UserForm $form;

    public function index(): Json
    {
        return ApiResponse::success(
            $this->logic->paginate(
                $this->request->searcher()->params(['module' => 'system'], false)
            )
        );
    }

    public function create(): Json
    {
        return ApiResponse::success($this->form->build());
    }

    public function edit($id): Json
    {
        return ApiResponse::success($this->form->build($this->logic->read($id)));
    }

    public function read($id): Json
    {
        return ApiResponse::success(
            $this->logic->read($id)->hidden(['password'])
        );
    }

    public function save(): Json
    {
        return ApiResponse::success(
            $this->logic->save($this->form->withFixed(['module' => 'system'])->get())
        );
    }

    public function update($id): Json
    {
        return ApiResponse::success(
            $this->logic->update(
                $id, $this->form->withScene('edit')->withFixed(['id' => $id])->get()
            )
        );
    }

    public function delete($id): Json
    {
        return ApiResponse::success(
            $this->logic->delete($id)
        );
    }

    public function deletes(): Json
    {
        $this->logic->deletes($this->request->post('ids/a', []));
        return ApiResponse::success();
    }
}