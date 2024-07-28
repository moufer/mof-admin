<?php

namespace mof\enum;

use mof\annotation\Description;
use mof\concern\EnumExtend;

enum SwitchEnum: int
{
    use EnumExtend;

    #[Description('开启')]
    case YES = 1;

    #[Description('关闭')]
    case NO = 0;
}