<?php

namespace app\library;

use mof\exception\LogicException;
use mof\Searcher;
use think\exception\InvalidArgumentException;
use think\exception\ValidateException;

class Request extends \mof\Request
{
    /**
     * 获取搜索器
     * @param array $defaultParams 默认参数
     * @param string $type 搜索器类型
     * @return Searcher
     */
    public function searcher(array $defaultParams = [], string $type = 'table'): Searcher
    {
        $data = match ($type) {
            'table' => $this->tableSearcherData(),
            'selectSearch' => $this->selectSearchSearcherData(),
            default => throw new InvalidArgumentException('Invalid searcher type')
        };
        return (new Searcher())
            ->params(array_merge($defaultParams, $data['params']))
            ->pageSize($data['pageSize'])
            ->order($data['order']);
    }

    /**
     * 获取搜索选项
     * @return array
     */
    protected function tableSearcherData(): array
    {
        $data = [];
        $data['params'] = $this->get('params/a', []);
        $data['pageSize'] = $this->get('page_size/d', 10);
        $data['order'] = [];
        $orderRaw = $this->get('order/a', []);
        if (!empty($orderRaw['field'])) {
            $data['order'] = ([$orderRaw['field'] => $orderRaw['sort'] ?? 'asc']);
        }
        return $data;
    }

    /**
     * 获取selectSearch组件所需的选项
     * @return array
     */
    protected function selectSearchSearcherData(): array
    {
        //查询参数
        $result = $params = [];
        if ($this->has('keyField') && $this->has('keyValue')) {
            $params[$this->param('keyField')] = $this->param('keyValue');
        }
        if ($this->has('searchField') && $this->has('searchValue')) {
            $params[$this->param('searchField')] = $this->param('searchValue');
        }
        //自定义参数
        if ($this->has('custom')) {
            $custom = $this->param('custom/a');
            $params = array_merge($params, $custom);
        }
        $result['params'] = $params;

        //排序
        if ($this->has('orderBy')) {
            $orderBy = $this->param('orderBy/a');
            $result['order'] = [$orderBy['field'] => $orderBy['sort']];
        }

        //字段值
        $result['pageSize'] = max(1, $this->param('pageSize/d', 50));

        return $result;
    }

    /**
     * 获取提交的id集合
     * @param string $name
     * @return array
     */
    public function getPostIds(string $name = 'id'): array
    {
        if (!$ids = $this->post($name . '/a', [])) {
            throw new LogicException('缺少ID参数');
        }
        return $ids;
    }

    /**
     * 获取批量更新的参数
     * @param string $name 主键的键名
     * @param string $fieldName 更新字段的键名
     * @param string $valueName 更新值的键名
     * @return array
     */
    public function updatesData(string $name = 'ids', string $fieldName = 'field', string $valueName = 'value'): array
    {
        $ids = $this->param("{$name}/a");
        $field = $this->param($fieldName);
        $value = $this->param($valueName);

        if (empty($ids)) {
            throw new ValidateException('未选择更新项');
        }
        if (empty($field)) {
            throw new ValidateException('未选择更新字段');
        }
        if (false === $value || is_null($value)) {
            throw new ValidateException('未设置更新值');
        }

        return [
            'ids'   => $ids,
            'field' => $field,
            'value' => $value
        ];
    }

}
