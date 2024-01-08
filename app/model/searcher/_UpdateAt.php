<?php

namespace app\model\searcher;

trait _UpdateAt
{
    public function searchCreateAtAttr($query, $value, $data): void
    {
        if (!is_array($value) && str_contains($value, ',')) {
            $value = explode(',', $value);
        }
        if (is_array($value)) {
            $query->whereBetweenTime('update_at', $value[0], $value[1]);
        }
    }
}