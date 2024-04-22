<?php

namespace module\miniapp\controller\backend;

use app\library\Controller;
use module\miniapp\front\form\AdminForm;
use module\miniapp\logic\admin\AdminLogic;
use mof\annotation\Inject;
use mof\ApiResponse;
use think\response\Json;

class Admin extends Controller
{
    #[Inject]
    protected AdminLogic $logic;

    #[Inject]
    protected AdminForm $form;

    public function index(): Json
    {
        $searcher = $this->request->searcher();
        return ApiResponse::success(
            $this->logic->paginate($searcher->params(['module' => 'miniapp'], false))
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

    public function delete($id): Json
    {
        $this->logic->delete($id);
        return ApiResponse::success();
    }
}