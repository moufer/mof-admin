<?php

namespace module\miniapp\middleware;

use module\miniapp\model\MiniApp;
use Closure;
use mof\ApiResponse;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Request;
use think\Response;

/**
 * 获取指定的应用信息
 */
class MiniappFrontendMiddleware
{
    /**
     * 处理请求
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$id = $request->param('id/d')) {
            return ApiResponse::error('未指小程序');
        }
        if (!$miniapp = MiniApp::find($id)) {
            return ApiResponse::error('小程序不存在');
        }

        //挂载到容器内，控制器和逻辑层可以通过inject注解注入
        app()->instance(get_class($miniapp), $miniapp);

        return $next($request);
    }
}