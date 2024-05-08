<?php

namespace app\library;

use app\model\Perm;
use mof\exception\LogicException;

/**
 * 安装权限菜单
 */
class InstallPerm
{
    protected array $actions = [
        'index'   => '列表',
        'create'  => '增加',
        'read'    => '详情',
        'edit'    => '编辑',
        'save'    => '保存',
        'update'  => '更新',
        'delete'  => '删除',
        'deletes' => '批量删除',
        'updates' => '批量更新',
    ];

    /**
     * 模块名称
     * @var string
     */
    protected string $moduleName;

    public static function make($moduleName): static
    {
        $moduleInfo = \mof\Module::info($moduleName);
        if (!$moduleInfo) {
            throw new LogicException(sprintf('找不到模块%s信息', $moduleName));
        }
        return new static($moduleInfo);
    }

    /**
     * @param array $moduleInfo 模块信息
     */
    public function __construct(protected array $moduleInfo)
    {
        $this->moduleName = $moduleInfo['name'];
    }

    /**
     * 安装模块权限
     * @return bool
     * @throws \Exception
     */
    public function install(): bool
    {
        $model = new Perm();
        $model->startTrans();
        try {
            //先卸载旧菜单(不调用模型事件)
            $this->uninstall();

            //安装菜单
            $perms = $this->moduleInfo['perms'];
            if ($perms) $this->savePerm($perms);

            $model->commit();
        } catch (\Exception $e) {
            $model->rollback();
            throw $e;
        }

        return true;
    }

    /**
     * 卸载模块权限
     * @return void
     */
    public function uninstall(): void
    {
        Perm::where('module', $this->moduleName)->delete();
    }

    /**
     * 启用
     * @return void
     */
    public function enable(): void
    {
        Perm::where('module', $this->moduleName)->update(['status' => 1]);
    }

    /**
     * 停用
     * @return void
     */
    public function disable(): void
    {
        Perm::where('module', $this->moduleName)->update(['status' => 0]);
    }

    /**
     * 保存到数据库
     * @param array $perms
     * @param Perm|null $parentPerm
     * @return void
     */
    protected function savePerm(array $perms, Perm $parentPerm = null): void
    {
        foreach ($perms as $perm) {
            //从数组 $perm 中获取键名为perm、title、url、icon的值，组成一个新数组
            $data = array_intersect_key(
                $perm, array_flip(['type', 'category', 'perm', 'title', 'url', 'icon', 'sort'])
            );
            $data['pid'] = $parentPerm ? $parentPerm->id : 0;
            $data['category'] = $data['category'] ?? ($parentPerm ? $parentPerm->category : 'system');
            $data['module'] = $this->moduleName;
            $data['status'] = 1;
            //新建根权限
            $prem = Perm::create($data);
            //新建action类型权限
            if (isset($perm['actions']) && is_array($perm['actions'])) {
                $this->createActions($prem, $perm['actions']);
            }
            //找下级，递归添加
            if (!empty($perm['children']) && is_array($perm['children'])) {
                $this->savePerm($perm['children'], $prem);
            }
        }
    }

    /**
     * 添加菜单行为组
     * @param Perm $parentPerm
     * @param $actions
     * @return array
     */
    protected function createActions(Perm $parentPerm, $actions): array
    {
        $index = array_search('*', $actions);
        if ($index !== false) {
            unset($actions[$index]);
            $actions = array_merge($actions, array_keys($this->actions));
        }
        return array_map(function ($action) use ($parentPerm) {
            $actionInfo = $this->getActionInfo($action);
            return Perm::create([
                'pid'      => $parentPerm->getAttr('id'), //父级id
                'pid_path' => $parentPerm->getParents($parentPerm->getAttr('id'), true), //父级id路径
                'type'     => 'action', //类型
                'category' => $parentPerm->category,
                'module'   => $parentPerm->getAttr('module'),  //所属模块
                'perm'     => $parentPerm->getAttr('perm') . '@' . $actionInfo['action'], //权限标识
                'title'    => $actionInfo['title'],
                'url'      => '',
                'status'   => 1,
                'sort'     => 0,
            ]);
        }, $actions);
    }

    /**
     * 获取内置的action信息
     * @param string $actionStr
     * @return array
     */
    protected function getActionInfo(string $actionStr): array
    {
        if (strpos($actionStr, '@')) {
            list($action, $title) = explode('@', $actionStr);
        } else if (in_array($actionStr, array_keys($this->actions))) {
            $action = $actionStr;
            $title = $this->actions[$action];
        } else {
            $action = $title = $actionStr;
        }

        return [
            'action' => $action,
            'title'  => $title,
        ];
    }

}