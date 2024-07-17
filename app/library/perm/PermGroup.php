<?php
/**
 * Project: MofAdmin
 * Author: moufer <moufer@163.com>
 * Date: 2024/7/17 00:31
 */

namespace app\library\perm;

/**
 * Class PermGroup
 * @package app\library\perm
 * @property string $icon
 * @property int $sort
 * @property PermMenu[] $children
 */
class PermGroup extends Perm
{
    protected array $attrs = [
        'type', 'category', 'module', 'name', 'title', 'hash',
        'icon', 'sort', 'children',
    ];

    protected array $data = [
        'type'     => 'group',
        'children' => []
    ];

    public function addMenu(PermMenu $menu): void
    {
        !$menu->category && $menu->category = $this->category;
        !$menu->module && $menu->module = $this->module;

        $this->data['children'][] = $menu;
    }
}