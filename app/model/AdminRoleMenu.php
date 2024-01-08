<?php

namespace app\model;

use think\model\relation\BelongsTo;

/**
 * @property array $actions
 * @property AdminMenu $menu
 */
class AdminRoleMenu extends \mof\Model
{
    /**
     * 检测行为是否有变化
     * @param array $actions
     * @return bool
     */
    public function diffActions(array $actions): bool
    {
        $roleMenuActions = $this->getAttr('actions');
        //找出 $roleMenuActions 里有，但是 $actions 里没有的行为
        $diff = array_diff($roleMenuActions, $actions);
        //如果有差异，则从 $roleMenuActions 里删除 $diff 里的行为
        if ($diff) {
            //删除差异行为
            $newRoleMenuActions = array_diff($roleMenuActions, $diff);
            //更新角色菜单
            $this->save(['actions' => $newRoleMenuActions]);
        }
        return true;
    }

    /**
     * 修改器 - 行为列表
     * @param $value
     * @return string
     */
    protected function setActionsAttr($value): string
    {
        return is_array($value) ? implode(',', $value) : trim($value);
    }

    /**
     * 获取器 - 行为列表
     * @param $value
     * @return array
     */
    protected function getActionsAttr($value): array
    {
        return $value ? explode(',', $value) : [];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(AdminMenu::class, 'menu_id');
    }
}