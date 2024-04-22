<?php

namespace module\miniapp\logic;

use EasyWeChat\Kernel\Exceptions\DecryptException;
use module\miniapp\library\AuthFrontend;
use module\miniapp\library\WechatMiniappApplication;
use module\miniapp\model\MiniApp;
use module\miniapp\model\User;
use mof\annotation\Inject;
use mof\exception\LogicException;
use mof\interface\AuthInterface;
use mof\interface\UserInterface;
use mof\Logic;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

class UserLogic extends Logic
{
    /**
     * @var User
     */
    #[Inject(User::class)]
    protected $model;

    #[Inject]
    protected AuthFrontend $auth;

    #[Inject]
    protected MiniApp $miniapp;

    /**
     * @param $code
     * @return AuthInterface
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function loginByCode($code): AuthInterface
    {
        try {
            $handler = WechatMiniappApplication::make($this->miniapp)->handler();
            $data = $handler->getUtils()->codeToSession($code);
        } catch (ExceptionInterface|\Exception $e) {
            throw new LogicException($e->getMessage());
        }

        $model = $this->model()->where('miniapp_id', $this->miniapp->id)
            ->where('openid', $data['openid'])
            ->find();

        if (!$model) {
            //自动注册
            $model = $this->model->newInstance([
                'miniapp_id'   => $this->miniapp->id,
                'miniapp_type' => $this->miniapp->type,
                'unionid'      => $data['unionid'] ?? '',
                'openid'       => $data['openid'],
                'nickname'     => $data['nickName'] ?? '微信用户',
            ]);
        }
        $model->setAttr('session_key', $data['session_key']);
        $model->save();

        //登录
        $this->auth->login($model);

        //更新上次登录信息
        $model->save([
            'login_ip' => $this->app->request->ip(),
            'login_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->auth;
    }

    /**
     * 解码并更新用户数据
     * @param $iv
     * @param $encryptedData
     * @return UserInterface|null
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function updateByEncryptedData($iv, $encryptedData): ?UserInterface
    {
        $handler = WechatMiniappApplication::make($this->miniapp)->handler();
        $sessionKey = $this->auth->getUser()->getAttr('session_key');
        try {
            $userData = $handler->getUtils()->decryptSession($sessionKey, $iv, $encryptedData);
            $this->update($this->auth->getId(), $userData);
            $this->auth->refresh(); //刷新
            return $this->auth->getUser();
        } catch (DecryptException) {
            throw new LogicException('用户数据解密失败');
        }
    }
}