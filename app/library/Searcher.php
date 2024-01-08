<?php

namespace app\library;

use mof\Model;
use think\db\BaseQuery;
use think\db\Query;
use think\helper\Str;

class Searcher
{
    protected Model $model;
    protected Query $query;

    protected array $params;

    protected array $fields;
    protected array $order;
    protected int   $pageSize = 10;
    protected array $with     = [];

    public static function make(\think\Request $request): static
    {
        $params = $request->get('params/a', []);
        $order = $request->get('order/a', []);
        $pageSize = $request->get('page_size/d', 10);

        $instance = new static();
        $instance->params($params);
        $instance->order($order);
        $instance->pageSize($pageSize);

        return $instance;
    }

    public function model(string $abstract): static
    {
        $this->model = new $abstract();
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
        $this->pageSize = $num;
        return $this;
    }

    public function params(array $params): static
    {
        $this->params = $params;
        return $this;
    }

    public function build(): BaseQuery
    {
        /** @var Query $query */
        $query = $this->model->where(function ($query) {
            foreach ($this->params as $key => $value) {
                $searchName = 'search' . Str::studly($key) . 'Attr';
                if (method_exists($this->model, $searchName)) {
                    call_user_func([$this->model, $searchName], $query, $value, $this->params);
                }
            }
        });

        //关联
        if (!empty($this->with)) {
            $query->with($this->with);
        }

        //排序
        if (!empty($this->order)) {
            $query->order([$this->order['field'] => $this->order['order'] ?? 'asc']);
        }

        //字段
        if (!empty($this->fields)) {
            $query->field($this->fields);
        }

        return $query;
    }

    public function search($simple = false): \think\Paginator
    {
        $query = $this->build();
        //查询
        return $query->paginate($this->pageSize, $simple);
    }

    public function select($page = false): \think\Collection|array
    {
        $query = $this->build();
        if (is_numeric($page)) {
            $query->page($page, $this->pageSize);
        }
        return $query->select();
    }

}