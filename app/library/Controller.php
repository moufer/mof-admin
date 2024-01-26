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

    /**
     * @var ?FormValidate 表单验证
     */
    protected ?FormValidate $form;

    /**
     * 验证信息
     * 格式 [params=>params,allow=>allow,only=>only,rule=>rule,message=>message]
     */
    protected array $formValidate = [];

    protected function initialize(): void
    {
        parent::initialize();
        $this->formValidate();
    }

    /**
     * 给定验证规则或验证器
     * @return void
     */
    protected function formValidate(): void
    {
        $this->form = FormValidate::make($this->formValidate);
    }
}
