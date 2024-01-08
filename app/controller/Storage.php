<?php

namespace app\controller;

use app\library\AdminController;
use app\concern\Batch;
use app\library\Searcher;
use app\logic\StorageLogic;
use mof\annotation\Inject;
use mof\ApiResponse;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\response\Json;

class Storage extends AdminController
{
    protected string $modelName = \app\model\Storage::class;

    /** @var StorageLogic $storageLogic 存储逻辑 */
    #[Inject]
    protected StorageLogic $storageLogic;

    public function index(): Json
    {
        return ApiResponse::success(
            $this->storageLogic->paginate($this->request->searcher())
        );
    }

    public function delete($id): Json
    {
        $this->storageLogic->delete($id);
        return ApiResponse::success();
    }

    public function deletes(): Json
    {
        $this->storageLogic->deletes($this->request->post('ids/d', []));
        return ApiResponse::success();
    }
}