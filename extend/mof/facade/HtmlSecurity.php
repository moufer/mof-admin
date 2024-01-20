<?php

namespace mof\facade;

/**
 * @see \mof\utils\HtmlSecurity
 * @package mof\facade
 * @mixin \mof\utils\HtmlSecurity
 * @method static xss_clean($str, $is_image = false) 过滤xss
 */
class HtmlSecurity extends \think\Facade
{
    protected static function getFacadeClass(): string
    {
        return \mof\utils\HtmlSecurity::class;
    }
}