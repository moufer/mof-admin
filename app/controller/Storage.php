<?php

namespace app\controller;

use app\library\Controller;
use app\logic\StorageLogic;
use mof\annotation\AdminPerm;
use mof\annotation\Inject;
use mof\ApiResponse;
use think\response\Json;

#[AdminPerm(
    title: '存储管理', url: 'system/storage', actions: '*,!selector',
    sort: 3, icon: 'MostlyCloudy', group: 'system'
)]
class Storage extends Controller
{
    #[Inject]
    protected StorageLogic $logic;

    public function index(): Json
    {
        return ApiResponse::success(
            $this->logic->paginate($this->request->searcher($this->getScopeData()))
        );
    }

    public function delete($id): Json
    {
        $this->logic->withAccess($this->getScopeData())->delete($id);
        return ApiResponse::success();
    }

    public function deletes(): Json
    {
        $this->logic->withAccess($this->getScopeData())->deletes($this->request->getPostIds());
        return ApiResponse::success();
    }

    public function selector(): Json
    {
        return ApiResponse::success(
            $this->logic->paginate($this->request->searcher($this->getScopeData()))
        );
    }

    /**
     * 获取当前资源的访问范围数据
     * @return array
     */
    protected function getScopeData(): array
    {
        return [
            'extend_type' => 'system',
            'extend_id'   => 0,
            'user_type'   => 'system',
            'user_id'     => $this->auth->getId()
        ];
    }
}