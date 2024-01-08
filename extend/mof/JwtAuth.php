<?php

declare(strict_types=1);

namespace mof;

use Exception;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use think\facade\Config;

class JwtAuth
{
    /**
     * 生成jwt token
     * @param string $sub 用户id
     * @param int $expires 到期时间，默认是7200秒
     * @param array $payload
     * @return string
     */
    public static function create(string $sub, int $expires = 7200, array $payload = []): string
    {
        $key = Config::get('jwt.key');
        $time = time();
        $payload = [
            'iss' => $payload['iss'] ?? app('request')->domain(), //签发者
            'aud' => $payload['aud'] ?? 'mof', //面向的用户
            'iat' => $time, //签发时间
            'exp' => $time + $expires, //过期时间
            'sub' => $sub, //用户id
        ];
        return JWT::encode($payload, $key, 'HS256', Config::get('jwt.key_id'));
    }

    /**
     * 验证token
     * @param string $jwt
     * @param $aud
     * @return int|\stdClass
     */
    public static function verify(string $jwt, $aud): int|\stdClass
    {
        $key = Config::get('jwt.key');
        try {
            $key = new Key($key, 'HS256');
            $result = JWT::decode($jwt, $key);
            //验证签发域名, aud
            if (app('request')->domain() !== $result->iss
                || $aud !== $result->aud) {
                return -2;
            }
            return $result;
        } catch (SignatureInvalidException) { //签名不正确
            return -4;
        } catch (BeforeValidException|ExpiredException) { // 签名在某个时间点之后才能用 | token过期
            return -5;
        } catch (Exception) { //其他错误
            return -10;
        }
    }

}
