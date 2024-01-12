<?php

namespace app\library;

use mof\annotation\Inject;
use mof\ApiController;

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
    #[Inject]
    protected Auth $auth;

    /**
     * 是否开启软删除
     * @var bool
     */
    protected bool $softDelete = false;

    /**
     * 验证规则或验证器类名
     */
    protected string|array $validate = [];

    /**
     * 验证反馈消息
     * @var array
     */
    protected array $validateMessage = [];


    protected function initialize(): void
    {
        parent::initialize();
        $this->setValidate();
    }

    /**
     * 给定验证规则或验证器
     * @return void
     */
    protected function setValidate(): void
    {
        if ($this->validate) {
            $this->request->withValidate($this->validate, $this->validateMessage);
        }
    }
}
