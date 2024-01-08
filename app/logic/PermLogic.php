<?php

namespace app\logic;

use mof\Logic;

class PermLogic extends Logic
{
    public function save(array $params): bool
    {
        parent::save($params);
        //保存后，如果是菜单，则把actions数组插入到Perm表中
        $actions = $this->model->actions;
        if ('menu' === $this->model->getAttr('type') && is_array($actions) && count($actions) > 0) {
            //如果是菜单，且actions数组有值，则遍历把action数组，插入到Perm表中
            foreach ($actions as $action) {
                $this->model->createAction($action);
            }
        }
        return true;
    }
}