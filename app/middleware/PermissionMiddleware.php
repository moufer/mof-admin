<?php
declare (strict_types=1);

namespace app\middleware;

use Closure;
use mof\ApiResponse;
use app\model\AdminMenu;
use app\model\AdminRoleMenu;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Request;
use think\Response;

class PermissionMiddleware
{
    /**
     * 处理请求
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function handle(Request $request, Closure $next): Response
    {
        //如果未登录，则提示用户登录
        if (!$request->user) {
            return ApiResponse::needLogin();
        }

        //如果登录会员是超级管理员则直接通过
        if ($request->user->is_super_admin) {
            return $next($request);
        }

        //获取当前请求的权限id
        list($module, $controller, $action) = $this->parseRule($request->rule()->getName());
        $ruleId = $this->getRuleId($module, $controller, $action);
        if (!$ruleId) {
            return ApiResponse::noPermission();
        }

        //获取当前登录会员的所有权限id
        $ruleIds = AdminRoleMenu::where('role_id', $request->user->role_id)
            ->column('menu_id');
        if (!in_array($ruleId, $ruleIds)) {
            return ApiResponse::noPermission();
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
        //name格式1: \app\{module}\controller\{controller}@{action}
        //name格式2: \app\{module}\controller\{controller}\{action}
        //name格式3: \app\{module}\controller\{controller}/{action}

        $name = trim(str_replace(['\\', '@', '//'], '/', $name), '/');
        $path = explode('/', $name);
        $module = $path[1];
        $controller = $path[3];
        $action = $path[4];

        return [$module, $controller, $action];
    }

    /**
     * 获取权限id
     * @param $module string 模块名
     * @param $controller string 控制器名
     * @param $action string 方法名
     * @return int 返回权限id
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    protected function getRuleId(string $module, string $controller, string $action): int
    {
        $perm = "$controller@$action";
        $where = [
            'module' => $module,
            'perm'   => $perm,
            'status' => 1,
        ];
        $menu = AdminMenu::where($where)->find();
        return $menu ? $menu->id : 0;
    }
}
