<?php

namespace mof;

use think\db\BaseQuery;
use think\db\Query;
use think\helper\Str;

class Searcher
{
    protected \think\Model $model;

    protected array $order = [];
    protected array $with  = [];

    protected array $params   = [];
    protected int   $pageSize = 10;

    protected ?\Closure $autoCallback = null;

    public function __construct()
    {
    }

    public function model(string|\think\Model $model): static
    {
        if (is_string($model)) {
            $model = new $model();
        }
        $this->model = $model;
        return $this;
    }

    public function with(array $with): static
    {
        $this->with = $with;
        return $this;
    }

    public function order(array $order): static
    {
        $this->order = $order;
        return $this;
    }

    public function pageSize(int $num): static
    {
        $num > 0 && $num < 100 && $this->pageSize = $num;
        return $this;
    }

    public function params(array $params, $override = true): static
    {
        $this->params = $override ? $params : array_merge($this->params, $params);
        return $this;
    }

    public function auto(): static
    {
        if ($this->autoCallback) {
            call_user_func($this->autoCallback, $this);
        }
        return $this;
    }

    public function build(): BaseQuery
    {
        /** @var Query $query */
        $query = $this->model->where(function ($query) {
            //获取允许参与搜索的字段
            $searchFields = method_exists($this->model, 'getSearchFields')
                ? $this->model->getSearchFields() : false;
            if ($searchFields) {
                foreach ($searchFields as $field => $option) {
                    if (!isset($this->params[$field])) continue;        //没有提供这个参数
                    $val = $this->params[$field];                      //参数值
                    $method = 'search' . Str::studly($field) . 'Attr'; //先查找是否存在专有搜索器
                    if (method_exists($this->model, $method)) {
                        $this->model->$method($query, $val, $this->params);
                    } else {
                        //通用搜索器
                        $this->searchAttr($option, $query, $field, $val, $this->params);
                    }
                }
            }
        });

        //关联
        if (!empty($this->with)) {
            $query->with($this->with);
        }

        //排序
        if (!empty($this->order)) {
            $query->order($this->order);
        }

        //字段
        if (!empty($this->fields)) {
            $query->field($this->fields);
        }

        return $query;
    }

    public function select(?int $page = null): \think\Collection
    {
        $query = $this->build();
        $page && $query->page($page, $this->pageSize);
        return $query->select();
    }

    public function paginate($simple = false): \think\Paginator
    {
        $query = $this->build();
        return $query->paginate($this->pageSize, $simple);
    }

    protected function searchAttr(array|string $option,
                                  Query        $query, string $key, mixed $value, array $data): void
    {
        if (is_string($option)) $option = [$option];
        if (empty($option[0])) $option[0] = 'string';
        if (empty($option['op'])) $option['op'] = '=';

        switch ($option[0]) {
            case 'integer':
                if (!is_numeric($value)) return;
                if (empty($value) && empty($option['zero'])) return;
                if ('find_in_set' === $option['op']) {
                    $query->whereFindInSet($key, $value);
                } else {
                    $query->where($key, $option['op'], $value);
                }
                break;
            case 'string':
                if (!is_string($value) && !is_numeric($value)) return;
                if (empty($option['empty']) && empty($value)) return;
                if ('like' === $option['op']) {
                    if (empty($option['like'])) $option['like'] = '%{value}%';
                    $query->whereLike($key, str_replace('{value}', $value, $option['like']));
                } else {
                    $query->where($key, $option['op'], $value);
                }
                break;
            case 'datetime':
                if (empty($value)) return;
                if ('between' === $option['op']) {
                    empty($option['split']) && $option['split'] = ',';
                    if (!is_array($value) && str_contains($value, $option['split'])) {
                        $value = explode($option['split'], $value);
                    }
                    is_array($value) && $query->whereBetweenTime($key, $value[0], $value[1]);
                } else {
                    $query->whereTime($key, $option['op'], $value);
                }
                break;
            case 'array':
                if (!is_array($value) || empty($value)) return;
                $query->whereIn($key, $value);
                break;
        }
    }

    public function setAutoCallback(\Closure $function): static
    {
        $this->autoCallback = $function;
        return $this;
    }
}