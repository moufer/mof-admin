<?php

namespace mof\concern\model;

use think\Model;

/**
 * @mixin Model
 */
trait Searcher
{
    /** @var array 搜索配置 */
    protected array $searchOption = [];

    public function setSearchOption(array $searchOption, $override = true): static
    {
        if ($override) {
            $this->searchOption = $searchOption;
        } else {
            $this->searchOption = array_merge($this->searchOption, $searchOption);
        }
        return $this;
    }

    public function removeSearchOption(string|array $searchOption = null): static
    {
        if (null === $searchOption) {
            $this->searchOption = [];
            return $this;
        }
        if (is_array($searchOption)) {
            $this->searchOption = array_diff_key($this->searchOption, array_flip($searchOption));
        } else if (isset($this->searchOption[$searchOption])) {
            unset($this->searchOption[$searchOption]);
        }
        return $this;
    }

    public function getSearchOption(): array
    {
        return $this->searchOption;
    }
}