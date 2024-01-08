<?php

namespace app\model;

use app\model\searcher\PermSearcher;
use mof\Model;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Db;

class Perm extends \mof\Model
{
    protected array $searchOption = [
        'id'        => 'integer:pk',
        'category'  => 'string',
        'module'    => 'string',
        'type'      => 'string',
        'title'     => 'string',
        'status'    => 'integer',
        'create_at' => 'time_range',
    ];

    public static function onAfterUpdate(Perm $model): void
    {
        //检测pid是否改变，如果改变了，则更新下级的pid_path
        if ($model->isDirty('pid')) {
            $newPid = $model->getAttr('pid');
            $oldPid = $model->getOrigin('pid');
            $newPidPath = $model->getParents($newPid); //新的父级id路径
            $oldPidPath = $model->getParents($oldPid); //旧的父级id路径
            self::where('pid_path', 'like', $oldPidPath . '-%')
                ->update([
                    'pid_path' => Db::raw("REPLACE(pid_path, '{$oldPidPath}-', '{$newPidPath}-')")
                ]);
        }
    }

    public static function onAfterDelete(Model $model): void
    {
        //删除菜单时，把菜单下的action也删除
        if ('menu' === $model->getAttr('type')) {
            self::where('pid', $model->id)->delete();
        }
    }

    /**
     * 获取指定父模块下所有权限
     * @param string $expr
     * @param $returnTree bool 是否返回树形结构
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function getAll(string $expr = 'module=admin', bool $returnTree = false): array
    {
        list($field, $value) = explode('=', $expr);
        $perms = self::where($field, $value)
            ->where('status', 1)
            ->order('sort', 'asc')
            ->select()
            ->toArray();
        if ($returnTree) {
            $tree = new \mof\front\Tree($perms);
            $perms = $tree->getData(0, 'title');
        }
        return $perms;
    }

    /**
     * 根据提供的权限id集合，返回包含上级id的完整的权限权限集合
     * @param array $permIds
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function getCompletePermIds(array $permIds): array
    {
        $perms = self::where('id', 'in', $permIds)
            ->where('status', 1)
            ->select();
        //从$perms里取出pid_path
        $ids = [];
        $perms->each(function ($item) use (&$ids) {
            $ids[] = $item->getAttr('id');         //把自己的id也加进去
            $pidPath = $item->getAttr('pid_path'); //父级id路径
            if ($pidPath) {
                $ids = array_merge($ids, explode('-', $pidPath)); //把父级id路径也加进去
            }
        });
        //数组转换成数字后，去重
        $ids = array_map('intval', $ids);
        return array_unique($ids);
    }

    /**
     * 新建当前菜单的action
     * @param string $action
     * @return false|static
     */
    public function createAction(string $action): bool|static
    {
        $action = $this->getActionInfo($action);
        if (!$action || !isset($action['action'])) {
            return false;
        }
        //action行为插入到Perm表中
        $model = new static();
        $model->save([
            'pid'      => $this->getAttr('id'), //父级id
            'pid_path' => $this->getParents($this->getAttr('id')), //父级id路径
            'type'     => 'action', //类型
            'module'   => $this->getAttr('module'),  //所属模块
            'perm'     => $this->getAttr('perm') . '@' . $action['action'], //权限标识
            'title'    => $action['title'] ?? $action['action'],
            'url'      => '',
            'status'   => 1,
            'sort'     => 0,
        ]);
        return $model;
    }

    /**
     * 当前节点的所有父级节点id，格式：1-2-3
     */
    public function getParents($pid): string
    {
        if (!$pid) return '';
        $parents = [];
        while ($pid && $pid > 0) {
            //$pid插入数组$parents的头部
            array_unshift($parents, $pid);
            $m = self::find($pid);
            $pid = $m ? $m->pid : 0;
        }
        return implode('-', $parents);
    }

    /**
     * 设置上级id
     * @param $value
     * @return int
     */
    public function setPidAttr($value): int
    {
        if (is_array($value)) {
            $value = array_pop($value);
        } else if ($value === '') {
            $value = 0;
        }
        $this->setAttr('pid_path', $this->getParents($value));
        return $value;
    }

    /**
     * 设置模块
     * @param $value
     * @param $data
     * @return string
     */
    public function setModuleAttr($value, $data): string
    {
        //行为类型时，所属模块使用上级权限的模块
        if ('action' === $data['type']) {
            try {
                //获取上级id
                $pid = is_array($data['pid']) ? array_pop($data['pid']) : $data['pid'];
                $parent = !empty($pid) ? self::find($pid) : false;
                return $parent ? $parent->module : $value;
            } catch (DbException) {
                return $value;
            }
        }
        return $value;
    }

    protected function setIconAttr($value): string
    {
        return str_replace(' ', '_', $value);
    }

    /**
     * 获取内置的action信息
     * @param string $action
     * @return array
     */
    protected function getActionInfo(string $action): array
    {
        $actions = [
            'index'  => ['title' => '列表', 'action' => 'index'],
            'save'   => ['title' => '添加', 'action' => 'save'],
            'read'   => ['title' => '详情', 'action' => 'read'],
            'update' => ['title' => '编辑', 'action' => 'update'],
            'delete' => ['title' => '删除', 'action' => 'delete'],
            'multi'  => ['title' => '批量操作', 'action' => 'multi'],
        ];
        //TODO 批量删除
        return $actions[$action] ?? ["action" => $action];
    }
}