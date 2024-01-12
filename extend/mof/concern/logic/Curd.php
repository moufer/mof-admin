<?php

namespace mof\concern\logic;

use mof\Logic;
use mof\Model;
use mof\Searcher;
use think\Collection;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\exception\InvalidArgumentException;
use think\Paginator;

/**
 * 增删改查
 * @mixin Logic
 */
trait Curd
{
    /**
     * 综合分页
     * @param Searcher $searcher
     * @param bool $simple
     * @return Paginator
     */
    public function paginate(Searcher $searcher, bool $simple = false): Paginator
    {
        return $searcher->model($this->model)->paginate($simple);
    }

    /**
     * 读取指定页码或全部数据
     * @param Searcher $searcher
     * @param bool|int $page 指定页码，false表示不分页
     * @return Collection
     */
    public function select(Searcher $searcher, bool|int $page = false): Collection
    {
        return $searcher->model($this->model)->select($page);
    }

    /**
     * 读取数据
     * @param $id
     * @return Model
     * @throws DataNotFoundException
     */
    public function read($id): Model
    {
        try {
            $this->model = $this->model->findOrFail($id);
            return $this->model;
        } catch (ModelNotFoundException) {
            throw new DataNotFoundException('数据不存在');
        }
    }

    /**
     * 添加数据
     * @param $params
     * @return bool
     */
    public function save($params): bool
    {
        return $this->model->data($params, true)->save();
    }

    /**
     * 更新数据
     * @throws DbException
     * @throws DataNotFoundException
     */
    public function update($id, $params): bool
    {
        if (isset($params[$this->model->getPk()])) {
            unset($params[$this->model->getPk()]);
        }

        return $this->read($id)->save($params);
    }

    /**
     * 删除记录
     * @param $id
     * @return bool
     * @throws DataNotFoundException
     */
    public function delete($id): bool
    {
        return $this->read($id)->delete();
    }

    /**
     * 批量更新
     * @param $ids
     * @param $field
     * @param $value
     * @return Collection
     * @throws DbException
     */
    public function updates($ids, $field, $value): Collection
    {
        $models = $this->model->where($this->model->getPk(), 'in', $ids)->select();
        if ($models->count() > 0) {
            $models->each(function ($model) use ($field, $value) {
                $model->save([$field => $value]);
            });
        }
        return $models;
    }

    /**
     * 批量删除
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function deletes($ids): Collection
    {
        $models = $this->model->whereIn($this->model->getPk(), $ids)->select();
        if (!$models->count()) {
            throw new DataNotFoundException('数据不存在');
        }

        foreach ($models as $model) {
            $model->delete();
        }

        return $models;
    }
}