<?php
/**
 * Project: MofAdmin
 * Author: moufer <moufer@163.com>
 * Date: 2024/7/26 23:05
 */

namespace mof\enum;

use mof\annotation\Description;
use mof\concern\EnumExtend;

enum EnableEnum: int
{
    use EnumExtend;

    #[Description('启用')]
    case ENABLED = 1;

    #[Description('禁用')]
    case DISABLED = 0;
}
