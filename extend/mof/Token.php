<?php

namespace mof;

use Exception;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use mof\exception\AuthTokenException;
use mof\interface\TokenInterface;
use mof\utils\Random;

/**
 * Token
 */
class Token implements TokenInterface
{
    protected string $key;
    protected int    $expires;

    protected string $token;
    protected string $uuid;

    protected array $payload;

    public function __construct()
    {
        $jwt = config('jwt');
        $this->key = $jwt['key'];
        $this->expires = $jwt['expires'] ?? 7200;
    }

    /**
     * 创建令牌
     * @param string $aud 面向的用户
     * @return array 令牌信息，格式：['token' => $token, 'expires' => $expires]
     */
    public function create(string $aud = 'admin'): array
    {
        //生成token
        $payload = [];
        $uuid = Random::uuid();
        $token = $this->createJWT($uuid, $aud, $payload);

        $this->uuid = $uuid;
        $this->token = $token;
        $this->payload = $payload;

        return $this->toArray();
    }

    /**
     * 验证令牌
     * @param string $token 令牌
     * @param string $module 模块标识
     * @throws AuthTokenException
     */
    public function verify(string $token, string $module = 'admin'): array
    {
        $payload = $this->verifyJWT($token, $module);
        if (!is_object($payload)) {
            throw new AuthTokenException('登录令牌无效，请重新登录');
        }

        $this->uuid = $payload->sub;
        $this->token = $token;
        $this->payload = (array)$payload;

        return $this->payload;
    }

    /**
     * 获取令牌信息
     * @return array 令牌信息 ，格式：['token' => $token, 'expires' => $expires]
     */
    public function toArray(): array
    {
        if (!empty($this->token)) {
            return [
                'token'   => $this->token, //令牌
                'expires' => $this->payload['exp'], //过期时间
            ];
        }
        return [];
    }

    /**
     * 获取唯一ID
     * @return string
     */
    public function uuid(): string
    {
        return $this->uuid;
    }

    /**
     * 清除
     * @return void
     */
    public function destroy(): void
    {
        $this->uuid = '';
        $this->token = '';
        $this->payload = [];
    }

    /**
     * 生成jwt token
     * @param string $sub 用户id
     * @param string $aud
     * @param array $payload
     * @return string
     */
    protected function createJWT(string $sub, string $aud, array &$payload = []): string
    {
        $time = time();
        $payload = [
            'iss' => $payload['iss'] ?? app('request')->domain(), //签发者
            'aud' => $aud, //面向的用户
            'iat' => $time, //签发时间
            'exp' => $time + $this->expires, //过期时间
            'sub' => $sub, //用户id
        ];
        return JWT::encode($payload, $this->key, 'HS256', config('jwt.key_id'));
    }

    /**
     * 验证jwt
     * @param string $jwt
     * @param string|null $aud null时不对aud进行验证
     * @return int|\stdClass
     */
    protected function verifyJWT(string $jwt, string $aud = null): int|\stdClass
    {
        try {
            $key = new Key($this->key, 'HS256');
            $result = JWT::decode($jwt, $key);
            //验证签发域名, aud
            if (app('request')->domain() !== $result->iss) {
                return 10002;
            } else if ($aud && $aud !== $result->aud) {
                return 10003;
            }
            return $result;
        } catch (SignatureInvalidException) { //签名不正确
            return 10004;
        } catch (BeforeValidException|ExpiredException) { // 签名在某个时间点之后才能用 | token过期
            return 10005;
        } catch (Exception $e) { //其他错误
            return 10010;
        }
    }

}