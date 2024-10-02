<?php

namespace mof;

use think\db\BaseQuery;
use think\db\Query;
use think\helper\Str;
use think\Paginator;

class Searcher
{
    protected \think\Model $model;

    protected array $order        = [];
    protected array $with         = [];
    protected array $field        = [];
    protected bool  $fieldWithout = false;
    protected array $appends      = [];

    protected array $params     = [];
    protected int   $pageSize   = 10;
    protected int   $limitTotal = 0;

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

    public function appends(array $attrs)
    {
        $this->appends = $attrs;
        return $this;
    }

    public function with(array $with): static
    {
        $this->with = $with;
        return $this;
    }

    public function field(array $field, $without = false): static
    {
        $this->field = $field;
        $this->fieldWithout = $without;
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

    public function limitTotal(int $num): static
    {
        $num >= 0 && $this->limitTotal = $num;
        return $this;
    }

    public function params(array $params, $override = true): static
    {
        $this->params = $override ? $params : array_merge($this->params, $params);
        return $this;
    }

    public function getParams(): array
    {
        return $this->params;
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
        //关联
        $query = null;
        if (!empty($this->with)) {
            $query = $this->model->with($this->with);
        }

        /** @var Query $query */
        $query = ($query ?: $this->model)->where(function ($query) {
            //获取允许参与搜索的字段
            $searchFields = method_exists($this->model, 'getSearchFields')
                ? $this->model->getSearchFields() : false;
            if ($searchFields) {
                foreach ($searchFields as $field => $option) {
                    if (!isset($this->params[$field])) continue;        //没有提供这个参数
                    $val = $this->params[$field];                       //参数值
                    $method = 'search' . Str::studly($field) . 'Attr';  //先查找是否存在专有搜索器
                    if (method_exists($this->model, $method)) {
                        $this->model->$method($query, $val, $this->params);
                    } else {
                        //通用搜索器
                        $this->searchAttr($option, $query, $field, $val, $this->params);
                    }
                }
            }
        });

        //排序
        if (!empty($this->order)) {
            if ($this->order === ['rand']) {
                $query->orderRaw('rand()');
            } else {
                $query->order($this->order);
            }
        }

        //字段
        if (!empty($this->field)) {
            $fieldFunc = $this->fieldWithout ? 'withoutField' : 'field';
            $query->$fieldFunc($this->field);
        }

        return $query;
    }

    public function select(?int $page = null): \think\Collection
    {
        $query = $this->build();
        $page && $query->page($page, $this->pageSize);
        $select = $query->select();
        if ($this->appends && $select->count()) {
            $select->append($this->appends);
        }
        return $select;
    }

    public function paginate($simple = false): \think\Paginator
    {
        //检测是否设置了最大页吗
        if ($this->limitTotal > 0) {
            $lastPage = ceil($this->limitTotal / $this->pageSize);
            //获取当前页页码
            $page = (int)app()->request->get('page', 1);
            if ($page > $lastPage) {
                $results = new \think\Collection([]);
                $total = $this->limitTotal;
                //返回空记录
                $paginate = Paginator::make($results, $this->pageSize, $page, $total, $simple);
            }
        }

        if (empty($paginate)) {
            $query = $this->build();
            $paginate = $query->paginate($this->pageSize, $simple);
        }

        if ($this->appends && $paginate->count()) {
            $paginate->getCollection()->append($this->appends);
        }
        return $paginate;
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
            case 'boolean':
                $query->where($key, '=', empty($value) ? 0 : 1);
                break;
        }
    }

    public function setAutoCallback(\Closure $function): static
    {
        $this->autoCallback = $function;
        return $this;
    }
}