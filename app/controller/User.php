<?php

namespace app\controller;

use app\library\AdminController;
use app\logic\AdminLogic;
use mof\annotation\Inject;
use mof\ApiResponse;
use mof\Model;
use think\model\Collection;
use think\response\Json;

class User extends AdminController
{
    protected string $modelName    = \app\model\Admin::class;
    protected string $validateName = \app\validate\Admin::class;

    #[Inject]
    protected AdminLogic $adminLogic;

    public function index(): Json
    {
        return ApiResponse::success(
            $this->adminLogic->paginate($this->request->searcher())
        );
    }

    public function read($id): Json
    {
        return ApiResponse::success(
            $this->adminLogic->read($id)
        );
    }

    public function update($id): Json
    {
        return ApiResponse::success(
            $this->adminLogic->update($id, $this->validate('.param', $this->validateName . '.edit'))
        );
    }

    public function save(): Json
    {
        return ApiResponse::success(
            $this->adminLogic->save($this->validate('.param', $this->validateName . '.add'))
        );
    }

    public function delete($id): Json
    {
        return ApiResponse::success(
            $this->adminLogic->delete($id)
        );
    }

    public function deletes(): Json
    {
        $this->adminLogic->deletes($this->request->post('ids/d', []));
        return ApiResponse::success();
    }
}