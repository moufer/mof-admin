<?php

namespace module\miniapp\controller\backend;

use app\library\Controller;
use app\model\Perm;
use module\miniapp\model\AdminRelation;
use module\miniapp\model\MiniApp;
use mof\ApiResponse;
use mof\Searcher;
use mof\utils\Arr;
use think\db\exception\DbException;
use think\response\Json;

class Index extends Controller
{
    public function index(): Json
    {
        $params = $this->request->param();
        $pageSize = $this->request->get('page_size/d', 10);
        if (!$this->auth->getUser()->is_super_admin) {
            //找管理员关联的小程序
            $miniappIds = AdminRelation::where(['admin_id' => $this->auth->getUser()->id])
                ->column('miniapp_id');
            $params['miniapp_ids'] = $miniappIds ?: [];
        }

        $paginate = (new Searcher())->model(MiniApp::class)
            ->params($params)
            ->pageSize($pageSize)
            ->paginate();

        return ApiResponse::success($paginate);
    }

    /**
     * 获取指定小程序
     * @param $id
     * @return Json
     * @throws DbException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function read($id): Json
    {
        $model = MiniApp::find($id);
        if (!$model) {
            return ApiResponse::error('小程序不存在');
        }
        //加载模块信息
        $model->append(['module_info'], true);
        if (!$model->module_info || !$model->module_info->status) {
            return ApiResponse::error('模块不存在或未启用');
        }

        //加载权限信息
        $model->setAttr('perms', $this->getPerms($model));
        return ApiResponse::success($model);
    }

    /**
     * @param $miniapp
     * @return array
     * @throws DbException
     */
    protected function getPerms($miniapp): array
    {
        $modules = ['miniapp'];
        if ($miniapp->module_info->status) {
            $modules[] = $miniapp->module;
        }
        $result = Perm::where('category', 'miniapp')
            ->whereIn('module', $modules)
            ->order('sort', 'asc')
            ->select()
            ->toArray();
        return Arr::tree($result);
    }
}