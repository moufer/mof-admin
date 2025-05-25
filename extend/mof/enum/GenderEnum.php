<?php

/**
 * Author: moufer <moufer@163.com>
 * Date: 2024/12/30 11:09
 */

namespace mof\enum;

use mof\annotation\Description;
use mof\concern\EnumExtend;

enum GenderEnum: int
{
    use EnumExtend;

    #[Description("男性")]
    case MALE = 1;

    #[Description("女性")]
    case FEMALE = 2;

    #[Description("未知")]
    case UNKNOWN = 0;
}
