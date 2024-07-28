<?php

namespace mof\enum;

use mof\annotation\Description;
use mof\concern\EnumExtend;

enum YesNoEnum: int
{
    use EnumExtend;

    #[Description('是')]
    case ON = 1;

    #[Description('否')]
    case OFF = 0;
}