<?php

namespace module\miniapp\controller\backend;

use module\miniapp\library\MiniappController;
use module\miniapp\logic\admin\PayLogic;
use mof\annotation\AdminPerm;
use mof\annotation\Description;
use mof\annotation\Inject;
use mof\ApiResponse;
use think\response\Json;

#[AdminPerm(
    title: '支付配置', url: 'miniapp/pay', actions: 'submit', sort: 3, icon: 'CreditCard',
    group: 'common', category: 'miniapp'
)]
class Pay extends MiniappController
{
    /**
     * @var PayLogic 小程序打包业务逻辑
     */
    #[Inject]
    protected PayLogic $logic;

    /**
     * @return Json
     * @throws \Exception
     */
    public function form(): Json
    {
        return ApiResponse::success($this->logic->form());
    }

    /**
     * 提交
     * @return Json
     * @throws \Exception
     */
    #[Description('提交')]
    public function submit(): Json
    {
        $data = $this->request->withValidate([
            'mch_id|商户ID'            => 'require',
            'private_key|商户证书私钥' => 'require',
            'certificate|商户证书'     => 'require',
            'secret_key|API秘钥'       => 'require'
        ])->param();

        return ApiResponse::success($this->logic->submit($data));
    }
}