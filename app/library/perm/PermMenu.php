<?php
/**
 * Project: MofAdmin
 * Author: moufer <moufer@163.com>
 * Date: 2024/7/17 00:30
 */

namespace app\library\perm;

/**
 * Class PermMenu
 * @package app\library\perm
 * @property string $url
 * @property string $perm
 * @property string $icon
 * @property int $sort
 * @property PermAction[] $children
 */
class PermMenu extends Perm
{
    protected array $attrs = [
        'type', 'category', 'module', 'name', 'title', 'hash',
        'url', 'perm', 'icon', 'sort', 'children', 'group'
    ];

    protected array $data = [
        'type' => 'menu',
        'children' => []
    ];

    public function addAction(PermAction $action): void
    {
        $action->category = $this->category;
        $action->module = $this->module;

        $this->data['children'][] = $action;
    }
}