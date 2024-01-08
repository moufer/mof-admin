<?php

namespace module\miniapp\controller;

use app\library\AdminController;
use app\library\Searcher;
use module\miniapp\logic\MiniAppLogic;
use module\miniapp\model\MiniApp;
use mof\annotation\Inject;
use mof\ApiResponse;
use mof\Model;
use mof\utils\Arr;
use think\response\Json;

/**
 * 小程序管理
 */
class Manage extends AdminController
{
    protected string $modelName = MiniApp::class;

    protected array $validateRules = [
        'type'       => 'require|in:wechat',
        'title'      => 'require',
        'appid'      => 'require|unique:miniapp',
        'app_secret' => 'require',
    ];

    protected array $validateMessage = [
        'title.require'      => '小程序名称不能为空',
        'appid.require'      => '小程序AppID不能为空',
        'appid.unique'       => '小程序AppID已存在',
        'app_secret.require' => '小程序AppSecret不能为空',
        'type.require'       => '小程序类型不能为空',
        'type.in'            => '小程序类型不正确',
    ];

    #[Inject]
    protected MiniAppLogic $miniAppLogic;

    public function index(): Json
    {
        return ApiResponse::success(
            $this->miniAppLogic->index(
                Searcher::make($this->request)
            )
        );
    }

    public function onReadAfter(Model $model): void
    {
        $append = $this->request->param('append', '', 'trim');
        //是否获取perm数据
        if ($append) {
            $append = explode(',', $append);
            if (in_array('module', $append)) {
                $model->append(['module_info'], true);
            }
            if (in_array('perms', $append)) {
                $model->setAttr(
                    'perms',
                    Arr::tree($this->request->user->role->getPerms('miniapp', ['miniapp', $model->getData('module')]))
                );
            }
        }
    }

    public function onDeleteAfter(Model $model): void
    {
        // 触发小程序删除后事件
        event('MiniAppDeleteAfter', $model);
    }
}