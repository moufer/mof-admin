<?php
/**
 * Project: MofAdmin
 * Author: moufer <moufer@163.com>
 * Date: 2024/7/17 00:27
 */

namespace app\library\perm;

use mof\exception\LogicException;

/**
 * @property string $type
 * @property string $category
 * @property string $module
 * @property string $name
 * @property string $title
 * @property string $hash
 */
abstract class Perm implements PermInterface
{
    protected array $attrs = ['type', 'category', 'module', 'name', 'title', 'hash', 'status'];

    protected array $data = [];

    public static function make(array $data): static
    {
        $new = new static();
        foreach ($data as $key => $value) {
            if (in_array($key, $new->attrs)) {
                $new->$key = $value;
            }
        }
        return $new;
    }

    public function __construct()
    {
        foreach ($this->attrs as $attr) {
            if (!isset($this->data[$attr])) {
                $this->$attr = '';
            }
        }
    }

    public function __set($name, $value)
    {
        if (!in_array($name, $this->attrs)) {
            throw new LogicException(sprintf('权限属性 %s 不存在', $name));
        }
        $this->data[$name] = $value;
    }

    public function __get($name)
    {
        return $name === 'hash' ? $this->getHash() : ($this->data[$name] ?? null);
    }

    public function getHash(): string
    {
        $content = [];
        foreach (['type', 'category', 'module', 'name', 'perm'] as $key) {
            $content[] = $this->$key;
        }
        return md5(implode('', $content));
    }

    public function toArray(): array
    {
        $result = [];
        foreach ($this->attrs as $attr) {
            $result[$attr] = $this->$attr;
        }

        return $result;
    }
}