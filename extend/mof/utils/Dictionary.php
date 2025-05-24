<?php

/**
 * Author: moufer <moufer@163.com>
 * Date: 2024/12/30 12:14
 */

namespace mof\utils;

use ArrayAccess;
use Countable;
use Iterator;
use JsonSerializable;

class Dictionary implements ArrayAccess, JsonSerializable, Countable, Iterator
{
    private array $data     = [];
    private int   $position = 0;

    public static function make(array $items): static
    {
        $instance = new static();
        foreach ($items as $key => $item) {
            $instance->set($key, $item);
        }
        return $instance;
    }

    // 添加或更新键值对
    public function set(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    // 获取值
    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    // 删除键值对
    public function delete(string $key): void
    {
        unset($this->data[$key]);
    }

    // 获取所有键
    public function keys(): array
    {
        return array_keys($this->data);
    }

    // 获取所有值
    public function values(): array
    {
        return array_values($this->data);
    }

    // 获取字典的大小
    public function size(): int
    {
        return count($this->data);
    }

    // 转换成数组
    public function toArray(): array
    {
        return $this->data;
    }

    // 转换成 label-value 数组
    public function convertLabelValue(): array
    {
        $result = [];
        foreach ($this->data as $k => $v) {
            $result[] = ['label' => $v, 'value' => $k];
        }
        return $result;
    }

    // 实现 ArrayAccess 接口的方法
    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset): void
    {
        $this->delete($offset);
    }

    // 实现 Iterator 接口的方法
    public function rewind(): void
    {
        $this->position = 0;
    }

    public function current(): mixed
    {
        return $this->data[array_keys($this->data)[$this->position]];
    }

    public function key(): string|int
    {
        return array_keys($this->data)[$this->position];
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function valid(): bool
    {
        return isset(array_keys($this->data)[$this->position]);
    }

    // 实现 Countable 接口的方法
    public function count(): int
    {
        return count($this->data);
    }

    // 实现 JsonSerializable 接口的方法
    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
