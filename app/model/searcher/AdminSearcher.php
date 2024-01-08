<?php

namespace app\model\searcher;

use mof\Model;
use think\db\Query;

/**
 * @mixin Model
 */
trait AdminSearcher
{
    use _Id, _CreateAt, _Status;

    protected array $searchOption = [
        'id'        => 'integer:id',
        'status'    => 'integer',
        'create_at' => 'time_range',
    ];


    public function searchUsernameAttr(Query $query, $value, $data): void
    {
        $value && $query->whereLike('username', "%{$value}%");
    }

}