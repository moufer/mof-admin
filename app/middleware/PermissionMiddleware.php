<?php
declare (strict_types=1);

namespace app\middleware;

use app\library\Auth;
use app\model\Perm;
use app\model\RolePerm;
use Closure;
use mof\ApiResponse;
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
        $auth = app(Auth::class);
        //如果未登录，则提示用户登录
        if (!$auth->isLogin()) {
            return ApiResponse::needLogin();
        }

        //如果登录会员是超级管理员则直接通过
        if ($auth->getUser()->is_super_admin) {
            return $next($request);
        }

        //获取当前请求的权限id
        list($module, $controller, $action) = $this->parseRule($request->rule()->getName());
        $ruleId = $this->getRuleId($module, $controller, $action);
        if (!$ruleId) {
            return ApiResponse::noPermission();
        }

        //获取当前登录会员的所有权限id，判断是否拥有权限
        $ruleIds = (new RolePerm)->where('role_id', $auth->getUser()->role_id)
            ->column('perm_id');
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
        //name格式1: \app\\controller\{controller}[@\/]{action}
        //name格式2: \module\\miniapp\controller\{controller}[@\/]{action}

        $name = trim(str_replace(['\\', '@', '//'], '/', $name), '/');
        $path = explode('/', $name);
        $action = array_pop($path);
        $controller = array_pop($path);
        $module = $path[0] === 'app' ? 'admin' : $path[1];

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
            'type'   => 'action',
            'perm'   => $perm,
            'status' => 1,
        ];
        $menu = (new Perm)->where($where)->find();
        return $menu ? $menu->id : 0;
    }
}
