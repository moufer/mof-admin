<?php

namespace module\miniapp\controller;

use module\miniapp\library\MiniappController;
use module\miniapp\logic\PackageLogic;
use mof\annotation\Inject;
use mof\ApiResponse;
use think\db\exception\DbException;
use think\response\File;
use think\response\Json;

/**
 * 小程序打包
 */
class Package extends MiniappController
{
    /** @var PackageLogic 小程序打包业务逻辑 */
    #[Inject]
    protected PackageLogic $packageLogic;

    /**
     * @return Json
     * @throws \Exception
     */
    public function form(): Json
    {
        return ApiResponse::success($this->packageLogic->form());
    }

    /**
     * 提交并打包
     * @return Json
     * @throws \Exception
     */
    public function submit(): Json
    {
        //获取post参数
        $data = $this->request->post();
        return ApiResponse::success($this->packageLogic->submit($data));
    }

    /**
     * 下载打包的文件
     * @return File|Json
     * @throws \Exception
     */
    public function download(): File|Json
    {
        $key = $this->request->param('key');
        if (!$key) return ApiResponse::error('参数错误');
        //下载
        return $this->packageLogic->download($key);
    }

    /**
     * 下载完成
     * @return Json
     * @throws DbException
     */
    public function downloaded(): Json
    {
        $key = $this->request->param('key');
        if ($key) {
            $this->packageLogic->downloaded($key);
            return ApiResponse::success();
        }
        return ApiResponse::error();
    }

}