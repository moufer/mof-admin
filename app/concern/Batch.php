<?php

namespace app\concern;

use app\library\AdminController;
use mof\ApiResponse;
use think\db\exception\DbException;
use think\response\Json;

/**
 * 批量操作
 * @mixin AdminController
 */
trait Batch
{

    /**
     * 批量更新
     * @return Json
     * @throws DbException
     */
    public function updates(): Json
    {
        //判断是不是put请求
        if (!$this->request->isPut()) {
            return ApiResponse::error('请求方式错误');
        }

        //获取ID(数组集合)
        $ids = $this->request->param($this->model->getPk() . '/a');
        //获取更新字段
        $field = $this->request->param('field/s');
        //获取更新值
        $value = $this->request->param('value/s');

        if (empty($ids)) return ApiResponse::error('未选择更新项');
        if (empty($field)) return ApiResponse::error('未选择更新字段');
        if (false === $value) return ApiResponse::error('未设置更新值');

        $models = $this->model->where($this->model->getPk(), 'in', $ids)->select();
        //编辑前
        if (method_exists($this, 'onUpdateBefore')) {
            call_user_func([$this, 'onUpdateBefore'], $models, [$field => $value]);
        }
        if ($models->count() > 0) {
            $models->each(function ($model) use ($field, $value) {
                $model->save([$field => $value]);
            });
            //编辑后操作
            if (method_exists($this, 'onUpdateAfter')) {
                call_user_func([$this, 'onUpdateAfter'], $models);
            }
        }
        return ApiResponse::success();
    }

    /**
     * 批量删除
     * @return Json
     * @throws DbException
     */
    public function deletes(): Json
    {
        if (!$this->request->isPost()) {
            return ApiResponse::error('请求方式错误');
        } elseif (!$ids = $this->request->param($this->model->getPk() . '/a')) {
            return ApiResponse::error('未选择删除项');
        }
        $models = call_user_func([$this->modelName, 'whereIn'], $this->model->getPk(), $ids)->select();
        if (!$models->count()) {
            return ApiResponse::error('数据不存在');
        }
        //删除前
        if (method_exists($this, 'onDeleteBefore')) {
            call_user_func([$this, 'onDeleteBefore'], $models);
        }
        foreach ($models as $model) {
            $model->delete();
        }
        //删除后操作
        if (method_exists($this, 'onDeleteAfter')) {
            call_user_func([$this, 'onDeleteAfter'], $models);
        }
        return ApiResponse::success();
    }
}