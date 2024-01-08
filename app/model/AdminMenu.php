<?php

namespace app\model;

use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Model;

/**
 * 后台菜单模型
 * @property array $actionList 行为列表
 */
class AdminMenu extends \mof\Model
{
    protected array $actionOptions = [
        'index'  => '列表',
        'add'    => '添加',
        'edit'   => '编辑',
        'delete' => '删除',
        'submit' => '提交',
    ];

    public static function onAfterInsert(Model $model): void
    {
        $data = $model->getData();
        //如果没有设置排序，则默认为id
        if (empty($data['sort'] ?? 0)) {
            $model->save(['sort' => $model->id]);
        }
    }

    public static function onAfterUpdate(Model $model): void
    {
        //获取已修改的字段
        $data = $model->getChangedData();
        //如果actions发生变化，则更新角色菜单
        if (isset($data['actions'])) {
            $roleMenus = AdminRoleMenu::where('menu_id', $model->id)->select();
            foreach ($roleMenus as $roleMenu) {
                $actions = array_keys($model->actionList);
                $roleMenu->diffActions($actions);
            }
        }
    }

    public static function onAfterDelete(Model $model): void
    {
        //删除角色菜单
        AdminRoleMenu::where('menu_id', $model->id)->delete();
    }

    /**
     * 添加菜单
     * @param array $options
     * @return AdminMenu|Model
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function addMenu(array $options): AdminMenu|Model
    {
        //查找上级菜单
        $parent = false;
        if ($options['parent']) {
            $parent = self::where('name', $options['parent'])->find();
        }
        //添加菜单
        $path = explode('/', $options['name']);
        $data = [
            'parent_id' => $parent ? $parent->id : 0, //上级菜单
            'type'      => $options['type'] ?: 'link', //类型
            'name'      => $options['name'], //名称
            'title'     => $options['title'] ?: $path[count($path) - 1], //标题
            'actions'   => $options['action'], //行为
            'icon'      => '', //图标
            'sort'      => 0, //排序
            'status'    => 1, //状态
        ];
        $data['type'] == 'folder' && $data['actions'] = '';
        return self::create($data);
    }

    /**
     * 行为列表
     * @param $value
     * @param $data
     * @return array
     */
    protected function getActionListAttr($value, $data): array
    {
        $result = [];
        if (!$data['actions']) return $result;
        $actions = explode(',', $data['actions']);
        foreach ($actions as $key => $val) {
            $_key = is_numeric($key) ? $val : $key;
            $_opt = is_numeric($key) ? ($this->actionOptions[$val] ?? $val) : $val;
            $result[$_key] = $_opt;
        }
        return $result;
    }

}