<?php

namespace mof;

use think\App;
use think\Collection;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\exception\InvalidArgumentException;
use think\Paginator;

class Logic
{
    protected Model $model;

    public function __construct(
        protected App $app
    )
    {
        $this->injectModel();
        $this->initialize();
    }

    public function initialize()
    {
    }

    /**
     * 获取模型
     * @return Model
     */
    public function model(): Model
    {
        return $this->model;
    }

    /**
     * 分页排序
     * @param Searcher $searcher
     * @param bool $simple
     * @return Paginator
     */
    public function paginate(Searcher $searcher, bool $simple = false): Paginator
    {
        return $searcher->model($this->model)->paginate($simple);
    }

    /**
     * 读取数据
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function read($id): Model
    {
        try {
            $this->model->findOrFail($id);
            return $this->model;
        } catch (ModelNotFoundException) {
            throw new DataNotFoundException('数据不存在');
        }
    }

    /**
     * 添加数据
     * @param array $params
     * @return bool
     */
    public function save(array $params): bool
    {
        return $this->model->data($params, true)->save();
    }

    /**
     * 更新数据
     * @throws DbException
     * @throws DataNotFoundException
     */
    public function update($id, array $params): bool
    {
        if (isset($params[$this->model->getPk()])) {
            unset($params[$this->model->getPk()]);
        }

        return $this->read($id)->data($params, true)->save();
    }

    /**
     * 删除记录
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function delete($id): bool
    {
        return $this->read($id)->delete();
    }

    /**
     * 批量更新
     * @param array $ids
     * @param $field
     * @param $value
     * @return Collection
     * @throws DbException
     */
    public function updates(array $ids, $field, $value): Collection
    {
        if (empty($ids)) {
            throw new InvalidArgumentException('未选择更新项');
        }
        if (empty($field)) {
            throw new InvalidArgumentException('未选择更新字段');
        }
        if (false === $value || is_null($value)) {
            throw new InvalidArgumentException('未设置更新值');
        }

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
    public function deletes(array $ids): Collection
    {
        if (empty($ids)) {
            throw new InvalidArgumentException('未选择删除项');
        }

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
     * 解析#[inject]注解，实例化注入类
     * @return void
     */
    protected function injectModel(): void
    {
        $reflect = new \ReflectionObject($this);
        $properties = $reflect->getProperties(\ReflectionProperty::IS_PROTECTED);
        foreach ($properties as $property) {
            $getAttributes = $property->getAttributes();
            if (count($getAttributes) > 0) {
                if ('mof\annotation\InjectModel' === $getAttributes[0]->getName()) {
                    $propertyName = $property->name;
                    $propertyType = $getAttributes[0]->getArguments()[0];
                    $this->$propertyName = new $propertyType;
                }
            }
        }
    }
}