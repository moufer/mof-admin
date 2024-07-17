<?php
/**
 * Project: MofAdmin
 * Author: moufer <moufer@163.com>
 * Date: 2024/7/17 00:26
 */

namespace app\library\perm;

/**
 * @property string $perm
 */
class PermAction extends Perm
{
    protected array $attrs = [
        'type', 'category', 'module', 'name', 'title', 'hash',
        'perm',
    ];

    protected array $data = [
        'type' => 'action',
    ];

    protected array $titles = [
        'index'   => '列表',
        'create'  => '增加',
        'read'    => '详情',
        'edit'    => '编辑',
        'save'    => '保存',
        'update'  => '更新',
        'delete'  => '删除',
        'form'    => '增改表单',
        'deletes' => '批量删除',
        'updates' => '批量更新',
    ];

    public function __get($name)
    {
        $value = parent::__get($name);
        if ($name === 'title' && empty($value)) {
            $value = $this->titles[$this->name] ?? $this->name;
        }
        return $value;
    }

}