<?php

namespace module\miniapp\model\searcher;

use app\model\searcher\_CreateAt;
use app\model\searcher\_Id;
use mof\Model;
use think\db\Query;

/**
 * @mixin Model
 */
trait MiniAppSearcher
{
    use _Id, _CreateAt;

    public function searchTitleAttr(Query $query, $value, $data): void
    {
        $value && $query->where('title', 'like', '%' . $value . '%');
    }

    public function searchTypeAttr(Query $query, $value, $data): void
    {
        $value && $value!=='all' && $query->where('type', $value);
    }

    public function searchModuleAttr(Query $query, $value, $data): void
    {
        $value && $query->where('module', $value);
    }

    public function searchMiniappIdsAttr(Query $query, $value, $data): void
    {
        $value && $query->whereIn('id', $value);
    }
}