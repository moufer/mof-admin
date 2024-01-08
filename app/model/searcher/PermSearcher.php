<?php

namespace app\model\searcher;

use think\db\Query;

trait PermSearcher
{

    use _Id, _status, _CreateAt;

    public function searchTitleAttr(Query $query, $value, $data): void
    {
        $value && $query->where('title', $value);
    }

    public function searchCategoryAttr(Query $query, $value, $data): void
    {
        $value && $query->where('category', $value);
    }

    public function searchModuleAttr(Query $query, $value, $data): void
    {
        $value && $query->where('module', $value);
    }

    public function searchTypeAttr(Query $query, $value, $data): void
    {
        $value && $query->where('type', $value);
    }
}