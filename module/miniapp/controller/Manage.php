<?php

namespace module\miniapp\controller;

use app\library\Controller;
use module\miniapp\logic\MiniAppLogic;
use mof\annotation\Inject;
use mof\ApiResponse;
use think\response\Json;

/**
 * 小程序管理
 */
class Manage extends Controller
{
    #[Inject]
    protected MiniAppLogic $logic;

    protected function formValidate(): void
    {
        $this->formValidate = [
            'param' => [
                'type', 'title', 'appid', 'app_secret',
            ],
            'rule'  => [
                'type|类型'            => 'require|in:wechat',
                'title|名称'           => 'require',
                'appid|AppID'          => 'require|unique:miniapp',
                'app_secret|AppSecret' => 'require',
            ]
        ];
        parent::formValidate();
    }

    public function index(): Json
    {
        return ApiResponse::success(
            $this->logic->paginate($this->request->searcher())
        );
    }

    public function read($id): Json
    {
        return ApiResponse::success(
            $this->logic->read([$id, $this->request->param('append')])
        );
    }

    public function save(): Json
    {
        $this->logic->save($this->form->get());
        return ApiResponse::success($this->logic->model());
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

}