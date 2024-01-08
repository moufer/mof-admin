<?php

namespace app\logic;

use app\model\Storage;
use League\Flysystem\FileNotFoundException;
use mof\exception\LogicException;
use mof\Logic;
use mof\Searcher;

class StorageLogic extends Logic
{
    public function search(Searcher $searcher): \think\Paginator
    {
        return $searcher->model(Storage::class)
            ->with(['user'])->auto()->paginate();
    }

    public function delete(string|int $id)
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
    }
}