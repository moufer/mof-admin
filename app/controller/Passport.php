<?php

namespace app\controller;

use app\front\form\PassportForm;
use app\library\Controller;
use app\logic\PassportLogic;
use mof\annotation\Inject;
use mof\ApiResponse;
use mof\utils\Arr;
use think\db\exception\DbException;
use think\response\Json;

class Passport extends Controller
{
    #[Inject]
    protected PassportLogic $logic;

    #[Inject]
    protected PassportForm $form;

    /**
     * 登录
     * @return Json
     * @throws DbException
     */
    public function login(): Json
    {
        $data = $this->form->get();
        //登录
        $auth = $this->logic->withModule($data['module'])->login($data['username'], $data['password']);
        //登录时指定了模块，使用该模块的权限
        $module = $this->request->param('module', 'system');
        return ApiResponse::success([
            'token' => $auth->getToken()->toArray(),
            'user'  => $auth->getUser()->hidden(['password']),
            'perms' => Arr::tree($auth->getUser()->getPerms($module)),
        ]);
    }

    /**
     * 登出
     * @return Json
     */
    public function logout(): Json
    {
        $this->logic->logout();
        return ApiResponse::success();
    }

    /**
     * 获取验证token
     * @return Json
     */
    public function token(): Json
    {
        return ApiResponse::success($this->auth->getToken()->toArray());
    }

    public function info(): Json
    {
        $module = $this->request->param('module', 'system');
        $user = $this->auth->getUser()->hidden(['password']);
        if('system' === $module && 'system' !== $user->module) {
            return ApiResponse::error('当前用户没有系统后台权限');
        }
        $perms = $user->module === 'system'
            ? $this->auth->getUser()->role->getPerms()
            : [];
        $result = [
            'user'  => $user->toArray(),
            'perms' => Arr::tree($perms),
        ];
        return ApiResponse::success($result);
    }

    public function perms(): Json
    {
        $user = $this->auth->getUser();
        if ($user->module !== 'system') {
            return ApiResponse::error('当前用户没有管理该模块的权限');
        }
        $perms = $this->auth->getUser()->role->getPerms();
        return ApiResponse::success(Arr::tree($perms));
    }
}
