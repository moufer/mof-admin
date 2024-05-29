<?php

namespace app\logic;

use app\enum\LoginStatus;
use app\library\Auth;
use app\model\Admin;
use mof\annotation\Inject;
use mof\exception\LogicException;
use mof\Logic;
use mof\Model;

class PassportLogic extends Logic
{
    /**
     * @var Admin 操作模型
     */
    #[Inject(Admin::class, false)]
    protected $model;

    #[Inject]
    protected Auth $auth;

    protected string $module = 'system';

    public function login($username, $password): Auth
    {
        try {
            $status = LoginStatus::SUCCESS;

            //验证用户
            $user = $this->model->where('username', $username)->find();
            if (!$user) {
                $status = LoginStatus::NOT_FOUND;
            } else if (!password_verify($password, $user->password)) {
                $status = LoginStatus::PASSWORD_WRONG;
            } else if ($user->module !== $this->module) {
                $status = LoginStatus::NOT_MODULE_ADMIN;
            }

            if ($status !== LoginStatus::SUCCESS) {
                throw new LogicException($status->label());
            }

            //登录
            $this->auth->login($user);

            //更新上次登录信息
            $user->save([
                'login_ip'      => $this->app->request->ip(),
                'login_at'      => date('Y-m-d H:i:s'),
                'last_login_ip' => $user->login_ip ?: '',
                'last_login_at' => $user->login_at ?: date('Y-m-d H:i:s'),
            ]);

            $this->model = $user;
            return $this->auth;

        } finally {
            //登录事件
            event('AdminLogin', ['username' => $username, 'status' => $status]);
        }
    }

    public function logout(): void
    {
        $params = $this->auth->getUser()->visible(['id', 'username'])->toArray();

        $this->auth->logout();

        //登出事件
        event('AdminLogout', $params);
    }

    public function withModule($module): static
    {
        $this->module = $module;
        return $this;
    }
}