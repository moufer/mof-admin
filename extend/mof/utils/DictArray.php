<?php

namespace mof\utils;

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

    public function findValue($key)
    {
        return $this->items[$key] ?? null;
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function toElementData(): ElementData
    {
        return ElementData::make($this);
    }
}