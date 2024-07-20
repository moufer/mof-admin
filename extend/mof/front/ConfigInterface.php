<?php
/**
 * Project: AIGC
 * Author: moufer <moufer@163.com>
 * Date: 2024/7/19 20:59
 */

namespace mof\front;

interface ConfigInterface
{
    public function build(array $values = [], bool|string $withGroup = true): array;

    public function getGroups(): array;
}