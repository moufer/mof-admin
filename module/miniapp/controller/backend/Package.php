<?php

namespace module\miniapp\controller\backend;

use module\miniapp\library\MiniappController;
use module\miniapp\logic\admin\PackageLogic;
use mof\annotation\AdminPerm;
use mof\annotation\Description;
use mof\annotation\Inject;
use mof\ApiResponse;
use think\db\exception\DbException;
use think\response\File;
use think\response\Json;

#[AdminPerm(
    title: '打包上传', url: 'miniapp/package', actions: 'submit,download', sort: 4, icon: 'Box',
    group: 'common', category: 'miniapp'
)]
class Package extends MiniappController
{
    /**
     * @var PackageLogic 小程序打包业务逻辑
     */
    #[Inject]
    protected PackageLogic $logic;

    /**
     * @return Json
     * @throws \Exception
     */
    public function form(): Json
    {
        return ApiResponse::success($this->logic->form());
    }

    /**
     * 提交并打包
     * @return Json
     * @throws \Exception
     */
    #[Description('打包')]
    public function submit(): Json
    {
        $data = $this->request->withValidate([
            'siteroot|通信地址'
        ])->param();
        return ApiResponse::success($this->logic->submit($data));
    }

    /**
     * 下载打包的文件
     * @return File|Json
     * @throws \Exception
     */
    #[Description('下载')]
    public function download(): File|Json
    {
        $key = $this->request->withValidate(['key|参数'])->param('key');
        return $this->logic->download($key);
    }

    /**
     * 下载完成
     * @return Json
     * @throws DbException
     */
    public function downloaded(): Json
    {
        $key = $this->request->withValidate(['key|参数'])->param('key');
        $this->logic->downloaded($key);
        return ApiResponse::success();
    }

}