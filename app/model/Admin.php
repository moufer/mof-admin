<?php

namespace app\model;

use mof\interface\UserInterface;
use mof\Model;
use mof\Mof;
use think\db\exception\DbException;
use think\facade\Event;
use think\model\relation\BelongsTo;

/**
 * 管理员模型
 * Class User
 * @package app\model
 * @property Role $role 关联角色
 * @property bool $is_super_admin 是否超级管理员
 */
class Admin extends Model implements UserInterface
{
    protected array $searchFields = [
        'id'        => 'integer',
        'module'    => 'string',
        'username'  => ['type' => 'string', 'op' => 'like'],
        'status'    => ['integer', 'zero' => true],
        'create_at' => ['datetime', 'op' => 'between'],
    ];

    protected $hidden = ['password'];

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

    public function getId(): int
    {
        return $this->getAttr('id');
    }

    public function getNickName(): string
    {
        return $this->getAttr('name');
    }

    public function getAvatar(): string
    {
        $avatar = $this->getAttr('avatar');
        if (is_array($avatar)) {
            $avatar = $avatar['url'];
        }
        return $avatar;
    }

    /**
     * 角色关联
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * 获取权限数组
     * @param string $module
     * @return array
     * @throws DbException
     */
    public function getPerms(string $module = 'admin'): array
    {
        if ($module === 'admin') {
            $perms = $this->role->getPerms($module);
        } else {
            //其他模块通过事件来获取
            $perms = Event::until('GetPerms', [$this, $module]);
        }
        return $perms;
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

    /**
     * 角色ID修改器
     * @param $value
     * @return float|int|mixed|string|null
     */
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