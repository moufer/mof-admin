<?php

namespace app\controller;

use app\front\form\PermForm;
use app\library\Controller;
use app\logic\PermLogic;
use mof\annotation\Inject;
use mof\ApiResponse;
use mof\utils\Arr;
use think\response\Json;

class Perm extends Controller
{
    #[inject]
    protected PermLogic $logic;

    #[Inject]
    protected PermForm $form;

    public function index(): Json
    {
        $rows = $this->logic->select($this->request->searcher());
        return ApiResponse::success(
            Arr::generateMenuTree($rows->toArray()) //数据转换成树形结构
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
        return ApiResponse::success($this->logic->read($id));
    }

    public function save(): Json
    {
        $this->logic->save($this->form->get());
        return ApiResponse::success();
    }

    public function update($id): Json
    {
        $this->logic->update($id, $this->form->get());
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

}