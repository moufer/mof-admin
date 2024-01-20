<?php

namespace app\concern;

use app\library\Controller;
use mof\ApiResponse;
use mof\Model;
use think\response\Json;

/**
 * 软删除
 * @mixin Controller
 * @method  onRestoreBefore(Model|Model[] $list) 恢复前回调
 * @method  onRestoreAfter(Model|Model[] $model) 恢复后回调
 */
trait SoftDelete
{
    /**
     * 获取软删除的数据
     * @return Json
     */
    public function trashedList(): Json
    {
        $pageSize = $this->request->get('page_size/d', 10);
        $rows = call_user_func([$this->modelName, 'onlyTrashed'])->paginate($pageSize);
        //查询后操作
        if (method_exists($this, 'onSelectAfter')) {
            $rows = call_user_func([$this, 'onSelectAfter'], $rows);
        }
        return ApiResponse::success($rows);
    }

    /**
     * 真实删除
     * @param $id
     * @return Json
     */
    public function forceDelete($id): Json
    {
        //判断是不是delete请求
        if (!$this->request->isDelete()) {
            return ApiResponse::error('请求方式错误');
        }

        if (!$model = call_user_func([$this->modelName, 'find'], $id)) {
            return ApiResponse::error('数据不存在');
        }
        //删除前
        if (method_exists($this, 'onDeleteBefore')) {
            call_user_func([$this, 'onDeleteBefore'], $model);
        }
        $model->force()->delete();
        //删除后操作
        if (method_exists($this, 'onDeleteAfter')) {
            call_user_func([$this, 'onDeleteAfter'], $model);
        }
        return ApiResponse::success();
    }

    /**
     * 真实批量删除
     * @return Json
     */
    public function forceDeletes(): Json
    {
        if (!$this->request->isPost()) {
            return ApiResponse::error('请求方式错误');
        } elseif (!$ids = $this->request->param($this->model->getPk() . '/a')) {
            return ApiResponse::error('未选择删除项');
        }
        $models = call_user_func([$this->modelName, 'withTrashed'])
            ->whereIn($this->model->getPk(), $ids)
            ->select();
        if (!$models->count()) {
            return ApiResponse::error('数据不存在');
        }
        //删除前
        if (method_exists($this, 'onDeleteBefore')) {
            call_user_func([$this, 'onDeleteBefore'], $models);
        }
        foreach ($models as $model) {
            $model->force()->delete();
        }
        //删除后操作
        if (method_exists($this, 'onDeleteAfter')) {
            call_user_func([$this, 'onDeleteAfter'], $models);
        }
        return ApiResponse::success();
    }

    /**
     * 恢复软删除数据
     * @param $id
     * @return Json
     */
    public function restore($id): Json
    {
        if (!$this->request->isPost()) {
            return ApiResponse::error('请求方式错误');
        }
        //获取数据
        $model = call_user_func([$this->modelName, 'find'], $id);
        //恢复前
        if (method_exists($this, 'onRestoreBefore')) {
            call_user_func([$this, 'onRestoreBefore'], $model);
        }
        //恢复
        $model->trashed() && $model->restore();
        //恢复后
        if (method_exists($this, 'onRestoreAfter')) {
            call_user_func([$this, 'onRestoreAfter'], $model);
        }
        return ApiResponse::success();
    }

    /**
     * 批量恢复软删除数据
     * @return Json
     */
    public function restores(): Json
    {
        if (!$this->request->isPost()) {
            return ApiResponse::error('请求方式错误');
        } elseif (!$ids = $this->request->param($this->model->getPk() . '/a')) {
            return ApiResponse::error('未选择恢复项');
        }
        //获取软删除的数据
        $models = call_user_func([$this->modelName, 'onlyTrashed'])
            ->whereIn($this->model->getPk(), $ids)
            ->select();
        if (method_exists($this, 'onRestoreBefore')) {
            call_user_func([$this, 'onRestoreBefore'], $models);
        }
        //恢复前
        $models->each(function ($model) {
            $model->trashed() && $model->restore();
        });
        //恢复后
        if (method_exists($this, 'onRestoreAfter')) {
            call_user_func([$this, 'onRestoreAfter'], $models);
        }
        return ApiResponse::success();
    }
}