<?php

namespace module\miniapp\front\table;

use app\front\table\UserTable;
use module\miniapp\model\MiniApp;
use mof\utils\ElementData;

class AdminTable extends UserTable
{
    protected string $serverBaseUrl = '/{module}/backend/{table}';

    public function columnRoleId(): array
    {
        return [];
    }

    public function columnMiniappIds(): array
    {
        $rows = MiniApp::order('id', 'asc')->select();
        return [
            "order"     => 7,
            "prop"      => "miniapp_ids",
            "label"     => "关联小程序",
            "type"      => "tag",
            "options"   => ElementData::make($rows)->toSelectOptions('title', 'id'),
            "search"    => [
                'type'      => 'select',
            ],
            "width"     => '*'
        ];
    }


}