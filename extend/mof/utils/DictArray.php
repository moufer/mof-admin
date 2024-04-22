<?php

namespace mof\utils;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use think\contract\Arrayable;
use think\contract\Jsonable;
use think\helper\Arr;
use Traversable;

class DictArray
{
    /**
     * @param array $items 数据集数据
     */
    public function __construct(protected array $items = [])
    {
    }

    public static function make(array $items): static
    {
        return new static($items);
    }

    public function convertLabelValue(): array
    {
        $result = [];
        foreach ($this->items as $k => $v) {
            $result[] = [
                'label' => $v,
                'value' => $k,
            ];
        }
        return $result;
    }

    public function toElementData(): ElementData
    {
        return ElementData::make($this->convertLabelValue());
    }
}