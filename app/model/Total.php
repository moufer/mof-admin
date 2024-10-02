<?php
/**
 * Project: AIGC
 * Author: moufer <moufer@163.com>
 * Date: 2024/9/29 22:11
 */

namespace app\model;

use mof\Model;

/**
 * 统计模型
 * @property string $day 日期
 * @property string $module 模块名称
 * @property string $name 统计名称
 * @property int $count 数据量
 */
class Total extends Model
{
    protected $name = 'system_total';

}