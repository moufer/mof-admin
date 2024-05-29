<?php

namespace mof\concern\logic;

use mof\exception\LogicException;
use mof\exception\NoPermissionException;
use mof\Logic;
use mof\Model;
use mof\Searcher;
use think\Collection;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Paginator;

/**
 * 增删改查
 * @mixin Logic
 */
trait Curd
{
    /**
     * 是否进行权限检测
     * @var bool
     */
    protected bool $access = false;
    /**
     * 访问/编辑权限检测
     * @var mixed
     */
    protected mixed $accessMethod = null;
    /**
     * 权限检测失败提示
     * @var string
     */
    protected string $accessMessage = '权限不足';

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
            $model = $this->model->findOrFail($id);
            $this->access && $this->checkAccess($model);
            return $model;
        } catch (ModelNotFoundException) {
            throw new DataNotFoundException('数据不存在');
        }
    }

    /**
     * 添加数据
     * @param $params
     * @return Model
     * @throws DbException
     */
    public function save($params): Model
    {
        $class = $this->model::class;
        $model = new $class();
        $model->data($params, true);
        $this->access && $this->checkAccess($model);
        $model->save();
        return $model;
    }

    /**
     * 更新数据
     * @throws DbException
     * @throws DataNotFoundException
     */
    public function update($id, $params): Model
    {
        //更新数据里去掉主键信息
        if (isset($params[$this->model->getPk()])) {
            unset($params[$this->model->getPk()]);
        }

        $model = $this->read($id);
        $model->save($params);
        return $model;
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

    /**
     * 设置访问权限
     * @param mixed $method
     * @param string $message
     * @return $this
     */
    public function withAccess(mixed $method, string $message = ''): static
    {
        if (is_bool($method)) {
            $this->access = $method;
            return $this;
        } else {
            $this->access = true;
            $this->accessMethod = $method;
            $this->accessMessage = $message ?: '权限不足';
        }
        return $this;
    }

    /**
     * 权限检测
     * @param Model $model 要检测的模型
     * @param bool $reset 检测后是否重置
     * @return bool
     */
    protected function checkAccess(Model $model, bool $reset = false): bool
    {
        $allow = true;
        $method = $this->accessMethod;
        if (is_callable($method)) {
            //执行匿名方法
            $allow = call_user_func($method, $model);
        } else if (is_array($method) && !empty($method)) {
            //字段对比
            foreach ($method as $key => $value) {
                if ($model[$key] !== $value) {
                    $allow = false;
                    break;
                }
            }
        } else {
            throw new LogicException('未设置权限检测方法');
        }
        if (!$allow) {
            throw new NoPermissionException($this->accessMessage);
        }

        $reset && $this->access = false; //检测一次后恢复
        return true;
    }
}