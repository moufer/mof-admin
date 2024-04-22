<?php

namespace mof\utils;

class Str
{
    public static function numToHuman($num): string
    {
        $num = (int)$num;
        if ($num < 1000) return $num;
        if ($num < 10000) {
            return round($num / 1000, 1) . 'k+';
        }
        return round($num / 10000, 1) . 'w+';
    }
}