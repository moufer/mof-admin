<?php

namespace module\miniapp\logic\admin;

use app\library\Auth;
use app\model\Config;
use module\miniapp\model\AdminRelation;
use module\miniapp\model\MiniApp;
use module\miniapp\model\Package;
use module\miniapp\model\Statistics;
use module\miniapp\model\User;
use mof\annotation\Inject;
use mof\Logic;
use mof\Model;
use mof\Module;
use mof\utils\Arr;
use think\db\exception\DbException;
use think\facade\Db;

class MiniAppLogic extends Logic
{
    #[Inject]
    protected Auth $auth;

    #[Inject(MiniApp::class)]
    protected $model;

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

    public function save($params): Model
    {
        $this->model->startTrans();
        try {
            $model = parent::save($params);
            //触发事件
            event('MiniAppCreate', $model);
            $this->model->commit();
            return $model;
        } catch (\Exception $e) {
            $this->model->rollback();
            throw $e;
        }
    }

    public function delete($id): bool
    {
        $this->model->startTrans();
        try {
            $model = parent::read($id);
            //删除相关数据
            $result = $model->delete();
            $this->deleteAfter($model);
            $this->model->commit();
            return $result;
        } catch (\Exception $e) {
            $this->model->rollback();
            throw $e;
        }
    }

    /**
     * 删除后置操作
     * @param Model $miniapp
     * @return void
     * @throws DbException
     */
    protected function deleteAfter(Model $miniapp): void
    {
        $tables = [
            'admin_relation', 'package', 'user', 'statistics'
        ];

        foreach ($tables as $table) {
            $table = 'miniapp_' . $table;
            Db::name($table)->where('miniapp_id', $miniapp->id)->delete();
        }

        //删除参数配置
        Config::where([
            'module'      => $miniapp->module,
            'extend_id'   => $miniapp->id,
            'extend_type' => 'miniapp'
        ])->delete();

        //触发事件
        event('MiniAppDelete', $miniapp);
    }

}