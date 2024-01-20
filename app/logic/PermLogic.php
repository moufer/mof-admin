<?php

namespace app\logic;

use app\model\Perm;
use mof\Logic;
use mof\Model;


class PermLogic extends Logic
{
    /**
     * @var Perm
     */
    protected $model;

    public function save($params): Model
    {
        $model = parent::save($params);
        //保存后，如果是菜单，则把actions数组插入到Perm表中
        $actions = $model->actions;
        if ('menu' === $model->getAttr('type') && is_array($actions) && count($actions) > 0) {
            //如果是菜单，且actions数组有值，则遍历把action数组，插入到Perm表中
            foreach ($actions as $action) {
                $model->createAction($action);
            }
        }
        return $model;
    }
}