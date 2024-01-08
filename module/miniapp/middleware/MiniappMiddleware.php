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
class MiniappMiddleware
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
        $id = $request->param('id/d', 0);
        if (!$id) {
            return ApiResponse::error('未指小程序ID');
        }
        if (!$miniapp = MiniApp::find($id)) {
            return ApiResponse::error('小程序不存在');
        }
        $request->miniapp = $miniapp;

        return $next($request);
    }
}