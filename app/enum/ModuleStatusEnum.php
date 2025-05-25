<?php

/**
 * Author: moufer <moufer@163.com>
 * Date: 2024/12/30 13:06
 */

namespace app\enum;

use mof\annotation\Description;
use mof\concern\EnumExtend;

enum ModuleStatusEnum: int
{
    use EnumExtend;

    #[Description("已停用")]
    case DISABLED = 0;

    #[Description("已启用")]
    case ENABLED = 1;

    #[Description("未安装")]
    case UNINSTALLED = -1;
}
