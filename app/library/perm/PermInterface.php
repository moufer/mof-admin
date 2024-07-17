<?php
/**
 * Project: MofAdmin
 * Author: moufer <moufer@163.com>
 * Date: 2024/7/17 00:24
 */

namespace app\library\perm;

interface PermInterface
{
    public function getHash(): string;

    public function toArray(): array;
}