<?php
declare (strict_types=1);

namespace app\middleware;

use app\library\Auth;
use Closure;
use mof\ApiResponse;
use mof\exception\AuthTokenException;
use think\Request;
use think\Response;

class AuthTokenMiddleware
{
    /**
     * 处理请求
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        //验证header中的token，token是jwt生成的，验证通过则返回用户信息
        $token = $request->header('Authorization');
        if (!$token || !str_starts_with($token, 'Bearer ')) {
            return ApiResponse::needLogin();
        }
        try {
            $auth = app(Auth::class);
            $auth->verify(substr($token, 7));
        } catch (AuthTokenException $e) {
            return ApiResponse::needLogin($e->getMessage());
        }

        //获取用户信息
        if ($auth->getUser()->status !== 1) {
            return ApiResponse::noPermission('当前账号已被禁用');
        }
        $request->user = $auth->getUser()->hidden(['password']);
        return $next($request);
    }
}
