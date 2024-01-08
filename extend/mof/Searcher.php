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
        $num > 0 && $this->pageSize = $num;
        return $this;
    }

    public function params(array $params): static
    {
        $this->params = $params;
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
            foreach ($this->params as $key => $val) {
                $method = 'search' . Str::studly($key) . 'Attr';
                $searchOption = method_exists($this->model, 'getSearchOption')
                    ? $this->model->getSearchOption() : false;
                if (method_exists($this->model, $method)) {
                    $this->model->$method($query, $val, $this->params);
                } elseif ($searchOption && isset($searchOption[$key])) {
                    $this->searchAttr($searchOption[$key], $query, $key, $val, $this->params);
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

    protected function searchAttr(array|string $option, Query $query, string $key, mixed $value, array $data): void
    {
        if (is_string($option)) $option = ['type' => $option];
        if (empty($option['type'])) $option['type'] = 'string';
        if (empty($option['op'])) $option['op'] = '=';

        switch ($option['type']) {
            case 'integer':
            case 'integer:pk':
                if (!is_numeric($value)) return;
                if (!empty($option['min']) && $value < $option['min']) return;
                if (!empty($option['max']) && $value > $option['max']) return;
                if ('integer:pk' === $option['type'] && empty($value)) return;
                $query->where($key, $option['op'], $value);
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
            case 'find_in_set':
                if (empty($value)) return;
                $query->whereFindInSet($key, $value);
                break;
            case 'time':
                if (empty($value)) return;
                $query->whereTime($key, $option['op'], $value);
                break;
            case 'time_range':
                if (empty($value)) return;
                empty($option['split']) && $option['split'] = ',';
                if (!is_array($value) && str_contains($value, $option['split'])) {
                    $value = explode($option['split'], $value);
                }
                is_array($value) && $query->whereBetweenTime($key, $value[0], $value[1]);
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