<?php

namespace app\controller;

use app\library\Controller;
use app\logic\AdminLogic;
use app\model\Admin;
use app\validate\AdminValidate;
use mof\annotation\Inject;
use mof\ApiResponse;
use think\response\Json;

class User extends Controller
{
    #[Inject]
    protected AdminLogic $logic;

    protected array $formValidate = [
        'param' => [
            'username', 'password', 'name', 'avatar/a', 'email', 'role_id/d', 'status/d'
        ],
        'rule'  => AdminValidate::class
    ];

    public function index(): Json
    {
        return ApiResponse::success(
            $this->logic->paginate(
                $this->request->searcher()->params(['module'=>'admin'],false)
            )
        );
    }

    public function save(): Json
    {
        return ApiResponse::success(
            $this->logic->save($this->form->withFixed(['module'=>'admin'])->get())
        );
    }

    public function read($id): Json
    {
        return ApiResponse::success(
            $this->logic->read($id)->hidden(['password'])
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