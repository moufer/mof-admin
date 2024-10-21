<?php

namespace app\model;

use mof\Model;
use think\model\relation\BelongsTo;

/**
 * 管理员角色权限模型
 */
class RolePerm extends Model
{
    protected $name = 'system_role_perm';

    protected $updateTime = false;

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function perm(): BelongsTo
    {
        return $this->belongsTo(Perm::class, 'perm_id');
    }
}