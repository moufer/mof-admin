<?php

namespace app\library;

use app\model\Admin;
use mof\exception\AuthTokenException;
use mof\Model;
use mof\Token;
use think\facade\Cache;

class Auth
{
    protected Token $token;
    /**
     * @var Admin|null 当前登录用户
     */
    protected ?Admin $user = null;

    public function __construct()
    {
        $this->token = new Token();
    }

    /**
     * 用户登录
     * @param $user
     * @return bool
     */
    public function login($user): bool
    {
        //生成token信息
        $token = $this->token->create('admin');
        //缓存用户
        Cache::set($this->token->uuid(), $user, $token['expires'] - time());

        $this->user = $user;
        return true;
    }

    /**
     * 登出
     * @return void
     */
    public function logout(): void
    {
        Cache::delete($this->token->uuid());
        $this->user = null;
        $this->token->destroy();
    }

    /**
     * 验证token
     * @param string $token
     * @return bool
     */
    public function verify(string $token): bool
    {
        $this->token->verify($token, 'admin');
        //通过uuid获取用户缓存
        if (!$user = Cache::get($this->token->uuid())) {
            throw new AuthTokenException('登录已失效，请重新登录');
        }
        $this->user = $user;

        return true;
    }

    /**
     * 用户ID
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->user?->id ?? null;
    }

    /**
     * 用户信息
     * @return Admin|null
     */
    public function getUser(): ?Admin
    {
        return $this->user;
    }

    /**
     * 是否已登录
     * @return bool
     */
    public function isLogin(): bool
    {
        return $this->getId() !== null;
    }

    /**
     * 刷新用户信息
     * @return void
     */
    public function refresh(): void
    {
        $this->user->refresh();
        $token = $this->token->token();
        Cache::set($this->token->uuid(), $this->user, $token['expires'] - time());
    }

    /**
     * 获取token信息
     * @return array|null
     */
    public function getToken(): ?array
    {
        return $this->token->token();
    }

    /**
     * 获取 Token 类
     * @return Token
     */
    public function handler(): Token
    {
        return $this->token;
    }

}