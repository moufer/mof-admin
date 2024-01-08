<?php

namespace app\model\searcher;

use think\db\Query;

trait _Id
{
    public function searchIdAttr(Query $query, $value, $data): void
    {
        $value && $query->where('id', $value);
    }
}