<?php

namespace app\logic;

use app\model\Storage;
use mof\exception\LogicException;
use mof\Logic;
use mof\Searcher;
use think\Paginator;
use League\Flysystem\FileNotFoundException;


class StorageLogic extends Logic
{
    /**
     * @var Storage 模型
     */
    protected $model;

    public function search(Searcher $searcher): Paginator
    {
        return $searcher->model(Storage::class)->with(['user'])->paginate();
    }

    public function delete($id): bool
    {
        /** @var Storage $model */
        $model = (new Storage)->find($id);
        if (!$model) {
            throw new LogicException('数据不存在');
        }
        if ($model->delete()) {
            //删除文件
            $disk = app('filesystem')->disk($model->getAttr('provider'));
            try {
                return $disk->delete($model->getAttr('path'));
            } catch (FileNotFoundException) {
                return false;
            }
        }
        return false;
    }
}