<?php

namespace module\miniapp\logic;

use app\library\Auth;
use mof\annotation\Inject;
use mof\Logic;
use mof\Model;
use mof\utils\Arr;

class MiniAppLogic extends Logic
{
    #[Inject]
    protected Auth $auth;

    public function read($id): Model
    {
        is_array($id) && [$id, $append] = $id;
        $model = parent::read($id);
        //返回附加内容
        if (!empty($append)) {
            $append = explode(',', $append);
            if (in_array('module', $append)) {
                $model->append(['module_info'], true);
            }
            //获取perm数据
            if (in_array('perms', $append)) {
                $model->setAttr('perms',
                    Arr::tree($this->auth->getUser()->role->getPerms('miniapp', [
                        'miniapp', $model->getData('module')
                    ]))
                );
            }
        }
        return $model;
    }

    public function delete($id): bool
    {
        $model = parent::read($id);

        //触发事件
        event('MiniAppDeleteAfter', $model);

        return $model->delete();
    }

}