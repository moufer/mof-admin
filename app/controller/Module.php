<?php

namespace app\controller;

use app\library\Controller;
use app\logic\ModuleLogic;
use mof\annotation\AdminPerm;
use mof\annotation\Description;
use mof\annotation\Inject;
use mof\ApiResponse;
use think\db\exception\DbException;
use think\response\Json;

#[AdminPerm(
    title: '模块管理', url: 'system/module',
    actions: 'index,install,uninstall,disable,enable',
    sort: 2, icon: 'Grid', group: 'system'
)]
class Module extends Controller
{
    #[Inject]
    protected ModuleLogic $logic;

    public function initialize(): void
    {
        // 载入命令行
        $this->logic->loadCommand();
    }

    public function index(): Json
    {
        $modules = $this->logic->list($this->request->get('params/a', []));
        return ApiResponse::success(array_values($modules));
    }

    /**
     * 安装模块
     * @param $name
     * @return Json
     * @throws \Exception
     */
    #[Description('安装')]
    public function install($name): Json
    {
        return ApiResponse::success($this->logic->install($name));
    }

    /**
     * 卸载模块
     * @param $name
     * @return Json
     */
    #[Description('卸载')]
    public function uninstall($name): Json
    {
        $this->logic->uninstall($name);
        return ApiResponse::success();
    }

    /**
     * 停用模块
     * @param $name
     * @return Json
     */
    #[Description('停用')]
    public function disable($name): Json
    {
        $this->logic->disable($name);
        return ApiResponse::success();
    }

    /**
     * 启用模块
     * @param $name
     * @return Json
     * @throws DbException
     */
    #[Description('启用')]
    public function enable($name): Json
    {
        $this->logic->enable($name);
        return ApiResponse::success();
    }
}