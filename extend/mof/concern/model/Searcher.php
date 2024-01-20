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

    public function setSearchFields(array $searchFields, $override = true): static
    {
        if ($override) {
            $this->searchFields = $searchFields;
        } else {
            $this->searchFields = array_merge($this->searchFields, $searchFields);
        }
        return $this;
    }

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

    public function getSearchFields(): array
    {
        return $this->searchFields;
    }
}