<?php

namespace module\miniapp\logic\admin;

use module\miniapp\model\Admin;
use module\miniapp\model\AdminRelation;
use module\miniapp\model\MiniApp;
use mof\annotation\Inject;
use mof\Model;
use mof\Searcher;
use think\Paginator;

class AdminLogic extends \app\logic\AdminLogic
{
    #[Inject(Admin::class)]
    protected $model;

    public function paginate(Searcher $searcher, bool $simple = false): Paginator
    {
        return $searcher->model($this->model)
            ->with(['miniapps'])
            ->paginate($simple)
            ->append(['miniapp_ids']);
    }

    public function read($id): Model
    {
        $model = parent::read($id);
        $model->append(['miniapp_ids']);
        return $model;
    }

    public function save($params): Model
    {
        $appIds = $params['miniapp_ids'];
        unset($params['miniapp_ids']);

        $model = parent::save($params);

        // 关联小程序
        $this->relationMiniapp($model->id, $appIds);

        return $model;
    }

    public function update($id, $params): Model
    {
        $newAppIds = array_map('intval', $params['miniapp_ids']);
        unset($params['miniapp_ids']);

        /** @var Admin $model */
        $model = parent::update($id, $params);

        //数据库原来存放这的ids
        $oldAppIds = $model->miniapps()->column('miniapp_id');

        //从$oldAppIds里找出不在$newAppIds中的id，并删除，同时找出$newAppIds里不在$oldAppIds中，添加到数据库
        // 找出交集（不变的部分）
        $intersection = array_intersect($newAppIds, $oldAppIds);

        // 找出在 $oldAppIds 中独有的元素（需要删除的部分）
        $toDelete = array_diff($oldAppIds, $intersection);

        // 找出在 $newAppIds 中独有的元素（需要添加的部分）
        $toAdd = array_diff($newAppIds, $intersection);

        //删除
        if ($toDelete) {
            $model->miniapps()->whereIn('miniapp_id', $toDelete)->delete();
        }

        //添加
        if ($toAdd) {
            $this->relationMiniapp($model->id, $toAdd);
        }

        return $model;
    }

    /**
     * 关联小程序
     * @param $adminId
     * @param $miniappIds
     * @return int 关联数量
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function relationMiniapp($adminId, $miniappIds): int
    {
        $apps = MiniApp::where('id', 'in', $miniappIds)->select();
        $insertData = [];
        $apps->each(function ($miniapp) use (&$insertData, $adminId) {
            $insertData[] = [
                'admin_id'   => $adminId,
                'miniapp_id' => $miniapp->id,
                'module'     => $miniapp->module,
                'create_at' => date('Y-m-d H:i:s'),
            ];
        });
        return AdminRelation::insertAll($insertData);
    }
}