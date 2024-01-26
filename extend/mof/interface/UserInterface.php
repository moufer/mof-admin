<?php

namespace mof\interface;

interface UserInterface
{
    public function getId(): int;

    public function getNickName(): string;

    public function getAvatar(): string;
}