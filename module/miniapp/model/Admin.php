<?php

namespace module\miniapp\model;

use think\model\relation\HasMany;

/**
 * 小程序平台管理员
 * @package module\miniapp\model
 * @property AdminRelation[] $miniapps
 */
class Admin extends \app\model\Admin
{
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime         = 'create_at';

    public function miniapps(): HasMany
    {
        return $this->hasMany(AdminRelation::class, 'admin_id', 'id');
    }

    public function getMiniappIdsAttr($value): array
    {
        $result = [];
        $this->miniapps->each(function ($miniapp) use (&$result) {
            $result[] = $miniapp->miniapp_id;
        });
        return $result;
    }
}