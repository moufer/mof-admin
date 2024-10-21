<?php
/**
 * Project: MofAdmin
 * Author: moufer <moufer@163.com>
 * Date: 2024/10/18 21:42
 */

namespace app\controller;

use app\front\form\UserForm;
use app\library\Controller;
use app\logic\AdminLogic;
use app\logic\AdminLoginLogLogic;
use mof\annotation\AdminPerm;
use mof\annotation\Inject;
use mof\ApiResponse;
use think\response\Json;

#[AdminPerm(
    title: '个人资料', url: 'system/profile', actions: '*,!selector',
    sort: 4, icon: 'Postcard', group: 'system'
)]
class Profile extends Controller
{
    #[Inject]
    protected AdminLogic $logic;

    #[Inject]
    protected UserForm $form;

    public function index(AdminLoginLogLogic $logLogic): Json
    {
        return ApiResponse::success(
            $logLogic->paginate(
                $this->request->searcher([
                    'username' => $this->auth->getUser()->username
                ])
            )
        );
    }

    public function edit(): Json
    {
        return ApiResponse::success(
            $this->form->buildProfileForm($this->logic->read($this->auth->getId()))
        );
    }

    public function update(): Json
    {
        $data = $this->form
            ->withFixed(['id' => $this->auth->getId()])
            ->withScene('profile')
            ->get();

        $this->logic
            ->withAccess(['id' => $this->auth->getId()])
            ->update($this->auth->getId(), $data);

        return ApiResponse::success();
    }
}