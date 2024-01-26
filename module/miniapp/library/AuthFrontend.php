<?php

namespace module\miniapp\library;

use app\library\Auth;

class AuthFrontend extends Auth
{
    protected string $aud = 'miniapp';
}