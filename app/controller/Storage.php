<?php

namespace app\controller;

use app\library\Controller;
use app\concern\Batch;
use app\library\Searcher;
use app\logic\StorageLogic;
use mof\annotation\Inject;
use mof\ApiResponse;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\response\Json;

class Storage extends Controller
{
    #[Inject]
    protected StorageLogic $logic;

    public function index(): Json
    {
        return ApiResponse::success(
            $this->logic->paginate($this->request->searcher())
        );
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

    public function selector(): Json
    {
        return ApiResponse::success(
            $this->logic->paginate(
                $this->request->searcher([
                    'user_type' => 'system',
                    'user_id'   => $this->auth->getId()
                ])
            )
        );
    }
}