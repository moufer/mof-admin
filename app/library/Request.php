<?php

namespace app\library;

use mof\Searcher;
use think\exception\InvalidArgumentException;
use think\exception\ValidateException;

class Request extends \mof\Request
{
    /**
     * 获取搜索器
     * @param array $defaultParams
     * @return Searcher
     */
    public function searcher(array $defaultParams = []): Searcher
    {
        $data = $this->searcherData();
        return (new Searcher())
            ->params(array_merge($defaultParams, $data['params']))
            ->pageSize($data['pageSize'])
            ->order($data['order']);
    }

    /**
     * 获取搜索选项
     * @return array
     */
    public function searcherData(): array
    {
        $data = [];
        $data['params'] = $this->get('params/a', []);
        $data['pageSize'] = $this->get('page_size/d', 10);
        $data['order'] = [];
        $orderRaw = $this->get('order/a', []);
        if (!empty($orderRaw['field'])) {
            $data['order'] = ([$orderRaw['field'] => $orderRaw['order'] ?? 'asc']);
        }
        return $data;
    }

    /**
     * 获取提交的id集合
     * @param string $name
     * @return array
     */
    public function getPostIds(string $name = 'ids'): array
    {
        if (!$ids = $this->post($name . '/a', [])) {
            throw new InvalidArgumentException('缺少ID参数');
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
