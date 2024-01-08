<?php

namespace mof\utils;

use think\Collection;

/**
 * 饿了么组件数据
 */
class ElementData
{
    protected array $data = [];

    public static function make(Collection|array $data): static
    {
        if ($data instanceof Collection) {
            $data = $data->toArray();
        }
        return new static($data);
    }

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function data(): array
    {
        return $this->data;
    }

    public function map(callable $fn): array
    {
        return array_map($fn, $this->data);
    }

    public function each(callable $fn): void
    {
        foreach ($this->data as $index => $item) {
            $fn($item, $index);
        }
    }

    public function toSelectOptions($labelKey = 'label', $valueKey = 'value', $extra = [])
    {
        static $_data;
        if (!$_data) {
            $_data = array_map(fn($item) => [
                'label' => $item[$labelKey] ?? '',
                'value' => $item[$valueKey] ?? '',
            ], $this->data);
        }
        return $_data;
    }

    public function toTabs($labelKey = 'label', $nameKey = 'name', $extra = [])
    {
        static $_data;
        if (!$_data) {
            $_data = array_map(fn($item) => [
                'label' => $item[$labelKey] ?? '',
                'name'  => $item[$nameKey] ?? '',
            ], $this->data);
        }
        return $_data;
    }

    public function toCascaderOptions($idKey = 'id', $labelKey = 'label')
    {
        static $_data;
        if (!$_data) {
            $_data = $this->generateCascader($this->data, 0, ['id' => $idKey, 'label' => $labelKey]);
        }
        return $_data;
    }

    protected function generateCascader(array $data, int $pid = 0, array $keyAlias = []): array
    {
        $result = [];
        foreach ($data as $item) {
            if ($item['pid'] == $pid) {
                $children = $this->generateCascader($data, (int)$item['id'], $keyAlias);
                $result[] = [
                    'value'    => $item[$keyAlias['id'] ?? 'id'],
                    'label'    => $item[$keyAlias['label'] ?? 'label'],
                    'children' => $children,
                ];
            }
        }
        return $result;
    }
}