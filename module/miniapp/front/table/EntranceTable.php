<?php

namespace module\miniapp\front\table;

use mof\front\Table;

class EntranceTable extends Table
{
    protected string $serverBaseUrl  = '/{module}/backend/{id}/entrance';
    protected bool   $tableSelection = false;
    protected bool   $showSearch     = false;
    protected array  $toolbarButtons = ['refresh'];
    protected bool   $showPagination = false;

    protected function init(): void
    {
        $id = app()->request->param('id/d', 0);
        $this->serverBaseUrl = str_replace('{id}', $id, $this->serverBaseUrl);
        parent::init();
    }

    public function columnTitle(): array
    {
        return [
            "prop"  => "title",
            "label" => "名称",
            "width" => "200",
        ];
    }

    public function columnUrl(): array
    {
        return [
            "prop"  => "url",
            "label" => "小程序链接",
            "width" => "*",
            "align" => "left",
        ];
    }
}