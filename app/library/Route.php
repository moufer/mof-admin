<?php

namespace app\library;

class Route extends \think\Route
{
    /**
     * REST定义
     * @var array
     */
    protected $rest = [
        'index'  => ['get', '', 'index'], // 列表
        'create' => ['get', '/create', 'create'],

        'updates' => ['put', '/updates', 'updates'], // 批量更新
        'deletes' => ['post', '/deletes', 'deletes'], // 批量删除

        'edit'   => ['get', '/<id>/edit', 'edit'],
        'read'   => ['get', '/<id>', 'read'], // 详情
        'save'   => ['post', '', 'save'], // 添加
        'update' => ['put', '/<id>', 'update'], // 更新
        'delete' => ['delete', '/<id>', 'delete'], // 删除
    ];
}