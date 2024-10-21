<?php

namespace app\logic;

use app\model\Perm;
use mof\Logic;
use mof\Model;
use think\helper\Str;

class PermLogic extends Logic
{
    /**
     * @var Perm
     */
    protected $model;

    public function save($params): Model
    {
        $params['name'] = $params['name'] ?? Str::random();
        $params['hash'] = $this->createHash($params);
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

    public function createHash($params): string
    {
        foreach (['type', 'category', 'module', 'name', 'perm'] as $key) {
            $content[] = $params[$key];
        }
        $content[] = time(); //自定义添加时，用时间戳替代name字段
        return md5(implode('', $content));
    }
}