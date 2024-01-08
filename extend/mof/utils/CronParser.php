<?php

namespace mof\utils;

/**
 * 解析定时设置时间
 */
class CronParser
{
    /**
     *  解析定时设置时间
     * @param string $cronFormat
     * @return int
     */
    public static function getNextExecutionTime(string $cronFormat): int
    {
        $cronParts = explode(' ', $cronFormat);
        $cronParts = array_pad($cronParts, 5, '*');

        // 解析秒字段
        [$minute, $hour, $dayOfMonth, $month, $dayOfWeek] = $cronParts;

        // 获取当前时间的年份、月份和日期
        $currentYear = date('Y');
        $currentMonth = date('n');
        $currentDay = date('j');

        // 解析分钟和小时字段
        $nextMinute = self::getNextCronValue($minute, 0, 59, date('i'));
        $nextHour = self::getNextCronValue($hour, 0, 23, date('G'));
        // 解析日期和月份字段
        $lastDayOfMonth = date('t', strtotime("$currentYear-$currentMonth-01"));
        $nextDayOfMonth = self::getNextCronValue($dayOfMonth, 1, $lastDayOfMonth, $currentDay);
        $nextMonth = self::getNextCronValue($month, 1, 12, $currentMonth);
        // 解析星期字段
        $nextDayOfWeek = self::getNextCronValue($dayOfWeek, 0, 7, date('w'));

        // 获取下一次执行任务的时间戳
        $nextTime = strtotime("$currentYear-$nextMonth-$nextDayOfMonth $nextHour:$nextMinute");

        // 如果解析到的下一次执行时间小于当前时间，则增加一个周期，重新计算下一次执行时间
        if ($nextTime <= time()) {
            $nextTime = strtotime("+1 $cronFormat", $nextTime);
        }

        // 返回下一次执行任务的时间戳
        return $nextTime;
    }

    /**
     * 获取下一个满足 cron 字段的值
     * @param string $cronValue
     * @param int $min
     * @param int $max
     * @param int $currentValue
     * @return int
     */
    private static function getNextCronValue(string $cronValue, int $min, int $max, int $currentValue): int
    {
        // 解析 cron 字段的值
        $values = self::parseCronValue($cronValue, $min, $max);

        foreach ($values as $value) {
            if ($value >= $currentValue) {
                return $value;
            }
        }

        // 如果没有找到满足条件的值，则取最小值
        return $values[0];
    }

    /**
     * 解析 cron 字段的值
     * @param string $cronValue
     * @param int $min
     * @param int $max
     * @return array
     */
    private static function parseCronValue(string $cronValue, int $min, int $max): array
    {
        $values = [];
        $wildcard = ($cronValue === '*');

        if ($wildcard) {
            // 如果是通配符，返回所有可能的值
            for ($i = $min; $i <= $max; $i++) {
                $values[] = $i;
            }
        } elseif (str_contains($cronValue, ',')) {
            // 如果是逗号分隔的多个值，将它们拆分为单独的值
            $parts = explode(',', $cronValue);
            foreach ($parts as $part) {
                $values[] = intval($part);
            }
        } elseif (str_contains($cronValue, '/')) {
            // 如果是以斜杠分隔的步长值，解析步长和起始值
            [$start, $step] = explode('/', $cronValue);
            $start = intval($start);
            $step = intval($step);

            // 根据步长计算所有可能的值
            for ($i = $start; $i <= $max; $i += $step) {
                $values[] = $i;
            }
        } else {
            // 单个值
            $values[] = intval($cronValue);
        }

        // 确保值在范围内，并去重排序
        $values = array_unique($values);
        sort($values);

        return $values;
    }
}
