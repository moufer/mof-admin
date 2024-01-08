<?php

namespace app\controller;

use app\library\AdminController;
use app\logic\ModuleLogic;
use mof\annotation\Inject;
use mof\ApiResponse;
use mof\InstallModule;
use think\db\exception\DbException;
use think\response\Json;

class Module extends AdminController
{
    protected string $modelName = \app\model\Module::class;

    #[Inject]
    protected ModuleLogic $moduleLogic;

    public function initialize(): void
    {
        parent::initialize();
        // 载入命令行
        $this->moduleLogic->loadCommand();
    }

    public function index(): Json
    {
        $modules = $this->moduleLogic->list($this->request->get('params/a'));
        return ApiResponse::success(array_values($modules));
    }

    /**
     * 安装模块
     * @param $name
     * @return Json
     * @throws \Exception
     */
    public function install($name): Json
    {
        return ApiResponse::success($this->moduleLogic->install($name));
    }

    /**
     * 卸载模块
     * @param $name
     * @return Json
     */
    public function uninstall($name): Json
    {
        $this->moduleLogic->uninstall($name);
        return ApiResponse::success();
    }

    /**
     * 停用模块
     * @param $name
     * @return Json
     */
    public function disable($name): Json
    {
        $this->moduleLogic->disable($name);
        return ApiResponse::success();
    }

    /**
     * 启用模块
     * @param $name
     * @return Json
     */
    public function enable($name): Json
    {
        $this->moduleLogic->enable($name);
        return ApiResponse::success();
    }
}