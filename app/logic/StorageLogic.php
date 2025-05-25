<?php

namespace app\logic;

use app\model\Storage;
use mof\annotation\Inject;
use mof\exception\LogicException;
use mof\Logic;
use mof\Searcher;
use think\Collection;
use think\db\exception\DataNotFoundException;
use think\Paginator;
use League\Flysystem\FileNotFoundException;


class StorageLogic extends Logic
{
    #[Inject(Storage::class)]
    protected $model;

    public function search(Searcher $searcher): Paginator
    {
        return $searcher->model(Storage::class)->paginate();
    }

    public function delete($id): bool
    {
        /** @var Storage $model */
        $model = $this->read($id);
        if ($model->delete()) {
            //删除文件
            return $this->deleteFile($model);
        }
        return false;
    }

    public function deletes($ids): Collection
    {
        $models = $this->model->whereIn($this->model->getPk(), $ids)->select();
        if (!$models->count()) {
            throw new DataNotFoundException('数据不存在');
        }

        foreach ($models as $model) {
            $this->access && $this->checkAccess($model);
            if ($model->delete()) {
                $this->deleteFile($model);
            }
        }

        return $models;
    }

    private function deleteFile($model): bool
    {
        //删除文件
        $disk = app('filesystem')->disk($model->getAttr('provider'));
        try {
            return $disk->delete($model->getAttr('path'));
        } catch (FileNotFoundException) {
            return false;
        }
    }
}