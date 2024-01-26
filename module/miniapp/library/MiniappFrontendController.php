<?php

namespace module\miniapp\library;

use mof\annotation\Inject;
use mof\ApiController;
use mof\FormValidate;
use mof\interface\AuthInterface;
use mof\Request;

class MiniappFrontendController extends ApiController
{
    /**
     * @var Request 请求
     */
    #[Inject]
    protected Request $request;

    /**
     * @var AuthFrontend 登录授权
     */
    #[Inject(AuthFrontend::class)]
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