<?php

namespace module\miniapp\middleware;

use app\library\Auth;
use module\miniapp\model\AdminRelation;
use module\miniapp\model\MiniApp;
use Closure;
use mof\ApiResponse;
use think\Request;
use think\Response;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $auth = app(Auth::class);
        //如果未登录，则提示用户登录
        if (!$auth->isLogin()) {
            return ApiResponse::needLogin();
        }

        //如果登录会员是超级管理员则直接通过
        if ($auth->getUser()->is_super_admin) {
            return $next($request);
        }

        list($module, $controller, $action) = $this->parseRule($request->rule()->getName());
        if ($module !== 'miniapp') {
            return ApiResponse::error('无效的访问接口');
        } else if ($controller === 'Index') {
            return $next($request); //首页不检测权限
        }

        $miniapp = app(MiniApp::class);
        if (!$miniapp->id) {
            return ApiResponse::error('未指定管理小程序');
        }

        //找登录用户是否有这个小程序的管理权限
        $exists = AdminRelation::where([
                'admin_id'   => $auth->getUser()->id,
                'miniapp_id' => $miniapp->id
            ])->count() > 0;
        if (!$exists) {
            return ApiResponse::noPermission('无权限访问该小程序');
        }

        return $next($request);
    }

    /**
     * 解析路由规则，从中获取module,controller,action
     * @param $name string 路由规则
     * @return array [module,controller,action] 返回模块名、控制器名、方法名
     */
    protected function parseRule(string $name): array
    {
        //name格式1: \app\\controller\{controller}[@\/]{action}
        //name格式2: \module\\miniapp\controller\{controller}[@\/]{action}

        $name = trim(str_replace(['\\', '@', '//'], '/', $name), '/');
        $path = explode('/', $name);
        $action = array_pop($path);
        $controller = array_pop($path);
        $module = $path[0] === 'app' ? 'admin' : $path[1];

        return [$module, $controller, $action];
    }
}