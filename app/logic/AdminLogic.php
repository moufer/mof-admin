<?php

namespace app\logic;

use app\model\Admin;
use mof\Logic;
use mof\Model;

class AdminLogic extends Logic
{
    /**
     * @var Admin 模型
     */
    protected $model;

    public function update($id, $params): Model
    {
        //空密码表示不修改密码字段
        if (isset($params['password']) && trim($params['password']) === '') {
            unset($params['password']);
        }
        return parent::update($id, $params);
    }
}