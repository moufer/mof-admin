<?php

namespace app\library;

use mof\annotation\Inject;
use mof\ApiController;
use mof\FormValidate;
use mof\interface\AuthInterface;

/**
 * 后台管理基础控制器
 * Class Controller
 * @package app\library
 */
class Controller extends ApiController
{
    /**
     * @var Request 请求
     */
    #[Inject]
    protected Request $request;

    /**
     * @var Auth 登录授权
     */
    #[Inject(Auth::class)]
    protected AuthInterface $auth;

}
