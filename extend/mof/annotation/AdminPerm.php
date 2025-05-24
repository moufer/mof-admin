<?php
/**
 * Project: MofAdmin
 * Author: moufer <moufer@163.com>
 * Date: 2024/7/15 19:26
 */

namespace mof\annotation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class AdminPerm
{
    protected string $controllerClass;

    protected array $permPaths = [];

    public function __construct(
        public string $title,
        public string $url = '',
        public string $actions = '*',
        public int    $sort = 0,
        public string $icon = '',
        public string $group = 'main',
        public string $category = 'system',
        public int    $status = 1
    )
    {
    }

    public function setController(string $class): void
    {
        $this->controllerClass = $class;

        // 先分割命名空间
        $parts = explode('\\', $this->controllerClass);

        // 获取最后一个元素后的所有部分
        $found = false;
        $result = [];

        foreach ($parts as $part) {
            if ($found) {
                $result[] = $part;
            }
            if (strtolower($part) === 'controller') {
                $found = true;
            }
        }

        $this->permPaths = $result;
    }

    public function getPermPath($action, $adminPrefix = ''): string
    {
        $path = $this->permPaths;
        if ($path[0] === $adminPrefix) {
            // 删除
            array_shift($path);
        }
        return implode('/', $path) . '@' . $action;
    }
}