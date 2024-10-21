<?php
/**
 * Project: MofAdmin
 * Author: moufer <moufer@163.com>
 * Date: 2024/10/18 22:26
 */

namespace app\front\table;

use app\enum\LoginStatus;
use mof\front\Table;

class AdminLoginLogTable extends Table
{
    protected string $serverBaseUrl  = '/{module}/profile';
    protected bool   $showSearch     = false;
    protected bool   $tableSelection = false;
    protected array  $toolbarButtons = ['refresh'];

    public function operation(): array
    {
        return [
            'show'    => false,
            'buttons' => [],
        ];
    }

    public function columnUserName(): array
    {
        return [
            "prop"   => "username",
            "label"  => "登录名",
            "width"  => 120,
            "align"  => "center",
            "search" => true,
        ];
    }

    public function columnIp(): array
    {
        return [
            "prop"   => "ip",
            "label"  => "IP地址",
            "width"  => 140,
            "search" => true,
        ];
    }

    public function columnBrowser(): array
    {
        return [
            "prop"  => "browser",
            "label" => "设备",
            "width" => '*',
        ];
    }

    public function columnOs(): array
    {
        return [
            "prop"  => "os",
            "label" => "操作系统",
            "width" => 150,
        ];
    }

    public function columnStatus(): array
    {
        return [
            "prop"    => "status",
            "label"   => "登录状态",
            "type"    => "select",
            "options" => LoginStatus::toDict()->toElementData()->toSelectOptions(),
            "width" => 120,
        ];
    }

    public function columnLoginAt(): array
    {
        return [
            "prop"  => "login_at",
            "label" => "登录时间",
            "width" => '*',
        ];
    }
}