<?php

namespace module\miniapp\model;

use mof\interface\UserInterface;
use mof\Model;

class User extends Model implements UserInterface
{
    protected $table = 'miniapp_user';

    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_at';
    protected $updateTime = 'update_at';

    public function getUserType(): string
    {
        return 'miniapp';
    }

    public function getId(): int
    {
        return $this->getAttr('id');
    }

    public function getNickName(): string
    {
        return $this->getAttr('nickname');
    }

    public function getAvatar(): string
    {
        return $this->getAttr('avatar');
    }
}