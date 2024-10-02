<?php
/**
 * Project: AIGC
 * Author: moufer <moufer@163.com>
 * Date: 2024/9/30 14:38
 */

namespace app\library\Interface;

interface TotalInterface
{
    public function totals(): array;

    public function trends(): array;
}