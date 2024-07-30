<?php

namespace app\front\table;

use mof\front\Table;

class StorageTable extends Table
{
    protected array $toolbarButtons = ['refresh', 'delete', 'search'];

    public function operation(): array
    {
        $result = parent::operation();
        $result['width'] = 80;
        $result['buttons'] = ['delete'];
        return $result;
    }

    public function columnId(): array
    {
        return [
            "prop"   => 'id',
            "label"  => 'ID',
        ];
    }

    public function columnUrl(): array
    {
        return [
            "prop"            => "url",
            "label"           => "预览",
            "type"            => "media", // video image audio document
            "media_type_prop" => "file_type", // 文件类型字段
        ];
    }

    public function columnTitle(): array
    {
        return [
            "prop"  => "title",
            "label" => "文件名",
            "width" => "*",
            "align" => "left"
        ];
    }

    public function columnSize(): array
    {
        return [
            "prop"  => "size",
            "label" => "文件大小",
        ];
    }

    public function columnWidth(): array
    {
        return [
            "prop"  => "width",
            "label" => "宽度",
        ];
    }

    public function columnHeight(): array
    {
        return [
            "prop"  => "height",
            "label" => "高度",
        ];
    }

    public function columnProvider(): array
    {
        return [
            "prop"  => "provider",
            "label" => "存储方式",
        ];
    }

    public function columnMime(): array
    {
        return [
            "prop"   => "mime",
            "label"  => "文件类型",
            "width"  => 120,
            "search" => true,
        ];
    }

    public function columnCreateAt(): array
    {
        return [
            "prop"   => "create_at",
            "label"  => "上传时间",
            "type"   => "datetime",
            "search" => [
                'type' => 'datetimerange',
            ],
        ];
    }
}