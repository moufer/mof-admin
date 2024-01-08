<?php

namespace app\logic;

use mof\Logic;

class AdminLogic extends Logic
{
    public function update($id, array $params): bool
    {
        //空密码表示不修改密码字段
        if (isset($params['password']) && trim($params['password']) === '') {
            unset($params['password']);
        }
        parent::update($id, $params);
    }
}