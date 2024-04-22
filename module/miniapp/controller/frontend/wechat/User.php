<?php

namespace module\miniapp\controller\frontend\wechat;

use module\article\logic\UserLogic;
use module\miniapp\library\MiniappFrontendController;
use mof\annotation\Inject;
use mof\ApiResponse;
use think\db\exception\DbException;
use think\response\Json;

class User extends MiniappFrontendController
{
    #[Inject]
    protected UserLogic $logic;

    /**
     * 通过code登录获取session_key和openid
     * @param $code
     * @return Json
     * @throws DbException
     */
    public function login($code): Json
    {
        $auth = $this->logic->loginByCode($code);
        return ApiResponse::success([
            'token' => $auth->getToken()->toArray(), //登录token
            'user'  => $auth->getUser()->toArray(),
        ]);
    }

    public function update($iv, $encryptedData): Json
    {
        //decryptSession($sessionKey, $iv, $encryptedData)
        $userData = $this->logic->updateByEncryptedData($iv, $encryptedData);
        $this->logic->update($this->auth->getId(), $userData);
        return ApiResponse::success();
    }
}