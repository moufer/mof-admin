<?php

namespace module\miniapp\controller\frontend;

use module\miniapp\library\MiniappFrontendController;
use module\miniapp\library\WechatMiniappApplication;
use module\miniapp\logic\UserLogic;
use mof\annotation\Inject;
use mof\ApiResponse;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use think\db\exception\DbException;
use think\response\Json;

class WechatUser extends MiniappFrontendController
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
        $auth = $this->logic->loginByCode($code, $this->miniapp);
        return ApiResponse::success($auth->getUser()->toArray());
    }

    public function update($iv, $encryptedData): Json
    {
        //decryptSession($sessionKey, $iv, $encryptedData)
        $userData = $this->logic->getEncryptedData($iv, $encryptedData, $this->miniapp);
        $this->logic->update($this->auth->getId(), $userData);
        return ApiResponse::success();
    }
}