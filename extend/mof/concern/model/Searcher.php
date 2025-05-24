<?php

namespace mof\concern\model;

use think\Model;

/**
 * @mixin Model
 */
trait Searcher
{
    /** @var array 搜索配置 */
    protected array $searchFields = [];
    /** @var array 排序配置 */
    protected array $searchSort = [];

    /**
     * 设置搜索字段
     * @param array $searchFields
     * @param bool $override 是否覆盖原有配置
     * @return static
     */
    public function setSearchFields(array $searchFields, bool $override = true): static
    {
        if ($override) {
            $this->searchFields = $searchFields;
        } else {
            $this->searchFields = array_merge($this->searchFields, $searchFields);
        }
        return $this;
    }

    /**
     * 删除搜索字段
     * @param string|array|null $searchFields
     * @return $this
     */
    public function removeSearchField(string|array $searchFields = null): static
    {
        if (null === $searchFields) {
            $this->searchFields = [];
            return $this;
        }
        if (is_array($searchFields)) {
            $this->searchFields = array_diff_key($this->searchFields, array_flip($searchFields));
        } else if (isset($this->searchFields[$searchFields])) {
            unset($this->searchFields[$searchFields]);
        }
        return $this;
    }

    /**
     * 获取搜索字段
     * @return array
     */
    public function getSearchFields(): array
    {
        return $this->searchFields;
    }

    /**
     * 获取排序配置
     * @return array
     */
    public function getSearchSort(): array
    {
        return $this->searchSort;
    }

    /**
     * 生成模型数据搜索器
     * @param array $params
     * @return \mof\Searcher
     */
    public static function createSearcher(array $params): \mof\Searcher
    {
        $model = new static();
        $searcher = new \mof\Searcher();
        $searcher->model($model)->params($params)->order($model->searchSort);
        return $searcher;
    }
}