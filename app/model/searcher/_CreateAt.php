<?php

namespace app\model\searcher;

use think\db\Query;

trait _CreateAt
{
    public function searchCreateAtAttr(Query $query, $value, $data): void
    {
        if (!is_array($value) && str_contains($value, ',')) {
            $value = explode(',', $value);
        }
        if (is_array($value)) {
            $query->whereBetweenTime('create_at', $value[0], $value[1]);
        }
    }
}