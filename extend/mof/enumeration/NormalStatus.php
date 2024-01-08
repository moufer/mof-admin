<?php

namespace mof\enumeration;

use mof\annotation\Description;
use mof\concern\EnumExtend;

enum NormalStatus: int
{
    use EnumExtend;

    #[Description('正常')]
    case NORMAL = 1;
    #[Description('隐藏')]
    case HIDDEN = 0;

    public function label(): string
    {
        return $this->getDescription($this->name);
    }
}