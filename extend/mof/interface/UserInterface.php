<?php

namespace mof\interface;

use mof\Model;

/**
 * 用户接口
 * @package mof\interface
 * @mixin Model
 */
interface UserInterface
{
    public function getUserType(): string;

    public function getId(): int;

    public function getNickName(): string;

    public function getAvatar(): string;

}