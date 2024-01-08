<?php

namespace app\controller;

use app\library\AdminController;
use app\logic\PermLogic;
use mof\annotation\Inject;
use mof\ApiResponse;
use mof\utils\Arr;
use think\response\Json;

class Perm extends AdminController
{
    protected string $modelName    = \app\model\Perm::class;
    protected string $validateName = \app\validate\Perm::class;

    #[inject]
    protected PermLogic $permLogic;

    public function index(): Json
    {
        $paginate = $this->permLogic->paginate($this->request->searcher());
        return ApiResponse::success(
            Arr::generateMenuTree($paginate->toArray()) //数据转换成树形结构
        );
    }

    public function read($id): Json
    {
        return ApiResponse::success($this->permLogic->read($id));
    }

    public function save(): Json
    {
        $this->permLogic->save($this->validate('.param', $this->validateName . '.add'));
        return ApiResponse::success();
    }

    public function update($id): Json
    {
        $this->permLogic->update($id, $this->validate('.param', $this->validateName . '.edit'));
        return ApiResponse::success();
    }

    public function delete($id): Json
    {
        $this->permLogic->delete($id);
        return ApiResponse::success();
    }

    public function deletes(): Json
    {
        $this->permLogic->deletes($this->request->post('ids/d', []));
        return ApiResponse::success();
    }

}