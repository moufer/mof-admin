<?php

namespace app\controller;

use app\model\Admin;
use mof\BaseController;
use mof\ApiResponse;
use mof\utils\Arr;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\exception\ValidateException;
use think\response\Json;

class Passport extends BaseController
{
    /**
     * 登录
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function login(): Json
    {
        $status = 0;
        $username = input('username');
        $password = input('password');
        try {
            $user = Admin::where('username', $username)->find();
            if (!$user) {
                $status = -1;
                throw new ValidateException('用户不存在');
            }
            //验证密码
            if (!password_verify($password, $user->password)) {
                $status = -2;
                throw new \InvalidArgumentException('密码错误');
            }
            //生成token
            $token = app('token')->create($user, 'admin');
            $status = 1;
            return ApiResponse::success([
                'token' => $token,
                'user'  => $user,
            ]);
        } finally {
            //登录事件
            event('AdminLogin', ['username' => $username, 'status' => $status]);
        }
    }

    /**
     * 获取验证token
     * @return Json
     */
    public function token(): Json
    {
        $token = $this->request->header('Authorization');
        return ApiResponse::success([
            'token' => $token,
            'user'  => $this->request->user,
            'perms' => Arr::tree($this->request->user->role->getPerms()),
        ]);
    }

    /**
     * 退出登录
     * @return Json
     */
    public function logout(): Json
    {
        app('token')->destroy();
        event('AdminLogout', ['username' => $this->request->user->username]);
        return ApiResponse::success();
    }

    public function info(): Json
    {
        $result = [
            'user'     => $this->request->user->toArray(),
            'perms'    => Arr::tree($this->request->user->role->getPerms()),
        ];
        return ApiResponse::success($result);
    }

    public function perms(): Json
    {
        $perms = $this->request->user->role->getPerms();
        return ApiResponse::success(Arr::tree($perms));
    }
}
