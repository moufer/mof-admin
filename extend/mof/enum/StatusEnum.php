<?php

namespace mof\enum;

use mof\annotation\Description;
use mof\concern\EnumExtend;

enum StatusEnum: int
{
    use EnumExtend;

    #[Description('正常')]
    case NORMAL = 1;

    #[Description('隐藏')]
    case HIDDEN = 0;

}