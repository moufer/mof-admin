<?php

namespace module\miniapp\controller\backend;

use app\library\Controller;
use module\miniapp\logic\AdminLogic;
use module\miniapp\validate\AdminValidate;
use mof\annotation\Inject;
use mof\ApiResponse;
use think\response\Json;

class Admin extends Controller
{
    #[Inject]
    protected AdminLogic $logic;

    protected array $formValidate = [
        'param' => [
            'username', 'password', 'name', 'avatar/a', 'email', 'status/d', 'miniapp_ids/a',
        ],
        'rule'  => AdminValidate::class
    ];

    public function index(): Json
    {
        $searcher = $this->request->searcher();
        return ApiResponse::success(
            $this->logic->paginate($searcher->params(['module' => 'miniapp'], false))
        );
    }

    public function read($id): Json
    {
        return ApiResponse::success($this->logic->read($id));
    }

    public function save(): Json
    {
        return ApiResponse::success(
            $this->logic->save($this->form->withFixed(['module' => 'miniapp'])->get())
        );
    }

    public function update($id): Json
    {
        $data = $this->form->withScene('edit')->withFixed(['id' => $id])->get();
        return ApiResponse::success(
            $this->logic->update($id, $data)
        );
    }
}