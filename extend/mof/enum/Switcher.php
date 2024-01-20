<?php

namespace mof\enum;

use mof\annotation\Description;

enum Switcher: int
{
    #[Description('开启')]
    case ON = 1;
    #[Description('关闭')]
    case OFF = 0;
}