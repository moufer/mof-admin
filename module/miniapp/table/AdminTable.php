<?php

namespace module\miniapp\table;

use app\table\UserTable;
use module\miniapp\model\MiniApp;
use mof\utils\ElementData;

class AdminTable extends UserTable
{
    public function columnRoleId(): array
    {
        return [];
    }

    public function columnMiniappIds(): array
    {
        $rows = MiniApp::order('id', 'asc')->select()->toArray();
        $selectOptions = ElementData::make($rows)->toSelectOptions('title', 'id');

        return [
            "order"     => 7,
            "prop"      => "miniapp_ids",
            "propAlias" => "miniapp_ids",
            "label"     => "关联小程序",
            "type"      => "select",
            "options"   => $selectOptions,
            "search"    => [
                'type'      => 'select',
                'clearable' => true
            ],
            "form"      => [
                "type" => "select",
                'multiple' => true
            ]
        ];
    }


}