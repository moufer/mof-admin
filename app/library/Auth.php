<?php

namespace app\library;

use mof\exception\AuthTokenException;
use mof\interface\AuthInterface;
use mof\interface\TokenInterface;
use mof\interface\UserInterface;
use mof\Model;
use mof\Token;
use think\facade\Cache;

class Auth implements AuthInterface
{
    protected TokenInterface $token;
    /**
     * @var UserInterface|null 当前登录用户
     */
    protected ?UserInterface $user = null;

    protected string $aud = 'admin';

    public function __construct()
    {
        $this->token = new Token();
    }

    /**
     * 用户登录
     * @param UserInterface $user
     * @return bool
     */
    public function login(UserInterface $user): bool
    {
        //生成token信息
        $token = $this->token->create($this->aud);
        //缓存用户
        Cache::set($this->token->uuid(), $user, $token['expires'] - time());

        $this->setUser($user);
        return true;
    }

    /**
     * 设置用户
     * @param UserInterface $user
     * @return void
     */
    public function setUser(UserInterface $user): void
    {
        $this->user = $user;
    }

    /**
     * 登出
     * @return bool
     */
    public function logout(): bool
    {
        Cache::delete($this->token->uuid());
        $this->user = null;
        $this->token->destroy();
        return true;
    }

    /**
     * 验证token
     * @param string $token
     * @return bool
     */
    public function verify(string $token): bool
    {
        //验证token有消息
        $this->token->verify($token, $this->aud);
        //通过uuid获取用户缓存
        if (!$user = Cache::get($this->token->uuid())) {
            throw new AuthTokenException('登录已失效，请重新登录');
        }
        $this->user = $user;

        return true;
    }

    /**
     * 用户ID
     * @return int
     */
    public function getId(): int
    {
        return $this->user?->id ?? 0;
    }

    /**
     * 用户信息
     * @return UserInterface
     */
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    /**
     * 是否已登录
     * @return bool
     */
    public function isLogin(): bool
    {
        return !empty($this->getId());
    }

    /**
     * 刷新用户信息
     * @return void
     */
    public function refresh(): void
    {
        $this->user->refresh();
        $token = $this->token->toArray();
        Cache::set($this->token->uuid(), $this->user, $token['expires'] - time());
    }

    /**
     * 获取token信息
     * @return TokenInterface
     */
    public function getToken(): TokenInterface
    {
        return $this->token;
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