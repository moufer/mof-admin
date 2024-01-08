<?php

namespace app\model\searcher;

use think\db\Query;

trait _Status
{
    public function searchStatusAttr(Query $query, $value, $data): void
    {
        is_numeric($value) && $query->where('status', $value);
    }
}