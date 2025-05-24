<?php

/**
 * Author: moufer <moufer@163.com>
 * Date: 2025/1/14 00:57
 */

namespace app\concern;

use app\library\Controller;
use mof\ApiResponse;
use mof\front\Form;
use mof\Logic;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\response\Json;

/**
 * CurdTrait
 * 增删改查的通用方法
 * @package app\concern
 * @author moufer <moufer@163.com>
 * @mixin Controller
 * @property Form $form
 * @property Logic $logic
 */
trait CurdTrait
{
    /**
     * 列表
     * @return Json
     */
    public function index(): Json
    {
        return ApiResponse::success($this->logic->paginate($this->request->searcher()));
    }

    /**
     * 创建
     * @return Json
     */
    public function create(): Json
    {
        return ApiResponse::success($this->form->build());
    }

    /**
     * 编辑
     * @param $id
     * @return Json
     * @throws DataNotFoundException
     */
    public function edit($id): Json
    {
        return ApiResponse::success($this->form->build($this->logic->read($id)));
    }

    /**
     * 查看
     * @param $id
     * @return Json
     * @throws DataNotFoundException
     */
    public function read($id): Json
    {
        return ApiResponse::success($this->logic->read($id));
    }

    /**
     * 保存
     * @return Json
     * @throws DbException
     */
    public function save(): Json
    {
        $this->logic->save($this->form->get());
        return ApiResponse::success();
    }

    /**
     * 更新
     * @param $id
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function update($id): Json
    {
        $this->logic->update($id, $this->form->get());
        return ApiResponse::success();
    }

    /**
     * 删除
     * @param $id
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function delete($id): Json
    {
        $this->logic->delete($id);
        return ApiResponse::success();
    }
}
