<?php

namespace app\model;

use mof\Model;
use mof\Mof;
use think\model\relation\BelongsTo;

/**
 * 管理员模型
 * Class User
 * @package app\model
 * @property Role $role 关联角色
 * @property bool $is_super_admin 是否超级管理员
 */
class Admin extends Model
{
    protected $hidden = ['password'];

    protected array $searchOption = [
        'id'        => 'integer:pk',
        'username'  => ['type' => 'string', 'op' => 'like'],
        'status'    => 'integer',
        'create_at' => 'time_range',
    ];

    /**
     * 状态
     * @return array[]
     */
    public static function statusOptions(): array
    {
        return [
            ['value' => 1, 'label' => '正常'],
            ['value' => 0, 'label' => '禁用'],
        ];
    }

    /**
     * 角色关联
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * 头像
     * @param $value array|string 图片，格式: [ ['url'=>url,'path' => 'xxx'] ]
     * @return string
     */
    public function setAvatarAttr(array|string $value): string
    {
        if (is_array($value) && isset($value[0]['path'])) {
            return $value[0]['path'];
        } else {
            return '';
        }
    }

    /**
     * 头像
     * @param $value
     * @return array
     */
    public function getAvatarAttr($value): array
    {
        return $value ? [
            [
                'name' => 'avatar',
                'url'  => Mof::storageUrl($value),
                'path' => $value
            ]
        ] : [];
    }

    protected function setRoleIdAttr($value)
    {
        if (is_array($value)) {
            return array_pop($value);
        }
        return is_numeric($value) ? $value : 0;
    }

    /**
     * 密码hash，hash后为60个字符
     * @param $value
     * @return string
     */
    public function setPasswordAttr($value): string
    {
        return $value ? password_hash($value, PASSWORD_BCRYPT) : '';
    }

    /**
     * 是否超级管理员
     * @param $value
     * @param $data
     * @return bool
     */
    public function getIsSuperAdminAttr($value, $data): bool
    {
        return $data['role_id'] === 1;
    }

    /**
     * 搜索器：用户名
     */
    public function searchUsernameAttr($query, $value): void
    {
        $query->whereLike('username', "%{$value}%");
    }

    /**
     * 搜索器：名称
     */
    public function searchNameAttr($query, $value): void
    {
        $query->whereLike('name', "%{$value}%");
    }

}