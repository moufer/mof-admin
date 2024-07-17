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
    public function __construct(
        public string $title,
        public string $url = '',
        public string $actions = '*',
        public int    $sort = 0,
        public string $icon = '',
        public string $group = 'main',
        public string $category = 'system'
    )
    {
    }
}